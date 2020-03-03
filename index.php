<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\UploadedFile;
error_reporting(0);
date_default_timezone_set("America/Argentina/Buenos_Aires");

require_once "vendor/autoload.php";
require_once "EmployeeManagement.php";
require_once "ProductManagement.php";
require_once "TableManagement.php";
require_once "PollManagement.php";
require_once "ImageManagement.php";
require_once "OrderManagement.php";
require_once "PDFManagement.php";

$app = new \Slim\App(["settings" => ["displayErrorDetails" => true, "determineRouteBeforeAppMiddleware" => true]]);

$owner_middleware = function (Request $request, Response $response, $next) {
    $headers = $request->getHeaders();

    if (isset($headers["HTTP_AUTHORIZATION"])) {
        if (EmployeeManagement::credential_check($headers["HTTP_AUTHORIZATION"][0]) === 1) {
            $response = $next($request, $response);
        } else {
            $response->getBody()->write(
                json_encode (
                    array (
                        "status_code" => 403,
                        "message" => "Acceso denegado."
                    )
                )
            );
        }
    } else {
         $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 500,
                    "message" => "No se recibió el token de autenticación (ver documentación)."
                )
            )
        );
    }

    return $response;

};

$waiter_middleware = function (Request $request, Response $response, $next) {
    $headers = $request->getHeaders();

    if (isset($headers["HTTP_AUTHORIZATION"])) {
        if (EmployeeManagement::credential_check($headers["HTTP_AUTHORIZATION"][0]) === 1 || EmployeeManagement::credential_check($headers["HTTP_AUTHORIZATION"][0]) === 2) {
            $response = $next($request, $response);
        } else {
            $response->getBody()->write(
                json_encode (
                    array (
                        "status_code" => 403,
                        "message" => "Acceso denegado."
                    )
                )
            );
        }
    } else {
         $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 500,
                    "message" => "No se recibió el token de autenticación (ver documentación)."
                )
            )
        );
    }

    return $response;

};

$general_middleware = function (Request $request, Response $response, $next) {
    $headers = $request->getHeaders();

    if (isset($headers["HTTP_AUTHORIZATION"])) {
        if (EmployeeManagement::credential_check($headers["HTTP_AUTHORIZATION"][0]) != -1) {
            $response = $next($request, $response);
        } else {
            $response->getBody()->write(
                json_encode (
                    array (
                        "status_code" => 403,
                        "message" => "Acceso denegado."
                    )
                )
            );
        }
    } else {
         $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 500,
                    "message" => "No se recibió el token de autenticación (ver documentación)."
                )
            )
        );
    }

    return $response;

};

$app->get("/employees", function (Request $request, Response $response) {
    return $response->getBody()->write(
        EmployeeManagement::employee_list()
    );
})->add($owner_middleware);

$app->get("/employees/", function (Request $request, Response $response) {
    return $response->getBody()->write(
        EmployeeManagement::employee_list()
    );
})->add($owner_middleware);

$app->post("/employees/create", function(Request $request, Response $response) {
    $params = $request->getParsedBody();

    if (
        isset($params["password"]) &&
        isset($params["name"]) &&
        isset($params["work_station"])
    ) {
        return $response->getBody()->write(
            EmployeeManagement::create_employee($params["password"], $params["name"], $params["work_station"])
        );
    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }

})->add ($owner_middleware);

$app->post('/employees/change_password', function(Request $request, Response $response) {
    $params = $request->getParsedBody();

    if (
        isset($params["password"]) &&
        isset($params["new_password"]) &&
        isset($params["id"])
    ) {
        return $response->getBody()->write(
            EmployeeManagement::change_password($params["id"], $params["password"], $params["new_password"])
        );
    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }    

});

$app->post("/employees/delete", function(Request $request, Response $response) {
    $params = $request->getParsedBody();

    if (
        isset($params["id"])
    ) {
        return $response->getBody()->write(
            EmployeeManagement::delete_employee($params["id"])
        );
    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }    

})->add ($owner_middleware);

