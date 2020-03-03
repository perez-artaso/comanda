<?php

require_once("DBConnection.php");
require_once("Validators.php");

class TableManagement {
    
    public static function create_table ($description) {

        try{
            Validators::validate_table_description($description);
            $DBCon = DBConnection::NewDBConnection();
            $query = $DBCon->SetQuery("INSERT INTO tables (description) VALUES (?)");
            $query->bindValue(1, $description, PDO::PARAM_STR);
            $query->execute();

            return self::response_formatter(
                200,
                "Mesa registrada con éxito."
            );

        } catch (Exception $e) {
            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );
        }

    }

    public static function read_tables () {
        try{
            $DBCon = DBConnection::NewDBConnection();
            $query = $DBCon->SetQuery("SELECT * FROM tables");
            $query->execute();

            $result = $query->fetchAll(PDO::FETCH_ASSOC);

            return self::response_formatter(
                200,
                $result
            );

        } catch (Exception $e) {
            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );
        }
    }

    public static function get_table($id) {
        $DBCon = DBConnection::NewDBConnection();
        $query = $DBCon->SetQuery("SELECT * FROM tables WHERE id = ?");
        $query->bindValue(1, $id, PDO::PARAM_STR);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function delete_table ($id) {

        try{
            $DBCon = DBConnection::NewDBConnection();
            $query = $DBCon->SetQuery("DELETE FROM tables WHERE id = ?");
            $query->bindValue(1, $id, PDO::PARAM_INT);
            $query->execute();

            return self::response_formatter(
                200,
                "Mesa elminada del registro."
            );

        } catch (Exception $e) {
            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );
        }
        
    }

    public static function update_table ($id, $description) {

        try{
            Validators::validate_table_description($description);
            $DBCon = DBConnection::NewDBConnection();
            $query = $DBCon->SetQuery("UPDATE tables SET description = ? WHERE id = ?");
            $query->bindValue(1, $description, PDO::PARAM_STR);
            $query->bindValue(2, $id, PDO::PARAM_INT);
            $query->execute();

            return self::response_formatter(
                200,
                "Información actualizada."
            );

        } catch (Exception $e) {
            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );
        }

    }

    public static function change_table_status($id, $status) {
        try {

            if (self::table_exists($id)) {
                if ($status == "0" || $status == "1" || $status == "2" || $status == "3") {
                    $DBCon = DBConnection::NewDBConnection();
                    $query = $DBCon->SetQuery("UPDATE tables SET status = ? WHERE id = ?");
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
                    "La mesa indicada no existe en el registro."
                );
            }                       

        } catch (Exception $e) {
            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );
        }
    }

    static function table_exists($id) {
        $DBCon = DBConnection::NewDBConnection();

        $query = $DBCon->SetQuery("SELECT * FROM tables WHERE id = ?");
        $query->bindValue(1, $id, PDO::PARAM_INT);
        $query->execute();

        return $exists = ($query->fetchAll() != []) ? true : false;
    }

    static function response_formatter ($status_code, $message) {

        return json_encode (
            array (
                "status_code" => $status_code,
                "message" => $message
            )
        );

    }

    static public function close_table ($id) {

        try {

            if (self::get_status($id) == 3) {
                $DBCon = DBConnection::NewDBConnection();

                $query = $DBCon->SetQuery("UPDATE tables SET status = ? WHERE id = ?");
                $query->bindValue(1, 0, PDO::PARAM_INT);
                $query->bindValue(2, $id, PDO::PARAM_INT);
                $query->execute();
    
                return self::response_formatter(
                    200,
                    "Mesa cerrada con éxito."
                );
            } else {
                throw new Exception ("La mesa no puede cerrarse en su estado actual.");
            }            

        } catch (Exception $e) {
                return self::response_formatter(
                    500,
                    "Ha ocurrido una excepción: " . $e->getMessage()
                );
        }

    }

    public static function get_most_used_table() {

        try {
            $DBCon = DBConnection::NewDBConnection();
            $query = $DBCon->SetQuery("SELECT table_id, COUNT(table_id) as times_used FROM orders GROUP BY table_id ORDER BY times_used DESC LIMIT 1;");
            $query->execute();
    
            $most_used = $query->fetchAll(PDO::FETCH_ASSOC);
    
            return self::response_formatter(
                200,
                self::get_table($most_used[0]["table_id"])
            );
        } catch (Exception $e) {
            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );
        }   

    }

    public static function get_less_used_table() {

        try {
            $DBCon = DBConnection::NewDBConnection();
            $query = $DBCon->SetQuery("SELECT table_id, COUNT(table_id) as times_used FROM orders GROUP BY table_id ORDER BY times_used ASC LIMIT 1;");
            $query->execute();
    
            $less_used = $query->fetchAll(PDO::FETCH_ASSOC);
    
            return self::response_formatter(
                200,
                self::get_table($less_used[0]["table_id"])
            );
        } catch (Exception $e) {
            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );
        }   

    }

    public static function get_status($id) {
        if (self::table_exists($id)) {
            $DBCon = DBConnection::NewDBConnection();

            $query = $DBCon->SetQuery("SELECT status FROM tables WHERE id = ?");
            $query->bindValue(1, $id, PDO::PARAM_INT);
            $query->execute();
    
            return $query->fetchAll(PDO::FETCH_ASSOC)[0]["status"];
        } else throw new Exception ("La mesa solicitada no se encuentra en el registro.");
    }

    static function get_order_table ($order_id) {
        $DBCon = DBConnection::NewDBConnection();
        $query = $DBCon->SetQuery("SELECT table_id FROM orders WHERE id = ?");
        $query->bindValue(1, $order_id, PDO::PARAM_STR);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC)[0]["table_id"];
    }

    public static function get_total_bills_per_table() {
        $items_response = json_decode(ItemManagement::read_items());
        $items = (array) $items_response->message;

        $orders = [];

        for ($i = 0; $i < count($items); $i++) {
            if($items[$i]->status == 2) {
                $orders[$items[$i]->order_id][] = ItemManagement::get_item_price($items[$i]->id);
            }
        }

        foreach ($orders as $key => &$order_data) {
            $order_data["table_id"] = self::get_order_table($key);
        }

        $table_incomes = [];

        foreach ($orders as $j => &$order) {
            foreach($order as $key => $order_prices) {
                if ($key !== "table_id") {
                    $table_incomes[$orders[$j]["table_id"]] += $order_prices;
                }
            }
        }

        return $table_incomes;

    }

    public static function get_most_billed_table($table_incomes) {
        $highest_incomes_table = array (
            "table_id" => 0,
            "description" => "",
            "incomes" => 0
        );

        foreach ($table_incomes as $table_id => $incomes) {
            if (
                $incomes > $highest_incomes_table["incomes"]
            ) {
                $highest_incomes_table["table_id"] = $table_id;
                $highest_incomes_table["description"] = self::get_table($table_id)[0]["description"];
                $highest_incomes_table["incomes"] = $incomes;
            }
        }

        return self::response_formatter(
            200,
            $highest_incomes_table
        );
    }

    public static function get_less_billed_table($table_incomes) {
        $lowest_incomes_table = array (
            "table_id" => array_keys($table_incomes)[0],
            "description" => self::get_table(array_keys($table_incomes)[0])[0]["description"],
            "incomes" => $table_incomes[array_keys($table_incomes)[0]]
        );

        foreach ($table_incomes as $table_id => $incomes) {
            if (
                $incomes < $lowest_incomes_table["incomes"]
            ) {
                $lowest_incomes_table["table_id"] = $table_id;
                $lowest_incomes_table["description"] = self::get_table($table_id)[0]["description"];
                $lowest_incomes_table["incomes"] = $incomes;
            }
        }

        return self::response_formatter(
            200,
            $lowest_incomes_table
        );
    }

    public static function get_between_dates_table_income($from, $to, $table_id = '%') {
        try {

            $DBCon = DBConnection::NewDBConnection();

            Validators::validate_inputted_string_date($from);
            Validators::validate_inputted_string_date($to);
            $exploded_from = explode("/", $from);
            $exploded_to = explode("/", $to);
    
            $from_timestamp = mktime(00, 0, 0, $exploded_from[1], $exploded_from[0], $exploded_from[2]);
            $to_timestamp = mktime(00, 0, 0, $exploded_to[1], $exploded_to[0], $exploded_to[2]);

            $query = $DBCon->SetQuery(
                "SELECT * FROM orders 
                WHERE date > ? 
                AND date < ? 
                AND table_id LIKE ?"
            );

            $query->bindValue(1, $from_timestamp, PDO::PARAM_INT);
            $query->bindValue(2, $to_timestamp, PDO::PARAM_INT);
            $query->bindValue(3, $table_id, PDO::PARAM_STR);

            $query->execute();

            $resulting_orders = $query->fetchAll(PDO::FETCH_ASSOC);

            $total_income = 0;

            foreach ($resulting_orders as $order) {
                foreach (ItemManagement::get_items_by_order_id($order["id"]) as $item) {

                    if($item["status"] == 2) {
                        $total_income += ItemManagement::get_item_price($item["id"]);
                    }

                }
            }

            return self::response_formatter(
                200,
                $total_income
            );

        } catch (Exception $e) {

            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );

        }     
    }

    public static function get_monthly_average_per_table() {
        try {
            $orders = OrderManagement::read_orders();
            $monthly_income_per_table = array();
            foreach ($orders as $order) {
                $month_and_year = date("m/Y", $order["date"]);
                $monthly_income_per_table[$month_and_year][
                    $order["table_id"] . " (" . self::get_table($order["table_id"])[0]["description"] . ")"
                ]["total_income"] += OrderManagement::get_order_final_import($order["id"]);
                
                if (isset($monthly_income_per_table[$month_and_year][
                    $order["table_id"] . " (" . self::get_table($order["table_id"])[0]["description"] . ")"
                ]["amount_of_orders"])) {
                    $monthly_income_per_table[$month_and_year][
                        $order["table_id"] . " (" . self::get_table($order["table_id"])[0]["description"] . ")"
                    ]["amount_of_orders"]++;
                } else {
                    $monthly_income_per_table[$month_and_year][
                        $order["table_id"] . " (" . self::get_table($order["table_id"])[0]["description"] . ")"
                    ]["amount_of_orders"] = 1;
                }

                if (isset($monthly_income_per_table[$month_and_year]["total_amount_of_orders"])) {
                    $monthly_income_per_table[$month_and_year]["total_amount_of_orders"]++;
                } else {
                    $monthly_income_per_table[$month_and_year]["total_amount_of_orders"] = 1;
                }
                
            }

            foreach($monthly_income_per_table as &$per_month) {
                foreach ($per_month as &$income_per_table) {
                    $income_per_table["average_income"] = ($income_per_table["total_income"] / $income_per_table["amount_of_orders"]);
                    $income_per_table["percentage_of_usage"] = (($income_per_table["amount_of_orders"] / $per_month["total_amount_of_orders"]) * 100 );
                }
            }

            return self::response_formatter(
                200, 
                $monthly_income_per_table
            );
            
        } catch (Exception $e) {

            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );
            
        }
    }

}