<?php

include_once("EmployeeManagement.php");

class PDFManagement {

    public static function logins() {
        $inform = "<h1 style='text-align:center;'>Informe De Ingresos Al Sistema</h1>";
        $logins_data = json_decode(EmployeeManagement::get_login_dates(), true)["message"];
        foreach($logins_data as $key => $value) {
            $inform .= "<br><br><div style='border: 1px solid black; padding: 2%;'><h2>" . $key . "</h2><p><ul>";
            foreach($value as $employee_logins) {
                $inform .= "<li>".$employee_logins."</li>";
            }
            $inform .= "</ul></p></div>";
        }

        return $inform;
    }

    public static function orders_detail () {
        $inform = "<style>li{margin: 3px 2px;}</style><h1 style='text-align:center;'>Informe De Pedidos</h1>";

        $orders_detail = json_decode(OrderManagement::get_orders_detail(), true)["message"];

        foreach($orders_detail as $order) {
            $inform .= "<br><br>
                <div style='border: 1px solid black; padding: 2%;'><h2>Orden: " . $order["order_id"] . "</h2>
                <p>
                <img src='./images/" . $order["photograph"] ."'>" . "
                <ul>
                <li>Cliente: " . $order["client_name"] . "</li>" .
                "<li>Mesa: ID " . $order["table_description"]["id"] . " (". $order["table_description"]["description"] . ")</li>" .
                "<li>Inicio: " . $order["taken_at"] . "</li>" .
                "<li>Cierre: " . $order["closed_at"] . "</li>" .
                "<li>Tomó la orden: " . $order["taken_by"]["name"] . "</li>" .
                "<li>Pedidos: "; 
            foreach($order["items"] as $item) {
                $inform .= "<ul style='border: 1px solid black; padding: 2%;'>";
                $inform .= "<li>Producto: " . $item["product_name"] . "</li>";
                $inform .= "<li>Tomado por: " . $item["taken_by"]["name"] . "</li>";
                $inform .= "<li>Inicio: " . $item["taken_at"] . "</li>";
                $inform .= "<li>Estimación: " . $item["estimated_time"] . "</li>";
                $inform .= "<li>Terminado: " . $item["ready_at"] . "</li>";
                $inform .= "<li>Estado final: " . $item["status"] . "</li>";
                $inform .= "</ul>";
            }
            $inform .= "<li>Importe Final: <strong>$". $order["total_income"] ."</strong></li></ul></p></div>";
        }

        return $inform;
    }

}