<?php

require_once '../vendor/autoload.php';
header('Content-Type: application/json; charset=utf-8');
// Autoload
spl_autoload_register(function ($class) {

    $folders = [
        '../app/Core/',
        '../app/Config/',
        '../app/Controllers/',
        '../app/Services/',
        '../app/Repositories/',
        '../app/Models/',
        '../app/Helpers/',
        '../app/Middlewares/',
    ];

    foreach ($folders as $folder) {

        $file = $folder . $class . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Khởi tạo Router
$router = new Router();

// Load routes
require_once '../app/Routes/api.php';

// Dispatch
$router->dispatch();