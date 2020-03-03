<?php

include_once('DBConnection.php');
include_once('ProductManagement.php');
include_once('OrderManagement.php');

class ItemManagement {

    public static function create_item ($order_id, $product_id) {

        try {
            
            $DBCon = DBConnection::NewDBConnection();
            $query = $DBCon->SetQuery('INSERT INTO items (order_id, product_id) VALUES (?, ?)');
            $query->bindValue(1, $order_id, PDO::PARAM_INT);
            $query->bindValue(2, $product_id, PDO::PARAM_INT);
            $query->execute();

            return self::response_formatter(
                200,
                "Carga realizada con éxito."
            );

        } catch(Exception $e) {

            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );

        }

    }

    public static function read_items() {

        try {

            $DBCon = DBConnection::NewDBConnection();
            $query = $DBCon->SetQuery("SELECT * FROM items");
            $query->execute();

            $result = $query->fetchAll(PDO::FETCH_ASSOC);

            return self::response_formatter(
                200,
                $result
            );

        } catch (Exception $e) {

            return self::response_formatter(
                500,
                "Ha ocurrido una excpeción: " . $e->getMessage()
            );

        }

    }

    public static function get_product_name($item_id) {
        $DBCon = DBConnection::NewDBConnection();
        $query = $DBCon->SetQuery("SELECT name from products WHERE id IN (SELECT product_id FROM items WHERE id = ?)");
        $query->bindValue(1, $item_id, PDO::PARAM_INT);
        $query->execute();

        $product_name = $query->fetchAll(PDO::FETCH_ASSOC);

        if (isset($product_name[0]["name"])){
            return $product_name[0]["name"];
        } else {
            return "Producto Inexistente";
        }
    }

    public static function get_item ($id) {

        $DBCon = DBConnection::NewDBConnection();
        $query = $DBCon->SetQuery("SELECT * FROM items WHERE id = ?");
        $query->bindValue(1, $id, PDO::PARAM_INT);
        $query->execute();

        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($result != []) {
            return $result[0];
        } else {
            throw new Exception ("El item indicado no figura en la base de datos.");
        }

    }

    public static function change_item_status($id, $status) {

        try {

            if (self::item_exists($id)) {
                if ($status == "0" || $status == "1" || $status == "2" || $status == "3") {
                    $DBCon = DBConnection::NewDBConnection();
                    $query = $DBCon->SetQuery("UPDATE items SET status = ? WHERE id = ?");
                    $query->bindValue(1, $status, PDO::PARAM_INT);
                    $query->bindValue(2, $id, PDO::PARAM_INT);
                    $query->execute();

                    return self::response_formatter(
                        200,
                        "Estado actualizado."
                    );
    
                } else {
                    throw new Exception("Valor de estado inválido. Los valores posibles son 0, 1, 2 y 3. Vea la documentación.");
                }
            } else {

                return self::response_formatter(
                    404,
                    "El item señalada no figura en el registro."
                );

            }

        } catch (Exception $e) {

            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );

        }
    }

    public static function item_exists($id) {

        $DBCon = DBConnection::NewDBConnection();

        $query = $DBCon->SetQuery("SELECT * FROM items WHERE id = ?");
        $query->bindValue(1, $id, PDO::PARAM_INT);
        $query->execute();

        return $exists = ($query->fetchAll() != []) ? true : false;
        
    }

    public static function already_taken($id) {
        $DBCon = DBConnection::NewDBConnection();

        $query = $DBCon->SetQuery("SELECT * FROM items WHERE id = ? AND status = ?");
        $query->bindValue(1, $id, PDO::PARAM_INT);
        $query->bindValue(2, 0, PDO::PARAM_INT);
        $query->execute();

        return $taken = ($query->fetchAll() != []) ? true : false;
    }

    public static function get_items_per_status($status) {

        try {
            Validators::validate_status($status);

            $DBCon = DBConnection::NewDBConnection();

            $query = $DBCon->SetQuery("SELECT * FROM items WHERE status = ?");
            $query->bindValue(1, $status, PDO::PARAM_INT);
            $query->execute();

            $items = $query->fetchAll(PDO::FETCH_ASSOC);
    
            return self::response_formatter(
                200,
                $items
            );

        } catch (Exception $e) {
            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );
        }

    }

    public static function get_item_appearences () {

        $DBCon = DBConnection::NewDBConnection();
        $query = $DBCon->SetQuery("SELECT product_id, COUNT(product_id) appearences FROM items WHERE status = 2 GROUP BY product_id ORDER BY appearences DESC");
        $query->execute();

        $appearences = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach ($appearences as &$appearence) {
            $appearence["product_name"] = ProductManagement::get_product($appearence["product_id"])[0]["name"];
        }

        return $appearences;
    }

    public static function get_most_selled() {
        try {
            $selled_items = self::get_item_appearences();
            $most_selled = array(
                $selled_items[0]
            );
    
            for ($i = 1; $i < count($selled_items); $i++) {
                if ($selled_items[$i]["appearences"] == $selled_items[0]["appearences"]) {
                    array_push($most_selled, $selled_items[$i]);
                } else break;
            }

            return self::response_formatter(
                200,
                $most_selled
            );

        } catch (Exception $e) {

            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );

        }        
        
    }

    public static function get_less_selled() {

        try {
            $selled_items = self::get_item_appearences();
            $highest_index = count($selled_items) - 1;
            $less_selled = array(
                $selled_items[$highest_index]
            );
    
            for ($i = ($highest_index - 1); $i > -1; $i--) {
                if ($selled_items[$i]["appearences"] == $selled_items[$highest_index]["appearences"]) {
                    array_push($less_selled, $selled_items[$i]);
                } else break;
            }
    
            return self::response_formatter(
                200,
                $less_selled
            );

        } catch (Exception $e) {

            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );

        }        
        
    }

    public static function get_late_deliveries() {

        try {
            $items = json_decode(self::read_items())->message;

            $late_deliveries = [];
    
            foreach ($items as $item) {
                if ($item->ready_at > $item->estimated_time) {
    
                    $taken_by = EmployeeManagement::get_employee($item->taken_by);
    
                    array_push(
                        $late_deliveries,
                        array (
                            "order_id" => $item->order_id,
                            "taken_by" => array(
                                "id" => $taken_by["id"],
                                "name" => $taken_by["name"],
                                "work_station" => EmployeeManagement::verbalize_work_stations($taken_by["work_station"]),
                                "status" => $status = ($taken_by["status"] == 1) ? "Empleado" : "Dado de baja"
                            ),
                            "product" => self::get_product_name($item->id),
                            "taken_at" => date("d/m/Y H:i:s", $item->taken_at),
                            "estimated_time" => date("d/m/Y H:i:s", $item->estimated_time),
                            "ready_at" => date("d/m/Y H:i:s", $item->ready_at)
                        )
                    );
    
                    return self::response_formatter(
                        200,
                        $late_deliveries
                    );
                }
            }
        } catch (Exception $e) {
            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );
        }
       
    }

    public static function get_cancelled_items () {

        try {
            $DBCon = DBConnection::NewDBConnection();
            $query = $DBCon->SetQuery("SELECT * FROM items WHERE status = ?");
            $query->bindValue(1, 3, PDO::PARAM_INT);
            $query->execute();
    
            $cancelled_items = $query->fetchAll(PDO::FETCH_ASSOC);
    
            $formatted_message = [];
    
            foreach ($cancelled_items as $cancelled) {
                $taken_by = EmployeeManagement::get_employee($cancelled["taken_by"]);
                $order = OrderManagement::get_order($cancelled["order_id"])[0];
                array_push(
                    $formatted_message,
                    array(
                        "order_id" => $cancelled["order_id"],
                        "product" => self::get_product_name($cancelled["id"]),
                        "taken_by" => array(
                            "id" => $taken_by["id"],
                            "name" => $taken_by["name"],
                            "work_station" => EmployeeManagement::verbalize_work_stations($taken_by["work_station"]),
                            "status" => $status = ($taken_by["status"] == 1) ? "Empleado" : "Dado de baja"
                        ),
                        "date" => date("d/m/Y H:i:s", $order["date"]),
                        "table_id" => $order["table_id"],
                        "photograph" => $order["photograph"],
                        "client_name" => $order["client_name"],
                        "waiter" => EmployeeManagement::get_employee($order["taken_by"])["name"]
                    )
                );
            }
    
            return self::response_formatter(
                200,
                $formatted_message
            );

        } catch (Exception $e) {

            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );

        }        

    }

    public static function get_item_price($id) {
        $DBCon = DBConnection::NewDBConnection();
        $query = $DBCon->SetQuery("SELECT price FROM products WHERE id IN (SELECT product_id FROM items WHERE id = ?)");
        $query->bindValue(1, $id, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC)[0]["price"];
    }

    public static function get_items_by_order_id($order_id) {

        $DBCon = DBConnection::NewDBConnection();
        $query = $DBCon->SetQuery("SELECT * FROM items WHERE order_id = ?");
        $query->bindValue(1, $order_id, PDO::PARAM_INT);
        
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
        
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