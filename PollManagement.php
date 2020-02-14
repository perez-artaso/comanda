<?php

class PollManagement {

    public static function create_poll ($order_id, $subject, $score, $comments) {
        try {

            if (
                !self::already_polled($order_id, $subject)
            ) {
                if (self::poll_available($order_id)) {

                    $DBCon = DBConnection::NewDBConnection();
                    $query = $DBCon->SetQuery("INSERT INTO polls (order_id, subject, score, comments) VALUES (?, ?, ?, ?)");
                    $query->bindValue(1, $order_id, PDO::PARAM_INT);
                    $query->bindValue(2, $subject, PDO::PARAM_INT);
                    $query->bindValue(3, $score, PDO::PARAM_INT);
                    $query->bindValue(4, $comments, PDO::PARAM_STR);
        
                    $query->execute();
        
                    return self::response_formatter(
                        200,
                        "Encuesta registrada con éxito."
                    );

                } else {
                    throw new Exception ("La encuesta no se encuentra disponible (sólo se habilita antes del cierre de la mesa).");
                }
            } else {
                throw new Exception ("Esta encuesta ya fue registrada.");
            }

        } catch (Exception $e) {
            return self::response_formatter(
                500,
                "Ha ocurrido una excpeción: " . $e->getMessage()
            );
        }

    }

    public static function read_polls() {

        try {

            $DBCon = DBConnection::NewDBConnection();
            $query = $DBCon->SetQuery("SELECT * FROM polls");
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

    public static function delete_poll ($id) {
        try {

            if(self::poll_exists($id)) {
                $DBCon = DBConnection::NewDBConnection();
                $query = $DBCon->SetQuery("DELETE FROM polls WHERE id = ?");
                $query->bindValue(1, $id, PDO::PARAM_INT);
                $query->execute();
    
                return self::response_formatter(
                    200,
                    "Registro eliminado con éxito."
                );

            } else {
                
                return self::response_formatter(
                    404,
                    "El registro no existe en la base de datos."
                );

            }            

        } catch (Exception $e) {
            return self::response_formatter(
                500,
                "Ha ocurrido una excpeción: " . $e->getMessage()
            );
        }
    }

    static function poll_exists ($id) {
        $DBCon = DBConnection::NewDBConnection();

        $query = $DBCon->SetQuery("SELECT * FROM polls WHERE id = ?");
        $query->bindValue(1, $id, PDO::PARAM_INT);
        $query->execute();

        return $exists = ($query->fetchAll() != []) ? true : false;
    }

    static function already_polled ($order_id, $subject) {
        $DBCon = DBConnection::NewDBConnection();

        $query = $DBCon->SetQuery("SELECT * FROM polls WHERE order_id = ? AND subject = ?");
        $query->bindValue(1, $order_id, PDO::PARAM_INT);
        $query->bindValue(2, $subject, PDO::PARAM_INT);
        $query->execute();

        return $polled = ($query->fetchAll() != []) ? true : false;
    }

    public static function get_polls_by_order_id($order_id) {
        $DBCon = DBConnection::NewDBConnection();

        $query = $DBCon->SetQuery("SELECT * FROM polls WHERE order_id = ?");
        $query->bindValue(1, $order_id, PDO::PARAM_INT);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function poll_available ($order_id) {
        $DBCon = DBConnection::NewDBConnection();

        $query = $DBCon->SetQuery("SELECT status FROM tables WHERE id IN (SELECT table_id FROM orders WHERE id = ?)");
        $query->bindValue(1, $order_id, PDO::PARAM_INT);
        $query->execute();
        $table_satus = $query->fetchAll(PDO::FETCH_ASSOC)[0]["status"];

        return $available = ($table_satus == 2) ? true : false;
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