$app->post("/employees/update", function(Request $request, Response $response) {
    $params = $request->getParsedBody();

    if (
        isset($params["id"]) &&
        isset($params["name"]) &&
        isset($params["work_station"])
    ) {
        return $response->getBody()->write(
            EmployeeManagement::update_employee($params["id"], $params["name"], $params["work_station"])
        );
    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }

})->add ($owner_middleware);

$app->post("/employees/update_status", function (Request $request, Response $response) {
    $params = $request->getParsedBody();

    if (
        isset($params["id"]) &&
        isset($params["status"])
    ) {
        return $response->getBody()->write(
            EmployeeManagement::update_status($params["id"], $params["status"])
        );
    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }
})->add ($owner_middleware);

$app->post('/employees/login', function (Request $request, Response $response) {
    $params = $request->getParsedBody();

    if (
        isset($params["id"]) &&
        isset($params["password"])
    ) {
        return $response->getBody()->write(
            EmployeeManagement::login($params["id"], $params["password"])
        );
    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }

});

$app->post ("/products/create_product", function (Request $request, Response $response) {
    $params = $request->getParsedBody();

    if (
        isset($params["name"]) &&
        isset($params["price"]) &&
        isset($params["work_station"])
    ) {
        return $response->getBody()->write(
            ProductManagement::create_product($params["name"], $params["price"], $params["work_station"])
        );
    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }
});

$app->get("/menu/get_menu", function(Request $request, Response $response) {
    return $response->getBody()->write(
        ProductManagement::get_menu()
    );
});

$app->post("/tables/create", function (Request $request, Response $response) {
    $params = $request->getParsedBody();

    if (
        isset($params["description"])
    ) {
        return $response->getBody()->write(
            TableManagement::create_table($params["description"])
        );
    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }
})->add($owner_middleware);;

$app->post("/tables/delete", function (Request $request, Response $response) {
    $params = $request->getParsedBody();

    if (
        isset($params["id"])
    ) {
        return $response->getBody()->write(
            TableManagement::delete_table($params["id"])
        );
    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }
})->add($owner_middleware);

$app->post("/tables/update", function (Request $request, Response $response) {
    $params = $request->getParsedBody();

    if (
        isset($params["id"]) &&
        isset($params["description"])
    ) {
        return $response->getBody()->write(
            TableManagement::update_table($params["id"], $params["description"])
        );
    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }
})->add($owner_middleware);;

$app->post("/tables/change_table_status", function (Request $request, Response $response) {
    $params = $request->getParsedBody();

    if (
        isset($params["id"]) &&
        isset($params["status"])
    ) {
        return $response->getBody()->write(
            TableManagement::change_table_status($params["id"], $params["status"])
        );
    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }
})->add($waiter_middleware);

$app->post("/polls/create", function (Request $request, Response $response) {
    $params = $request->getParsedBody();

    if (
        isset($params["order_id"]) &&
        isset($params["scores"]) && 
        isset($params["comments"])
    ) {
        return $response->getBody()->write(
            PollManagement::insert_polls($params["order_id"], $params["scores"], $params["comments"])
        );
    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }
});

$app->get("/polls/", function(Request $request, Response $response) {
    return $response->getBody()->write(
        PollManagement::read_polls()
    );
})->add($owner_middleware);

$app->get("/polls", function(Request $request, Response $response) {
    return $response->getBody()->write(
        PollManagement::read_polls()
    );
})->add($owner_middleware);

$app->post("/polls/delete", function (Request $request, Response $response) {
    $params = $request->getParsedBody();

    if (
        isset($params["order_id"])
    ) {
        return $response->getBody()->write(
            PollManagement::delete_poll($params["order_id"])
        );
    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }
});

