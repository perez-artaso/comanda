<?php

include_once "DBConnection.php";

class ProductManagement {

    public static function create_product ($name, $price, $subcategory_id, $work_station) {
        try {

            $DBCon = DBConnection::NewDBConnection();
            $query = $DBCon->SetQuery("INSERT INTO products (name, price, subcategory_id, work_station) VALUES (?, ?, ?, ?)");
            $query->bindValue(1, $name, PDO::PARAM_STR);
            $query->bindValue(2, $price, PDO::PARAM_STR);
            $query->bindValue(3, $subcategory_id, PDO::PARAM_INT);
            $query->bindValue(4, $work_station, PDO::PARAM_INT);
            $query->execute();

            return self::response_formatter(
                200,
                "Producto cargado con éxito"
            );

        } catch (Exception $e) {

            return self::response_formatter (
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );

        }
    }

    public static function create_subcategory ($description, $work_station) {
        try {

            $DBCon = DBConnection::NewDBConnection();
            $query = $DBCon->SetQuery("INSERT INTO product_subcategories (description, work_station) VALUES (?, ?)");
            $query->bindValue(1, $description, PDO::PARAM_STR);
            $query->bindValue(2, $work_station, PDO::PARAM_INT);
            $query->execute();

            return self::response_formatter(
                200,
                "Subcategoría registrada con éxito"
            );

        } catch (Exception $e) {

            return self::response_formatter (
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );

        }
    }

    public static function read_subcategories () {
        try {

            $DBCon = DBConnection::NewDBConnection();
            $query = $DBCon->SetQuery("SELECT * FROM product_subcategories");
            $query->execute();

            $result = $query->fetchAll(PDO::FETCH_ASSOC);

            return self::response_formatter(
                200,
                $result
            );

        } catch (Exception $e) {

            return self::response_formatter (
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );

        }
    }

    static function read_products() {
        $DBCon = DBConnection::NewDBConnection();
        $query = $DBCon->SetQuery("SELECT * FROM products");
        $query->execute();
        $products = $query->fetchAll(PDO::FETCH_ASSOC);

        return $products;
    }

    public static function get_menu () {
        try {
            $work_stations = [3, 4, 5, 6];
            $products = self::read_products();
            $subcategories = (array) json_decode(self::read_subcategories());
            $subcategories = (array) $subcategories["message"];

            $areas = ["Cocina", "Candy Bar", "Chopera", "Tragos"];

            $menu = array(
                $areas[0] => array(),
                $areas[1] => array(),
                $areas[2] => array(),
                $areas[3] => array()
            );

            for ( $i = 0; $i < count($work_stations); $i++ ) {
                for ($j = 0; $j < count($subcategories); $j++) {
                    if ($work_stations[$i] == $subcategories[$j]->work_station) {
                        $menu[$areas[$i]][$subcategories[$j]->description] = array();
                        for ($k = 0; $k < count($products); $k++) {
                            if ($products[$k]["subcategory_id"] == $subcategories[$j]->id) {
                                $menu[$areas[$i]][$subcategories[$j]->description][] = $products[$k];
                            }
                        }       
                    }
                }
            }

            return self::response_formatter(
                200,
                $menu
            );

        } catch (Exception $e) {
            return self::response_formatter (
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );
        }
    }

    static function product_exists($id) {
        $DBCon = DBConnection::NewDBConnection();

        $query = $DBCon->SetQuery("SELECT * FROM products WHERE id = ?");
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
}