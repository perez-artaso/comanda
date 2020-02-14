<?php

require_once("DBConnection.php");

class TableManagement {
    
    public static function create_table ($description) {

        try{
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

            $DBCon = DBConnection::NewDBConnection();

            $query = $DBCon->SetQuery("UPDATE tables SET status = ? WHERE id = ?");
            $query->bindValue(1, 0, PDO::PARAM_INT);
            $query->bindValue(2, $id, PDO::PARAM_INT);
            $query->execute();

            return self::response_formatter(
                200,
                "Mesa cerrada con éxito."
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

}