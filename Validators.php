<?php

require_once 'vendor/autoload.php';
Use Respect\Validation\Validator as v;

class Validators {
    public static function validate_username ($user_name) {
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

    public static function validate_work_station($work_station) {
        if (
            $work_station != 1 &&
            $work_station != 2 &&
            $work_station != 3 &&
            $work_station != 4 &&
            $work_station != 5 &&
            $work_station != 6 &&
            $work_station != 7
        ) {
            throw new Exception ("Código de estación de trabajo incorrecto (rango aceptado: 1 - 7). Ver documentación.");
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
        if ($timestamp > PHP_INT_MAX || !v::intval()->validate($timestamp)) {
            throw new Exception ("El formato de tiempo proporcionado está corrompido (unix timestamp inválido).");
        }
    } 

    public static function validate_integer ($integer, $message) {
        if(!v::intVal()->validate($integer)) {
            throw new Exception ($message);
        }
    }
}