$app->post("/orders/create", function (Request $request, Response $response) {
    $headers = $request->getHeaders();
    $params = $request->getParsedBody();
    $uploadedFiles = $request->getUploadedFiles();

    if (
        isset($params["table_id"]) &&
        isset($params["client_name"]) &&
        isset($params["items"])
    ) {
        return $response->getBody()->write(
            OrderManagement::create_order(
                $headers["HTTP_AUTHORIZATION"][0],
                $params["client_name"], 
                ImageManagement::process_incoming_image($uploadedFiles['photograph'], $params["client_name"]), 
                $params["table_id"],
                $params["items"]                
            )
        );
    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }

})->add($waiter_middleware);

$app->post("/orders/add_items", function (Request $request, Response $response) {

    $params = $request->getParsedBody();

    if (
        isset($params["order_id"]) &&
        isset($params["items"])
    ) {
        return $response->getBody()->write(
            OrderManagement::add_items_to_order(
                $params["order_id"],
                $params["items"]                
            )
        );
    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }

})->add($waiter_middleware);

$app->get("/orders/get_active_orders", function (Request $request, Response $response) {

    return $response->getBody()->write(
        OrderManagement::get_active_orders()
    );

})->add($waiter_middleware);

$app->post("/orders/cancel_item", function (Request $request, Response $response) {

    $params = $request->getParsedBody();

    if (
        isset($params["item_id"])
    ) {
        return $response->getBody()->write(
            OrderManagement::cancel_item(
                $params["item_id"]        
            )
        );
    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }

})->add($waiter_middleware);

$app->post("/orders/close_order", function (Request $request, Response $response) {

    $params = $request->getParsedBody();

    if (
        isset($params["order_id"])
    ) {
        return $response->getBody()->write(
            OrderManagement::close_order(
                $params["order_id"]        
            )
        );
    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }

})->add($waiter_middleware);

$app->post("/orders/check", function (Request $request, Response $response) {

    $params = $request->getParsedBody();

    if (
        isset($params["order_id"])
    ) {
        return $response->getBody()->write(
            OrderManagement::check_order_status(
                $params["order_id"]        
            )
        );
    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }

});

$app->post("/tables/close_table", function (Request $request, Response $response) {

    $params = $request->getParsedBody();
    if (
        isset($params["table_id"])
    ) {
        return $response->getBody()->write(
            TableManagement::close_table($params["table_id"])
        );
    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }

})->add($owner_middleware);

$app->get("/items/get_pending_items", function (Request $request, Response $response) {

    try {

        $headers = $request->getHeaders();

        return $response->getbody()->write(
            json_encode(
                array(
                    "status_code" => 200,
                    "message" => EmployeeManagement::get_pending_items($headers["HTTP_AUTHORIZATION"][0])
                )
            )            
        );

    } catch (Exception $e) {
        return $response->getbody()->write(
            json_encode(
                array(
                    "status_code" => 500,
                    "message" => "Ha ocurrido una excepción: " . $e->getMessage()
                )
            )            
        );
    }

})->add($general_middleware);

$app->post("/items/take_item", function (Request $request, Response $response) {
    
    $params = $request->getParsedBody();
    $headers = $request->getHeaders();

    if (
        isset($params["item_id"]) &&
        isset($params["estimated_time"])
    ) {
        return $response->getBody()->write(
            EmployeeManagement::take_item (
                $headers["HTTP_AUTHORIZATION"][0],
                $params["item_id"],
                $params["estimated_time"]
            )
        );
    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }

})->add($general_middleware);

$app->post("/items/mark_as_ready", function (Request $request, Response $response) {

    $params = $request->getParsedBody();
    $headers = $request->getHeaders();

    if (
        isset($params["item_id"])
    ) {
        return $response->getBody()->write(
            EmployeeManagement::mark_as_ready (
                $headers["HTTP_AUTHORIZATION"][0],
                $params["item_id"]
            )
        );
    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }
})->add($general_middleware);

$app->get("/informs/get_operations_per_area", function (Request $request, Response $response) {
    
    return $response->getBody()->write(
        EmployeeManagement::get_sector_amount_of_operations_report()
    );

})->add($owner_middleware);

