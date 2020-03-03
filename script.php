<?php

    date_default_timezone_set("America/Argentina/Buenos_Aires");
    require_once "OrderManagement.php";
    require_once "DBConnection.php";
    require_once "ItemManagement.php";
    require_once "EmployeeManagement.php";
    require_once "TableManagement.php";
    require_once "PollManagement.php";

    $a = utf8_encode(chr(225));
    $e = utf8_encode(chr(233));
    $i = utf8_encode(chr(237));
    $o = utf8_encode(chr(243));
    $u = utf8_encode(chr(250));

    $date_time_object = new DateTime('2020-02-13 15:25:00');
    echo TableManagement::get_less_billed_table(
        TableManagement::get_total_bills_per_table()
    );
    //echo mktime(00, 00, 00, 03, 02, 2020);
    //echo date("d/m/Y", 1580526000);

?>