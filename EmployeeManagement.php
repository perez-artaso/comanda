<?php
use \Firebase\JWT\JWT;
include_once('DBConnection.php');
include_once('Validators.php');

class EmployeeManagement {

    public static function create_employee($password, $name, $work_station) {
        try {
            
            Validators::validate_work_station($work_station);
            Validators::validate_username($name);

            $DBCon = DBConnection::NewDBConnection();
            $query = $DBCon->SetQuery('INSERT INTO employees (password, name, work_station) VALUES (?, ?, ?)');
            $query->bindValue(1, password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
            $query->bindValue(2, $name, PDO::PARAM_STR);
            $query->bindValue(3, $work_station, PDO::PARAM_INT);
            $query->execute();

            $query = $DBCon->SetQuery("SELECT id FROM employees WHERE name LIKE ?");
            $query->bindValue(1, $name, PDO::PARAM_STR);
            $query->bindValue(2, $password, PDO::PARAM_STR);
            $query->execute();
            $result = $query->fetchAll();

            if ($result !== []) {
                return self::response_formatter(
                    202,
                    "Carga realizada con éxito. El id de su empleado es: " . $result[count($result) - 1]["id"] . "." 
                );              
            } else return self::response_formatter(
                404,
                "Falló la carga del empleado. El motor de la base de datos no está respondiendo como debería."
            );

        } catch(Exception $e) {
            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );
        }
    }

