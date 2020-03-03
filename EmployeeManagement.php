<?php
use \Firebase\JWT\JWT;
include_once('DBConnection.php');
include_once('Validators.php');

class EmployeeManagement {

    public static function create_employee($password, $name, $work_station) {
        try {
            
            Validators::validate_work_station($work_station);
            Validators::validate_username($name);
            Validators::validate_password($password);

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
                Validators::validate_password($new_password);

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
                        self::register_action($id, "login");
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
            $employees = $query->fetchAll(PDO::FETCH_ASSOC);

            $formatted_response = array();

            foreach ($employees as $employee) {
                array_push(
                    $formatted_response,
                    array(
                        "id" => $employee["id"],
                        "name" => $employee["name"],
                        "work_station" => $employee["work_station"],
                        "occupation" => self::verbalize_work_stations($employee["work_station"])
                    )
                );
            }

            return self::response_formatter(
                200,
                $formatted_response
            );


        } catch(Exception $e) {
            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );
        }
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
       
        try{

            Validators::validate_username($name);
            Validators::validate_work_station($work_station);

            $DBCon = DBConnection::NewDBConnection();
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
            Validators::validate_employee_status($status);
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

    public static function get_employee($id) {

        if ( $id != 0) {
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
        } else {
            return array (
                "id" => 0,
                "name" => "N/N",
                "work_station" => 0,
                "password" => "N/N",
                "status" => 0
            );
        }
        
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

        $formatted_response = [];

        foreach ($result as $item) {
            array_push(
                $formatted_response,
                array(
                    "id" => $item["id"],
                    "order_id" => $item["order_id"],
                    "product" => ItemManagement::get_product_name($item["id"])
                )
            );
        }

        return $formatted_response;
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

                        $query = $DBCon->SetQuery(
                            "UPDATE tables SET status = ? WHERE id IN (
                                SELECT table_id FROM orders WHERE id IN (
                                    SELECT order_id FROM items WHERE id = ?
                                )
                            );"
                        );

                        $query->bindValue(1, 2, PDO::PARAM_INT);
                        $query->bindValue(2, $item_id, PDO::PARAM_INT);

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

    public static function get_operations_per_sector ($work_station) {

        $DBCon = DBConnection::NewDBConnection();

        $queryString = ($work_station == 2) ? "SELECT COUNT(*) as 'Pedidos' FROM orders" : 
        "SELECT COUNT(*) as 'Pedidos' FROM items WHERE status = 2 AND product_id IN (SELECT id FROM products WHERE work_station = $work_station)";
        $query = $DBCon->SetQuery($queryString);

        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function get_sector_amount_of_operations_report() {
        try {
            $response = array (
                "Mozos/as" => self::get_operations_per_sector(2),
                "Cocina" => self::get_operations_per_sector(3),
                "Candy Bar" => self::get_operations_per_sector(4),
                "Choperas" => self::get_operations_per_sector(5),
                "Bar" => self::get_operations_per_sector(6)
            );
            return self::response_formatter(
                200,
                $response
            );
        } catch (Exception $e) {

            return self::response_formatter (
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );

        }
    }

    public static function get_operations_per_sector_and_employee() {
        try {

            $waiters = self::get_employees_per_section(2);
            $cookery_employees = self::get_employees_per_section(3);
            $bakery_employees = self::get_employees_per_section(4);
            $brewery_employees = self::get_employees_per_section(5);
            $bar_employees = self::get_employees_per_section(6);

            $operations_per_sector = array(
                "Mozos" => $waiters,
                "Cocina" => $cookery_employees,
                "Candy Bar" => $bakery_employees,
                "Chopera" => $brewery_employees,
                "Tragos" => $bar_employees
            );

            foreach ($operations_per_sector as &$sector) {
                foreach ($sector as &$employee) {
                    $employee["operations"] = self::get_operation_count($employee["id"], $employee["work_station"]);
                }
            }

            return self::response_formatter(
                200,
                $operations_per_sector
            );


        } catch (Exception $e) {
            
            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );

        }
    }

    public static function get_operations_per_employee($employee_id) {
        try {

            if(self::employee_exists($employee_id)) {

                $employee_data = self::get_employee($employee_id);
            
                return self::response_formatter(
                    200,
                    array(
                        "id" => $employee_data["id"],
                        "name" => $employee_data["name"],
                        "work_station" => self::verbalize_work_stations($employee_data["work_station"]),
                        "operations" => self::get_operation_count($employee_data["id"], $employee_data["work_station"])
                    )
                );
            } else throw new Exception ("El empleado indicado no figura en el registro.");

        } catch (Exception $e) {

            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );

        }
    }

    static function get_operation_count ($employee_id, $work_station) {
        $DBCon = DBConnection::NewDBConnection();

        $queryString = ($work_station == 2) ? 
        "SELECT COUNT(*) FROM orders WHERE taken_by = ?" : 
        "SELECT COUNT(*) FROM items WHERE taken_by = ? AND status = 2";

        $query = $DBCon->SetQuery($queryString);
        $query->bindValue(1, $employee_id, PDO::PARAM_INT);

        $query->execute();
        $count = $query->fetchAll(PDO::FETCH_ASSOC);

        if ($count != []) {
            return $count[0]["COUNT(*)"];
        } else return 0;
    }

