<?php

    date_default_timezone_set("America/Argentina/Buenos_Aires");
    require_once "OrderManagement.php";
    require_once "DBConnection.php";
    require_once "ItemManagement.php";

    $a = utf8_encode(chr(225));
    $e = utf8_encode(chr(233));
    $i = utf8_encode(chr(237));
    $o = utf8_encode(chr(243));
    $u = utf8_encode(chr(250));

    $date_time_object = new DateTime('2020-02-13 15:25:00');

?>