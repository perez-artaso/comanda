<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\UploadedFile;

date_default_timezone_set("America/Argentina/Buenos_Aires");

require_once "vendor/autoload.php";
require_once "EmployeeManagement.php";
require_once "ProductManagement.php";
require_once "TableManagement.php";
require_once "PollManagement.php";
require_once "ImageManagement.php";
require_once "OrderManagement.php";

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

$app->post("/create_employee", function(Request $request, Response $response) {
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

$app->post('/change_password', function(Request $request, Response $response) {
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

$app->post("/delete_employee", function(Request $request, Response $response) {
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

$app->post("/update_status", function (Request $request, Response $response) {
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

$app->post('/login', function (Request $request, Response $response) {
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

$app->post ("/create_product", function (Request $request, Response $response) {
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

$app->get("/get_menu", function(Request $request, Response $response) {
    return $response->getBody()->write(
        ProductManagement::get_menu()
    );
});

$app->post("/create_table", function (Request $request, Response $response) {
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
});

$app->post("/delete_table", function (Request $request, Response $response) {
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
});

$app->post("/update_table", function (Request $request, Response $response) {
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
});

$app->post("/change_table_status", function (Request $request, Response $response) {
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
});

$app->post("/create_poll", function (Request $request, Response $response) {
    $params = $request->getParsedBody();

    if (
        isset($params["order_id"]) &&
        isset($params["subject"]) && 
        isset($params["score"]) && 
        isset($params["comments"])
    ) {
        return $response->getBody()->write(
            PollManagement::create_poll($params["order_id"], $params["subject"], $params["score"], $params["comments"])
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

$app->get("/get_polls", function(Request $request, Response $response) {
    return $response->getBody()->write(
        PollManagement::read_polls()
    );
});

$app->post("/delete_poll", function (Request $request, Response $response) {
    $params = $request->getParsedBody();

    if (
        isset($params["id"])
    ) {
        return $response->getBody()->write(
            PollManagement::delete_poll($params["id"])
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

$app->post("/create_order", function (Request $request, Response $response) {

    $params = $request->getParsedBody();
    $uploadedFiles = $request->getUploadedFiles();

    if (
        isset($params["table_id"]) &&
        isset($params["items"]) &&
        isset($params["client_name"]) &&
        isset($params["items"])
    ) {
        return $response->getBody()->write(
            OrderManagement::create_order(
                $params["client_name"], 
                ImageManagement::process_incoming_image($uploadedFiles['photograph']), 
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

$app->get("/get_pending_items", function (Request $request, Response $response) {

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

$app->post("/take_item", function (Request $request, Response $response) {
    
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

$app->post("/mark_as_ready", function (Request $request, Response $response) {

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

$app->post("/test", function (Request $request, Response $response) {
    return $response->getBody()->write(var_dump($request->getParsedBody()));
});

$app->get("/get_tables", function (Request $request, Response $response) {

    return $response->getBody()->write(
        TableManagement::read_tables()
    );

});

/*$app->options('/login', function(Request $request, Response $response){
    return $newResponse = $response
    ->withAddedHeader("Access-Control-Allow-Headers", "*")
    ->withAddedHeader("Access-Control-Allow-Origin", "*");
});*/

$app->run();