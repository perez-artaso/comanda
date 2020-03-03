<?php

class PollManagement {

    public static function create_poll ($order_id, $subject, $score) {
        

            if (
                !self::already_polled($order_id, $subject)
            ) {
                if (self::poll_available($order_id)) {

                    $DBCon = DBConnection::NewDBConnection();
                    $query = $DBCon->SetQuery("INSERT INTO polls (order_id, subject, score) VALUES (?, ?, ?)");
                    $query->bindValue(1, $order_id, PDO::PARAM_STR);
                    $query->bindValue(2, $subject, PDO::PARAM_INT);
                    $query->bindValue(3, $score, PDO::PARAM_INT);
        
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

        

    }

    public static function insert_polls($order_id, $scores, $comment) {

        try {
            foreach ($scores as $score) {
                $decoded_score = json_decode($score, true);
                self::create_poll($order_id, $decoded_score["subject"], $decoded_score["score"]);
            }
    
            self::insert_comment($order_id, $comment);

            return self::response_formatter(
                200,
                "Encuesta registrada con éxito"
            );

        } catch (Exception $e) {
            return self::response_formatter(
                500,
                "Ha ocurrido una excpeción: " . $e->getMessage()
            );
        }       
        

    }

    static function insert_comment($order_id, $comment) {
        if (!self::already_commented($order_id)) {
            $DBCon = DBConnection::NewDBConnection();
            $query = $DBCon->SetQuery("INSERT INTO comments (order_id, comment) VALUES (?, ?)");
            $query->bindValue(1, $order_id, PDO::PARAM_INT);
            $query->bindValue(2, $comment, PDO::PARAM_STR);
    
            $query->execute();
        } else throw new Exception ("Ya existe un comentario registrado para esta encuesta.");        
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

    public static function delete_poll ($order_id) {
        try {

            if(self::poll_exists($order_id)) {
                $DBCon = DBConnection::NewDBConnection();
                $query = $DBCon->SetQuery("DELETE FROM polls WHERE order_id = ?");
                $query->bindValue(1, $order_id, PDO::PARAM_INT);
                $query->execute();
                
                $query = $DBCon->SetQuery("DELETE FROM comments WHERE order_id = ?");
                $query->bindValue(1, $order_id, PDO::PARAM_INT);
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

    static function poll_exists ($order_id) {
        $DBCon = DBConnection::NewDBConnection();

        $query = $DBCon->SetQuery("SELECT * FROM polls WHERE order_id = ?");
        $query->bindValue(1, $order_id, PDO::PARAM_INT);
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

    static function already_commented ($order_id) {
        $DBCon = DBConnection::NewDBConnection();

        $query = $DBCon->SetQuery("SELECT * FROM comments WHERE order_id = ?");
        $query->bindValue(1, $order_id, PDO::PARAM_INT);
        $query->execute();

        return $commented = ($query->fetchAll() != []) ? true : false;
    }

    public static function get_polls_by_order_id($order_id) {
        $DBCon = DBConnection::NewDBConnection();

        $query = $DBCon->SetQuery("SELECT * FROM polls WHERE order_id = ?");
        $query->bindValue(1, $order_id, PDO::PARAM_STR);
        $query->execute();

        $polls = $query->fetchAll(PDO::FETCH_ASSOC);

        return $polls;
    }

    public static function poll_available ($order_id) {
        $DBCon = DBConnection::NewDBConnection();

        $query = $DBCon->SetQuery("SELECT status FROM tables WHERE id IN (SELECT table_id FROM orders WHERE id = ?)");
        $query->bindValue(1, $order_id, PDO::PARAM_INT);
        $query->execute();
        $table_satus = $query->fetchAll(PDO::FETCH_ASSOC)[0]["status"];

        return $available = ($table_satus == 3) ? true : false;
    }

    public static function get_best_commentaries ($amount) {
        
        $DBCon = DBConnection::NewDBConnection();

        $query = $DBCon->SetQuery(
            "SELECT order_id, comment FROM comments 
            WHERE order_id IN ( 
                SELECT * FROM ( 
                    SELECT order_id FROM polls 
                    GROUP BY order_id 
                    ORDER BY SUM(score) 
                    DESC 
                    LIMIT ? 
                ) as t 
            )"
        );

        $query->bindValue(1, $amount, PDO::PARAM_INT);
        $query->execute();

        return self::response_formatter(
            200,
            $query->fetchAll(PDO::FETCH_ASSOC)
        );
        
    }

    public static function get_worst_commentaries ($amount) {
        
        $DBCon = DBConnection::NewDBConnection();

        $query = $DBCon->SetQuery(
            "SELECT order_id, comment FROM comments 
            WHERE order_id IN ( 
                SELECT * FROM ( 
                    SELECT order_id FROM polls 
                    GROUP BY order_id 
                    ORDER BY SUM(score) 
                    ASC 
                    LIMIT ? 
                ) as t 
            )"
        );

        $query->bindValue(1, $amount, PDO::PARAM_INT);
        $query->execute();

        return self::response_formatter(
            200,
            $query->fetchAll(PDO::FETCH_ASSOC)
        );
        
    }

    public static function get_between_dates_scores_and_commentaries($from, $to) {
        try {

            Validators::validate_inputted_string_date($from);
            Validators::validate_inputted_string_date($to);
            $exploded_from = explode("/", $from);
            $exploded_to = explode("/", $to);
    
            $from_timestamp = mktime(00, 0, 0, $exploded_from[1], $exploded_from[0], $exploded_from[2]);
            $to_timestamp = mktime(00, 0, 0, $exploded_to[1], $exploded_to[0], $exploded_to[2]);

            $DBCon = DBConnection::NewDBConnection();
            $query = $DBCon->SetQuery(
                "SELECT * FROM polls 
                WHERE order_id IN (
                    SELECT id FROM orders 
                    WHERE date >= ? AND date <= ?
                )"
            );
            $query->bindValue(1, $from_timestamp, PDO::PARAM_INT);
            $query->bindValue(2, $to_timestamp, PDO::PARAM_INT);
            $query->execute();
    
            $polls = $query->fetchAll(PDO::FETCH_ASSOC);

            $formatted_response = array();

            foreach($polls as $poll) {
                $formatted_response[$poll["order_id"]][] = array(
                    "subject" => $poll["subject"],
                    "subject_name" => self::verbalize_subject($poll["subject"]),
                    "score" => $poll["score"]
                );
            }

            $DBCon = DBConnection::NewDBConnection();

            foreach($formatted_response as $order_id => $formatted_poll){
                $query = $DBCon->SetQuery("SELECT comment FROM comments WHERE order_id = ?");
                $query->bindValue(1, $order_id, PDO::PARAM_STR);
                $query->execute();
                $comment = $query->fetchAll(PDO::FETCH_ASSOC);
                if ($comment != []) {
                    $formatted_response[$order_id]["comment"] = $comment[0]["comment"];
                }
            }
    
            return self::response_formatter(
                200,
                $formatted_response
            ); 

        } catch (Exception $e) {

            return self::response_formatter(
                500,
                "Ha ocurrido una excpeción: " . $e->getMessage()
            );

        }
    }

    static function verbalize_subject($subject) {
        switch($subject) {
            case 1:
                return "Mesa";
            break;
            case 2:
                return "Restaurante";
            break;
            case 3:
                return "Mozo";
            break;
            case 4:
                return "Cocinero";
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