$app->get("/informs/get_operations_per_sector_and_employee", function (Request $request, Response $response) {
    
    return $response->getBody()->write(
        EmployeeManagement::get_operations_per_sector_and_employee()
    );

})->add($owner_middleware);

$app->post("/informs/get_operations_per_employee", function (Request $request, Response $response) {

    $params = $request->getParsedBody();

    if (
        isset($params["id"])
    ) {

        return $response->getBody()->write(
            EmployeeManagement::get_operations_per_employee($params["id"])
        );
    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }

})->add($owner_middleware);

$app->get("/informs/get_most_selled_products", function (Request $request, Response $response) {
    
    return $response->getBody()->write(
        ItemManagement::get_most_selled()
    );

})->add($owner_middleware);

$app->get("/informs/get_less_selled_products", function (Request $request, Response $response) {
    
    return $response->getBody()->write(
        ItemManagement::get_less_selled()
    );

})->add($owner_middleware);

$app->get("/informs/get_late_deliveries", function (Request $request, Response $response) {
    
    return $response->getBody()->write(
        ItemManagement::get_late_deliveries()
    );

})->add($owner_middleware);

$app->get("/informs/get_cancelled_items", function (Request $request, Response $response) {
    
    return $response->getBody()->write(
        ItemManagement::get_cancelled_items()
    );

})->add($owner_middleware);

$app->get("/informs/get_most_used_table", function (Request $request, Response $response) {
    
    return $response->getBody()->write(
        TableManagement::get_most_used_table()
    );

})->add($owner_middleware);

$app->get("/informs/get_less_used_table", function (Request $request, Response $response) {
    
    return $response->getBody()->write(
        TableManagement::get_less_used_table()
    );

})->add($owner_middleware);

$app->get("/informs/get_most_billed_table", function (Request $request, Response $response) {
    
    return $response->getBody()->write(
        TableManagement::get_most_billed_table(
            TableManagement::get_total_bills_per_table()
        )
    );

})->add($owner_middleware);

$app->get("/informs/get_less_billed_table", function (Request $request, Response $response) {
    
    return $response->getBody()->write(
        TableManagement::get_less_billed_table(
            TableManagement::get_total_bills_per_table()
        )
    );

})->add($owner_middleware);

$app->post("/informs/get_between_dates_table_income", function (Request $request, Response $response) {

    $params = $request->getParsedBody();

    if (
        isset($params["from"]) &&
        isset($params["to"]) && 
        isset($params["table_id"])
    ) {

        return $response->getBody()->write(
            TableManagement::get_between_dates_table_income($params["from"], $params["to"], $params["table_id"])
        );

    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }

})->add($owner_middleware);

$app->post("/informs/get_between_dates_income", function (Request $request, Response $response) {

    $params = $request->getParsedBody();

    if (
        isset($params["from"]) &&
        isset($params["to"])
    ) {

        return $response->getBody()->write(
            TableManagement::get_between_dates_table_income($params["from"], $params["to"], '%')
        );

    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }

})->add($owner_middleware);

$app->post("/informs/get_between_dates_scores_and_commentaries", function (Request $request, Response $response) {

    $params = $request->getParsedBody();

    if (
        isset($params["from"]) &&
        isset($params["to"])
    ) {

        return $response->getBody()->write(
            PollManagement::get_between_dates_scores_and_commentaries($params["from"], $params["to"])
        );

    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }

})->add($owner_middleware);

$app->post("/informs/get_best_commentaries", function (Request $request, Response $response) {

    $params = $request->getParsedBody();

    if (
        isset($params["amount"])
    ) {

        return $response->getBody()->write(
            PollManagement::get_best_commentaries($params["amount"])
        );

    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }

})->add($owner_middleware);

$app->post("/informs/get_worst_commentaries", function (Request $request, Response $response) {

    $params = $request->getParsedBody();

    if (
        isset($params["amount"])
    ) {

        return $response->getBody()->write(
            PollManagement::get_worst_commentaries($params["amount"])
        );

    } else {
        return $response->getBody()->write(
            json_encode (
                array (
                    "status_code" => 422,
                    "message" => "Los parámetros enviados en el cuerpo de la petición no coinciden con los esperados. Revise la documentación."
                )
            )
        );
    }

})->add($owner_middleware);

