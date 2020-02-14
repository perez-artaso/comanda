<?php

include_once('DBConnection.php');
include_once('ItemManagement.php');
include_once('Validators.php');

class OrderManagement {

    public static function create_order ($client_name, $photograph = null, $table_id, Array $items) {
        try {

            $DBCon = DBConnection::NewDBConnection();

            if (TableManagement::get_status($table_id) == 0) {

                $order_id = dechex(mt_rand(65536, 1048575));

                $query = $DBCon->SetQuery("INSERT INTO orders (id, client_name, photograph, table_id) VALUES (?, ?, ?, ?)");
                $query->bindValue(1, $order_id, PDO::PARAM_STR);
                $query->bindValue(2, $client_name, PDO::PARAM_STR);
                $query->bindValue(3, $photograph, PDO::PARAM_STR);
                $query->bindValue(4, $table_id, PDO::PARAM_INT);

                $query->execute();

                foreach ($items as $item) {
                    for ($i = 0; $i < json_decode($item)->quantity ; $i++) {
                        ItemManagement::create_item($order_id, json_decode($item)->product_id);
                    }
                }

                return self::response_formatter(
                    200,
                    "Orden registrada con éxito."
                );
            }

        } catch (Exception $e) {
            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );
        }

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
                    "client_name" => $order_data["client_name"],
                    "photograph" => $order_data["photograph"],
                    "items" => []
                );

                foreach($items as $item) {
                    if ($item["status"] == 0) {
                        array_push(
                            $order_status["items"],
                            array(
                                "product_name" => ItemManagement::get_product_name($item["id"]),
                                "estimated_time" => OrderManagement::get_resting_time_for_completion($item["estimated_time"])
                            )
                        );
                    }
                }

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

        if ($estimated_completion < $date_time_object->getTimestamp()) {

            return "Su pedido pronto estará en su mesa.";

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

    public static function generate_random_id() {

        $ret_id = '';

        for ($i = 0; $i < 5; $i++) {
            if ( random_int(0, 1) == 0 ) {
                $ret_id .= chr( mt_rand(48, 57) );
            } else {
                $ret_id .= chr( mt_rand(97, 122) );
            }
        }

        return $ret_id;
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