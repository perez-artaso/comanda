<?php

include_once('DBConnection.php');
include_once('ItemManagement.php');
include_once('EmployeeManagement.php');
include_once('Validators.php');

class OrderManagement {

    public static function create_order ($token, $client_name, $photograph = null, $table_id, Array $items) {
        try {

            $DBCon = DBConnection::NewDBConnection();

            if (TableManagement::get_status($table_id) == 0) {

                $order_id = dechex(mt_rand(65536, 1048575));

                $query = $DBCon->SetQuery("INSERT INTO orders (id, client_name, photograph, table_id, date, taken_by) VALUES (?, ?, ?, ?, ?, ?)");
                $query->bindValue(1, $order_id, PDO::PARAM_STR);
                $query->bindValue(2, $client_name, PDO::PARAM_STR);
                $query->bindValue(3, $photograph, PDO::PARAM_STR);
                $query->bindValue(4, $table_id, PDO::PARAM_INT);
                $query->bindValue(5, time(), PDO::PARAM_INT);
                $query->bindValue(6, EmployeeManagement::get_decoded_jwt_body($token)["id"], PDO::PARAM_INT);

                $query->execute();

                foreach ($items as $item) {
                    for ($i = 0; $i < json_decode($item)->quantity ; $i++) {
                        ItemManagement::create_item($order_id, json_decode($item)->product_id);
                    }
                }

                TableManagement::change_table_status($table_id, 1);

                return self::response_formatter(
                    200,
                    "Orden registrada con éxito. Su identificador es: " . $order_id
                );

            } else throw new Exception ("La mesa se encuentra actualmente ocupada.");

        } catch (Exception $e) {
            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );
        }

    }

    public static function add_items_to_order($order_id, Array $items) {
        try {
            $order = self::get_order($order_id);
            
            if ($order != []) {

                if ($order[0]["closed_at"] == 0 ) {

                    foreach ($items as $item) {
                        for ($i = 0; $i < json_decode($item)->quantity ; $i++) {
                            ItemManagement::create_item($order_id, json_decode($item)->product_id);
                        }
                    }

                    return self::response_formatter(
                        200,
                        "Los pedidos fueron agregados con éxito."
                    );

                } else {
                    throw new Exception ("La orden indicada ya ha sido cerrada.");
                }

            } else {
                throw new Exception ("La orden indicada no existe en el registro.");
            }

        } catch (Exception $e) {

            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );

        } 
    }

    static function read_orders() {
        $DBCon = DBConnection::NewDBConnection();
        $query = $DBCon->SetQuery("SELECT * FROM orders");
        $query->execute();

        return $query->fetchALl(PDO::FETCH_ASSOC);
    }

    public static function get_order_ids () {

        $DBCon = DBConnection::NewDBConnection();
        $query = $DBCon->SetQuery("SELECT id FROM orders");
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);

    }

    public static function check_order_status ($order_id) {
        try {
            $order_data = self::get_order($order_id);
            if ($order_data != []) {

                $DBCon = DBConnection::NewDBConnection();
                $query = $DBCon->SetQuery("SELECT * FROM items WHERE order_id IN (SELECT id FROM orders WHERE id = ?)");
                $query->bindValue(1, $order_id, PDO::PARAM_STR);
                $query->execute();
                $items = $query->fetchAll(PDO::FETCH_ASSOC);

                $order_status = array(
                    "client_name" => $order_data[0]["client_name"],
                    "photograph" => $order_data[0]["photograph"],
                    "items" => []
                );

                foreach($items as $item) {
                    if ($item["status"] == 0 || $item["status"] == 1) {
                        array_push(
                            $order_status["items"],
                            array(
                                "product_name" => ItemManagement::get_product_name($item["id"]),
                                "estimated_time" => OrderManagement::get_resting_time_for_completion($item["estimated_time"])
                            )
                        );
                    }
                }

                return self::response_formatter(
                    200,
                    $order_status
                );

            } else {

                return self::response_formatter(
                    404,
                    "La comanda indicada no figura en el registro."
                );

            }
        } catch (Exception $e) {

            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );

        }
    }

    public static function get_resting_time_for_completion($estimated_completion) {

        Validators::validate_timestamp($estimated_completion);

        $date_time_object = new DateTime();

        if ($estimated_completion < $date_time_object->getTimestamp() && $estimated_completion != 0) {

            return "Su pedido pronto estará en su mesa.";

        } else if ($estimated_completion == 0) {

            return "Su pedido aún no empieza a prepararse.";

        } else {

            $estimated_completion_object = new DateTime(
                date("Y-m-d H:i:s", $estimated_completion)
            );

            $time_to_completion = date_diff($estimated_completion_object, $date_time_object);

            return "Su pedido estará listo en aproximadamente " . $time_to_completion->format("%i minutos.");

        }

    }

    public static function get_order ($order_id) {
        $DBCon = DBConnection::NewDBConnection();
        $query = $DBCon->SetQuery("SELECT * FROM orders WHERE id = ?");
        $query->bindValue(1, $order_id, PDO::PARAM_STR);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function get_active_orders() {
        try {
            $DBCon = DBConnection::NewDBConnection();
            $query = $DBCon->SetQuery("SELECT * FROM orders WHERE closed_at = ?");
            $query->bindValue(1, 0, PDO::PARAM_INT);
            $query->execute();
    
            $active_orders = $query->fetchAll(PDO::FETCH_ASSOC);
    
            foreach ($active_orders as &$order) {
                $items = ItemManagement::get_items_by_order_id($order["id"]);
                $item_array = [];
                foreach ($items as $item) {
                    array_push(
                        $item_array,
                        array (
                            "id" => $item["id"],
                            "product_name" => ItemManagement::get_product_name($item["id"]),
                            "estimated_time" => $estimated = ($item["estimated_time"] == 0) ? "No tomado" : date("H:i:s", $item["estimated_time"]),
                            "status" => self::verbalize_status($item["status"])
                        )
                    );
                }
                $order["items"] = $item_array;
            }
    
            return self::response_formatter(
                200,
                $active_orders
            );

        } catch (Exception $e) {

            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );

        }
    }

    public static function cancel_item ($item_id) {
        try {

            $DBCon = DBConnection::NewDBConnection();
            $query = $DBCon->SetQuery("UPDATE items SET status = ? WHERE id = ?");
            $query->bindValue(1, 3, PDO::PARAM_INT);
            $query->bindValue(2, $item_id, PDO::PARAM_INT);

            $query->execute();

            return self::response_formatter(
                200,
                "El pedido fue cancelado con éxito."
            );

        } catch (Exception $e) {

            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );

        }
    }

    public static function close_order ($order_id) {
        try {

            TableManagement::change_table_status(
                self::get_order($order_id)[0]["table_id"],
                3
            );

            $items = ItemManagement::get_items_by_order_id($order_id);
           
            foreach ($items as $item) {
                if ($item["status"] != 3 && $item["status"] != 2) {
                    ItemManagement::change_item_status($item["id"], 3);
                }
            }

            self::register_close_time($order_id);

            return self::response_formatter(
                200,
                "El importe final de la mesa es: $" . self::get_order_final_import($order_id)
            );

        } catch (Exception $e) {

            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );

        }
    }

    static function register_close_time($order_id) {
        $DBCon = DBConnection::NewDBConnection();
        $query = $DBCon->SetQuery("UPDATE orders SET closed_at = ? WHERE id = ?");
        $query->bindValue(1, time(), PDO::PARAM_INT);
        $query->bindValue(2, $order_id, PDO::PARAM_STR);
        $query->execute();
    }

    public static function get_order_final_import ($order_id) {
        
        $items = ItemManagement::get_items_by_order_id($order_id);
        $final_import = 0;
        foreach($items as $item){
            if ($item["status"] == 2) {
                $final_import += ItemManagement::get_item_price($item["id"]);
            }
        }
        return $final_import;

    }

    public static function get_orders_detail() {
        try {
            $orders = self::read_orders();

            foreach($orders as &$order) {
                $items = ItemManagement::get_items_by_order_id($order["id"]);
                foreach($items as $item) {
                    $order["items"][] = $item;
                }
            }
    
            unset($order);
    
            $formatted_response = array();
    
            foreach($orders as $order) {
                $waiter = EmployeeManagement::get_employee($order["taken_by"]);
                array_push($formatted_response, array(
                    "order_id" => $order["id"],
                    "client_name" => $order["client_name"],
                    "photograph" => $order["photograph"],
                    "table_id" => $order["table_id"],
                    "table_description" => TableManagement::get_table($order["table_id"]),
                    "taken_at" => date("d/m/Y H:i:s", $order["date"]),
                    "closed_at" => date("d/m/Y H:i:s", $order["closed_at"]),
                    "taken_by" => array(
                        "id" => $waiter["id"],
                        "name" => $waiter["name"],
                        "status" => $status = ($waiter["status"] == "1") ? "Empleado" : "Dado de baja" 
                    ),
                    "items" => array (
    
                    ),
                    "total_income" => 0
                ));
                
                foreach($order["items"] as $item) {
                    $employee = EmployeeManagement::get_employee($item["taken_by"]);
                    array_push(
                        $formatted_response[count($formatted_response) - 1]["items"],
                        array(
                            "product_name" => ItemManagement::get_product_name($item["id"]),
                            "taken_by" => array (
                                "id" => $employee["id"],
                                "name" => $employee["name"],
                                "status" => $status = ($employee["status"] == "1") ? "Empleado" : "Dado de baja"
                            ),
                            "taken_at" => date("d/m/Y H:i:s", $item["taken_at"]),
                            "estimated_time" => date("d/m/Y H:i:s", $item["estimated_time"]),
                            "ready_at" => date("d/m/Y H:i:s", $item["ready_at"]),
                            "status" => self::verbalize_status($item["status"])
                        )
                    );

                    if ($item["status"] == 2) {
                        $formatted_response[count($formatted_response) - 1]["total_income"] += ItemManagement::get_item_price($item["id"]);
                    }

                }
            }
    
            return self::response_formatter(
                200,
                $formatted_response
            );

        } catch (Exception $e) {

            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );

        }        
    }

    public static function get_monthly_income_average() {
        try {
            $orders = self::read_orders();

            $monthly_incomes = array();

            foreach($orders as $order) {
                $month_and_year = date("m/Y", $order["date"]);
                $monthly_incomes[$month_and_year]["total_income"] += self::get_order_final_import($order["id"]);
                if (isset($monthly_incomes[$month_and_year]["amount_of_orders"])){
                    $monthly_incomes[$month_and_year]["amount_of_orders"]++;
                } else {
                    $monthly_incomes[$month_and_year]["amount_of_orders"] = 1;
                }            
            }

            foreach($monthly_incomes as &$monthly_income) {
                $monthly_income["average_income"] = ($monthly_income["total_income"] / $monthly_income["amount_of_orders"]);
            }

            unset($monthly_income);

            return self::response_formatter(
                200,
                $monthly_incomes
            );

        } catch (Exception $e) {

            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );

        }
    }

    static function verbalize_status($status) {
        switch ($status) {
            case 0:
                return "En cola";
            break;

            case 1:
                return "En preparación";
            break;

            case 2:
                return "Listo";
            break;

            case 3:
                return "Cancelado";
            break;
        }
    }

    static function response_formatter ($status_code, $message) {

        return json_encode (
            array (
                "status_code" => $status_code,
                "message" => $message
            )
        );

    }

}