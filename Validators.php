<?php

require_once 'vendor/autoload.php';
Use Respect\Validation\Validator as v;

class Validators {
    public static function validate_username ($user_name) {

        if(strlen($user_name) < 4) {
            throw new Exception ("El nombre ingresado es demasiado corto. El mismo debe superar al menos los 3 caracteres.");  
        }

        foreach (str_split(strtolower($user_name)) as $char) {
            if (
                $char != 'ñ' &&
                $char != utf8_encode(chr(225)) &&
                $char != utf8_encode(chr(233)) &&
                $char != utf8_encode(chr(237)) &&
                $char != utf8_encode(chr(243)) &&
                $char != utf8_encode(chr(250)) &&
                !v::alpha()->validate($char)
            ) {
                throw new Exception ("Cáracter inválido identificado. Evite los ascentos y cualquier caracter no alfabético.");
            }
        }

    }

    public static function validate_table_description ($description) {
        if (
            strlen($description) < 6
        ) {
            throw new Exception ("Descripción demasiado corta. Debe contener al menos 6 caracteres.");
        }
    }

    public static function validate_work_station($work_station) {
        if (
            ($work_station < 1 || $work_station > 7)
        ) {
            throw new Exception ("Código de estación de trabajo incorrecto (rango aceptado: 1 - 7). Ver documentación.");
        }
    }

    public static function validate_password ($password) {
        if (
            strlen($password) < 6
        ) {
            throw new Exception ("Contraseña demasiado corta. Debe contener al menos 6 caracteres.");
        }
    }

    public static function validate_status ($status) {
        if (
            $status != 0 &&
            $status != 1 &&
            $status != 2 &&
            $status != 3
        ) {
            throw new Exception ("Estado inválido. Ver documentación.");
        }
    }

    public static function validate_timestamp ($timestamp) {
        if ($timestamp > PHP_INT_MAX || !v::intVal()->validate($timestamp)) {
            throw new Exception ("El formato de tiempo proporcionado está corrompido (unix timestamp inválido).");
        }
    } 

    public static function validate_integer ($integer, $message) {
        if(!v::intVal()->validate($integer)) {
            throw new Exception ($message);
        }
    }

    public static function validate_inputted_string_date($date) {
        $exploded_date = explode("/", $date);
        if(count($exploded_date) !== 3) {
            throw new Exception ("Error de formato en fecha. Se espera el formato dd/mm/aaaa");
        }
        if (strlen($exploded_date[0]) !== 2) {
            throw new Exception ("Error de formato en día de fecha. Se espera el formato dd/mm/aaaa");
        }
        if (strlen($exploded_date[1]) !== 2) {
            throw new Exception ("Error de formato en mes de fecha. Se espera el formato dd/mm/aaaa");
        }
        if (strlen($exploded_date[2]) !== 4) {
            throw new Exception ("Error de formato en año de fecha. Se espera el formato dd/mm/aaaa");
        }
    }

    public static function validate_employee_status ($status) {
        if ($status != 1 && $status != 0) {
            throw new Exception ("El estado del empleado sólo puede ser 1 para 'habilitado' y 0 para 'dado de baja'");
        }
    }
}