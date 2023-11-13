<?php

function call($controller, $action)
{
    
    require_once('src/controllers/' . ucfirst($controller) . 'Controller.php');

    if (class_exists(ucfirst($controller) . 'Controller')) {
        $controller_class = ucfirst($controller) . 'Controller';
        $controller = new $controller_class();
        if ($controller != null) {
            $controller->{$action}();
        }
    }
}



$controllers = [
    'webserver' => ['setup'], 
    'database' => ['setup'], 
    'environment' => ['setup'], 
    'dictionary' => ['setup', 'finish', 'getDictsList','updateOneDictionary'],
    'migrations' => ['setup']
];

[$controller, $action] = explode('/', $_GET['r']);



if (array_key_exists($controller, $controllers)) {
    if (in_array($action, $controllers[$controller])) {
        call($controller, $action);
    } else {
        http_response_code(404);
    }
} else {
    http_response_code(404);
}
