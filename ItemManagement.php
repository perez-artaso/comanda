<?php

include_once('DBConnection.php');

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
                    $query = $DBCon->SetQuery("UPDATE item SET status = ? WHERE id = ?");
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

    static function response_formatter ($status_code, $message) {

        return json_encode (
            array (
                "status_code" => $status_code,
                "message" => $message
            )
        );

    }

}