$app->get("/informs/get_orders_detail", function (Request $request, Response $response) {
    
    return $response->getBody()->write(
        OrderManagement::get_orders_detail()
    );

})->add($owner_middleware);

$app->get("/informs/get_monthly_income_average", function (Request $request, Response $response) {
    
    return $response->getBody()->write(
        OrderManagement::get_monthly_income_average()
    );

})->add($owner_middleware);

$app->get("/informs/get_monthly_average_per_table", function (Request $request, Response $response) {
    
    return $response->getBody()->write(
        TableManagement::get_monthly_average_per_table()
    );

})->add($owner_middleware);

$app->get("/informs/get_monthly_report", function (Request $request, Response $response) {
    
    return $response->getBody()->write(
        EmployeeManagement::get_monthly_report()
    );

})->add($owner_middleware);

$app->get("/informs/logins", function (Request $request, Response $response) {
    
    return $response->getBody()->write(
        EmployeeManagement::get_login_dates()
    );

})->add($owner_middleware);

$app->get("/tables/", function (Request $request, Response $response) {

    return $response->getBody()->write(
        TableManagement::read_tables()
    );

});

$app->get("/tables", function (Request $request, Response $response) {

    return $response->getBody()->write(
        TableManagement::read_tables()
    );

});

$app->get('/informs/pdf/logins', function (Request $request, Response $response) {

    $mpdf = new \Mpdf\Mpdf();
    $mpdf->WriteHTML(
        PDFManagement::logins()
    );
    $file = fopen("./mdpf.pdf", "w");    
    $mpdf->Output("./mdpf.pdf");
    fclose($file);
    $fh = fopen("./mdpf.pdf", "rb");
    $stream = new \Slim\Http\Stream($fh);
    $response = $response->withHeader('Content-Type', 'application/force-download');
    $response = $response->withHeader('Content-Description', 'File Transfer');
    $response = $response->withHeader('Content-Disposition', 'attachment; filename="' .basename("./logins.pdf") . '"');
    $response = $response->withHeader('Content-Transfer-Encoding', 'binary');
    $response = $response->withHeader('Expires', '0');
    $response = $response->withHeader('Cache-Control', 'must-revalidate');
    $response = $response->withHeader('Pragma', 'public');
    $response = $response->withHeader('Content-Length', filesize("./mdpf.pdf"));
    unlink("./mdpf.pdf");
    return $response->withBody($stream);
});

$app->get('/informs/pdf/orders_detail', function (Request $request, Response $response) {

    $mpdf = new \Mpdf\Mpdf();
    $mpdf->WriteHTML(
        PDFManagement::orders_detail()
    );
    $file = fopen("./mdpf.pdf", "w");    
    $mpdf->Output("./mdpf.pdf");
    fclose($file);
    $fh = fopen("./mdpf.pdf", "rb");
    $stream = new \Slim\Http\Stream($fh);
    $response = $response->withHeader('Content-Type', 'application/force-download');
    $response = $response->withHeader('Content-Description', 'File Transfer');
    $response = $response->withHeader('Content-Disposition', 'attachment; filename="' .basename("./comandas.pdf") . '"');
    $response = $response->withHeader('Content-Transfer-Encoding', 'binary');
    $response = $response->withHeader('Expires', '0');
    $response = $response->withHeader('Cache-Control', 'must-revalidate');
    $response = $response->withHeader('Pragma', 'public');
    $response = $response->withHeader('Content-Length', filesize("./mdpf.pdf"));
    unlink("./mdpf.pdf");
    return $response->withBody($stream);
});

/*$app->options('/login', function(Request $request, Response $response){
    return $newResponse = $response
    ->withAddedHeader("Access-Control-Allow-Headers", "*")
    ->withAddedHeader("Access-Control-Allow-Origin", "*");
});*/

$app->run();