    public static function change_password($id, $password, $new_password) {
        try {
                $DBCon = DBConnection::NewDBConnection();
                if(self::employee_exists($id)) {
                    if (self::permission_granted($id, $password)) {
                        $query = $DBCon->SetQuery('UPDATE employees SET password = ? WHERE id = ?');
                        $query->bindValue(1, password_hash($new_password, PASSWORD_DEFAULT), PDO::PARAM_STR);
                        $query->bindValue(2, $id, PDO::PARAM_INT);
                        $query->execute();
                        return self::response_formatter(
                            202,
                            "Contraseña modificada con éxito."
                        );
                    } else {
                        return self::response_formatter(
                            403,
                            "Contraseña incorrecta."
                        );
                    }            
                } else {
                    return self::response_formatter(
                        404,
                        "ID no registrado. El usuario no existe."
                    );
                }   

        } catch(Exception $e) {
            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );
        }
    }

    public static function login ($id, $password) {
        try {
            $DBCon = DBConnection::NewDBConnection();

            if (self::employee_exists($id)) {

                if (self::permission_granted($id, $password)) {
                    $user = self::get_employee($id);
                    if ($user["status"] == "1") {
                        $now = new DateTime();
                        $expirationTime = new DateTime('+9 hours');
                        return self::response_formatter(
                            202,
                            JWT::encode(array(
                                'id' => $user['id'],
                                'name' => $user['name'],
                                'work_station' => $user['work_station'],  
                                'iat' => $now->getTimeStamp(),
                                'exp' => $expirationTime->getTimeStamp()
                            ), $user['password'])
                        );
                    } else {
                        return self::response_formatter(
                            403,
                            "El usuario se encuentra inhabilitado."
                        );
                    }
 
                }else {
                    return self::response_formatter(
                        403,
                        "Contraseña incorrecta."
                    );
                }
            } else {
                return self::response_formatter(
                    404,
                    "Id desconocido. El usuario no existe."
                );
            }
        } catch (Exception $e) {
            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );
        }
    }

    public static function employee_list() {
        try {
            $DBCon = DBConnection::NewDBConnection();
            $query = $DBCon->SetQuery('SELECT * FROM employees');
            $query->execute();
            $employees = $query->fetchAll();
            return $employees;
        } catch(Exception $e) {
            return false;
        }
        return true;
    }

    public static function delete_employee($id) {
        try {
            $DBCon = DBConnection::NewDBConnection();
            $query = $DBCon->SetQuery('DELETE FROM employees WHERE id = ?');
            $query->bindValue(1, $id, PDO::PARAM_INT);
            $query->execute();
            return self::response_formatter(
                202,
                "Empleado eliminado con éxito."
            );
        } catch (Exception $e) {
            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );
        }
    }

    public static function update_employee($id, $name, $work_station) {
        $DBCon = DBConnection::NewDBConnection();
        try{
            $query = $DBCon->SetQuery('UPDATE employees SET name = ?, work_station = ? WHERE id = ?');
            $query->bindValue(1, $name, PDO::PARAM_STR);
            $query->bindValue(2, $work_station, PDO::PARAM_STR);
            $query->bindValue(3, $id, PDO::PARAM_STR);  
            $query->execute();

            return self::response_formatter(
                202,
                "Información del empleado actualizada con éxito."
            );

        } catch (Exception $e) {
            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );
        }
    }

    public static function update_status ($id, $status) {

        try {
            $DBCon = DBConnection::NewDBConnection();

            if ($status == "0" || $status == "1") {
                if (self::employee_exists($id)) {
                    $query = $DBCon->SetQuery("UPDATE employees SET status = ? WHERE id = ?");
                    $query->bindValue(1, $status, PDO::PARAM_INT);
                    $query->bindValue(2, $id, PDO::PARAM_INT);
                    $query->execute();
        
                    $status_word = ($status == "0") ? "deshabilitado." : "habilitado.";
        
                    return self::response_formatter(
                        202,
                        "Estado del empleado actualizado: " . $status_word
                    );
                } else {
                    return self::response_formatter(
                        404,
                        "Id no registrado. El empleado no existe."
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

    static function employee_exists ($id) {
        $DBCon = DBConnection::NewDBConnection();

        $query = $DBCon->SetQuery("SELECT * FROM employees WHERE id = ?");
        $query->bindValue(1, $id, PDO::PARAM_INT);
        $query->execute();

        return $exists = ($query->fetchAll() != []) ? true : false;
    }

    static function permission_granted ($id, $password) {
        $DBCon = DBConnection::NewDBConnection();

        $query = $DBCon->SetQuery("SELECT password FROM employees WHERE id = ?");
        $query->bindValue(1, $id, PDO::PARAM_INT);
        $query->execute();

        $result = $query->fetchAll();

        return $granted = (password_verify($password, $result[0]["password"])) ? true : false;
    }

    public static function credential_check ($token) {

        $decoded_jwt_body = self::get_decoded_jwt_body($token);
        if (self::employee_exists($decoded_jwt_body["id"])) {

            $user_data = self::get_employee($decoded_jwt_body["id"]);
            JWT::decode($token, $user_data["password"], array('HS256'));

            return $user_data["work_station"];

        } else {
            return -1;
        }

    }

    static function get_employee($id) {
        $DBCon = DBConnection::NewDBConnection();
        $query = $DBCon->SetQuery("SELECT * FROM employees WHERE id = ?");
        $query->bindValue(1, $id, PDO::PARAM_INT);
        $query->execute();

        $result = $query->fetchAll();

        return array (
            "id" => $result[0]["id"],
            "name" => $result[0]["name"],
            "work_station" => $result[0]["work_station"],
            "password" => $result[0]["password"],
            "status" => $result[0]["status"]
        );
    }

    public static function get_pending_items($token) {

        $work_station = self::get_decoded_jwt_body($token)["work_station"];
        
        $DBCon = DBConnection::NewDBConnection();

        $query = $DBCon->SetQuery(
            "SELECT * FROM items
            WHERE product_id IN
            (SELECT id FROM products WHERE work_station = ?)
            AND status = ?"
        );

        $query->bindValue(1, $work_station, PDO::PARAM_INT);
        $query->bindValue(2, 0, PDO::PARAM_INT);

        $query->execute();

        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    public static function get_items_being_prepared($token) {

        $work_station = self::get_decoded_jwt_body($token)["work_station"];
        
        $DBCon = DBConnection::NewDBConnection();

        $query = $DBCon->SetQuery(
            "SELECT * FROM items
            WHERE product_id IN
            (SELECT id FROM products WHERE work_station = ?)
            AND status = ?"
        );

        $query->bindValue(1, $work_station, PDO::PARAM_INT);
        $query->bindValue(2, 1, PDO::PARAM_INT);

        $query->execute();

        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    static function area_matches ($item_list, $item_id) {

        foreach ($item_list as $item) {
            if ($item["id"] == $item_id) {
                return true;
            }
        }

        return false;
    }

    static function can_mark_as_ready($id_item, $id_employee) {
        $DBCon = DBConnection::NewDBConnection();
        $query = $DBCon->SetQuery("SELECT * FROM items WHERE id = ? AND taken_by = ?");
        $query->bindValue(1, $id_item, PDO::PARAM_INT);
        $query->bindValue(2, $id_employee, PDO::PARAM_INT);
        $query->execute();

        return $can = ($query->fetchAll() != []) ? true : false;
    }

    public static function take_item ($token, $item_id, $estimated_time) {

        try {

            Validators::validate_integer (
                $estimated_time, 
                "La cantidad de tiempo estimada para la preparación debe expresarse en números enteros."
            );

            $date_object = new DateTime("+" . $estimated_time . " minutes");

            $estimated_time = $date_object->getTimestamp();

            $pending_items = self::get_pending_items($token);

            $item = ItemManagement::get_item($item_id);

            if (ItemManagement::item_exists($item_id)) {

                if ($item["status"] == 0) {
                    if (self::area_matches($pending_items, $item_id)) {

                        $DBCon = DBConnection::NewDBConnection();

                        $query = $DBCon->SetQuery(
                            "UPDATE items SET taken_by = ?, taken_at = ?, estimated_time = ?, status = ? WHERE id = ?"
                        );

                        $query->bindValue(1, self::get_decoded_jwt_body($token)["id"], PDO::PARAM_STR);
                        $query->bindValue(2, time(), PDO::PARAM_STR);
                        $query->bindValue(3, $estimated_time, PDO::PARAM_STR);
                        $query->bindValue(4, 1, PDO::PARAM_INT);
                        $query->bindValue(5, $item_id, PDO::PARAM_INT);

                        $query->execute();

                        return self::response_formatter (
                            200,
                            "Item tomado con éxito."
                        );

                    } else {
                        throw new Exception ("El item no pertenece al área del empleado.");
                    }
                } else {
                    throw new Exception ("El item ya fue asignado.");
                }

            } else {
                throw new Exception ("El item indicado no existe en el registro.");
            }

        } catch (Exception $e) {

            return self::response_formatter (
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );

        }

    }

    public static function mark_as_ready($token, $item_id) {
        try {
            if (
                self::area_matches(
                    self::get_items_being_prepared($token),
                    $item_id
                )
            ) {
                if (self::can_mark_as_ready(
                    $item_id,
                    self::get_decoded_jwt_body($token)["id"]
                )) {
                    $item = ItemManagement::get_item($item_id);

                    if ($item["status"] == 1) {

                        $DBCon = DBConnection::NewDBConnection();
                        $query = $DBCon->SetQuery("UPDATE items SET ready_at = ?, status = ? WHERE id = ?");
                        
                        $query->bindValue(1, time(), PDO::PARAM_STR);
                        $query->bindValue(2, 2, PDO::PARAM_INT);
                        $query->bindValue(3, $item_id, PDO::PARAM_INT);

                        $query->execute();

                        return self::response_formatter (
                            200,
                            "Preparación registrada."
                        );

                    } else {
                        throw new Exception ("El item ya no se encuentra en preparación.");
                    }

                } else {
                    throw new Exception ("Sólo el empleado que tomó el pedido puede marcarlo como listo.");
                }

            } else {
                throw new Exception ("El item no pertenece al área del empleado.");
            }
        } catch (Exception $e) {

            return self::response_formatter (
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );

        }
    }

    static function get_decoded_jwt_body($jwt) {
        return (array) json_decode ( 
            base64_decode( 
                explode( 
                    "." ,
                    $jwt
                )[1]
            )
        );
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


?>
