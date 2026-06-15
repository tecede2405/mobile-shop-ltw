<?php

require_once '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(
    dirname(__DIR__)
);

$dotenv->load();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Xử lý request kiểm tra trước (Preflight Request) của trình duyệt
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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