    static function get_employees_per_section($work_station) {

        $DBCon = DBConnection::NewDBConnection();

        $query = $DBCon->SetQuery("SELECT id, name, work_station FROM employees WHERE work_station = ?");
        $query->bindValue(1, $work_station, PDO::PARAM_INT);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function get_login_dates() {

        $DBCon = DBConnection::NewDBConnection();
        $query = $DBCon->SetQuery("SELECT * FROM logs WHERE action = ?");
        $query->bindValue(1, "login", PDO::PARAM_STR);

        $query->execute();

        $logins = $query->fetchAll(PDO::FETCH_ASSOC);

        $logins_report = array();

        foreach($logins as $login) {
            $logins_report[
                "ID: " . $login["employee_id"] . " (" . self::get_employee($login["employee_id"])["name"] . ")"
            ][] = date("d/m/Y H:i:s", $login["date"]);
        }

        return self::response_formatter(
            200,
            $logins_report
        );

    }

    public static function get_monthly_report() {
        
        try {
            $orders = OrderManagement::read_orders();

            $monthly_report = array();
    
            foreach ($orders as $order) {
                $month_and_year = date("m/Y", $order["date"]);
                $items = ItemManagement::get_items_by_order_id($order["id"]);
                
                if (!isset($monthly_report[$month_and_year]["total_amount_of_items"])) {
                    $monthly_report[$month_and_year]["total_amount_of_items"] = 0;
                }
                
                if (isset($monthly_report[$month_and_year]["items"])) {
                    
                    foreach ($items as $item) {
                        if ($item["status"] == 2) {
                            array_push($monthly_report[$month_and_year]["items"], $item);
                            $monthly_report[$month_and_year]["total_amount_of_items"]++;
                        }                    
                    }
    
                } else {
    
                    $monthly_report[$month_and_year]["items"] = [];
    
                    foreach ($items as $item) {
                        if ($item["status"] == 2) {
                            array_push($monthly_report[$month_and_year]["items"], $item);
                            $monthly_report[$month_and_year]["total_amount_of_items"]++;
                        }
                    }
                }
            }
    
            foreach ($monthly_report as &$month) {
                foreach($month["items"] as $item) {
                    if (isset(
                        $month[
                            "ID " . $item["taken_by"] . "(" . self::get_employee($item["taken_by"])["name"].")"
                        ]
                    )) {
                        $month[
                            "ID " . $item["taken_by"] . "(" . self::get_employee($item["taken_by"])["name"].")"
                        ]["amount_of_orders_taken"]++;
                        $month[
                            "ID " . $item["taken_by"] . "(" . self::get_employee($item["taken_by"])["name"].")"
                        ]["average"] = (($month[
                            "ID " . $item["taken_by"] . "(" . self::get_employee($item["taken_by"])["name"].")"
                        ]["amount_of_orders_taken"] / $month["total_amount_of_items"]) * 100);
                    } else {
                        $month[
                            "ID " . $item["taken_by"] . "(" . self::get_employee($item["taken_by"])["name"].")"
                        ]["amount_of_orders_taken"] = 1;
                        $month[
                            "ID " . $item["taken_by"] . "(" . self::get_employee($item["taken_by"])["name"].")"
                        ]["average"] = (($month[
                            "ID " . $item["taken_by"] . "(" . self::get_employee($item["taken_by"])["name"].")"
                        ]["amount_of_orders_taken"] / $month["total_amount_of_items"]) * 100);
                    }
                }
            }
    
            foreach ($monthly_report as &$report) {
                unset($report["items"]);
            }
    
            return self::response_formatter(
                200,
                $monthly_report
            );

        } catch (Exception $e) {

            return self::response_formatter(
                500,
                "Ha ocurrido una excepción: " . $e->getMessage()
            );

        }
    }

    public static function verbalize_work_stations($work_station) {
        switch($work_station) {

            case 1:
                return "Socio";
            break;

            case 2:
                return "Mozo/a";
            break;

            case 3:
                return "Cocina";
            break;

            case 4:
                return "Postres";
            break;

            case 5:
                return "Chopera";
            break;

            case 6:
                return "Bar";
            break;

            case 7:
                return "Data Entry";
            break;

            default:
                "Desconocido";
            break;
        }
    }

    static function register_action($employee_id, $action) {

        $DBCon = DBConnection::NewDBConnection();
        $query = $DBCon->SetQuery("INSERT INTO logs (employee_id, action, date) VALUES (?, ?, ?)");
        $query->bindValue(1, $employee_id, PDO::PARAM_INT);
        $query->bindValue(2, $action, PDO::PARAM_STR);
        $query->bindValue(3, time(), PDO::PARAM_INT);

        $query->execute();

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
