<?php

// Đường dẫn này lùi lại 2 cấp từ app/WebSocket/ ra thư mục gốc
require dirname(__DIR__, 2) . '/vendor/autoload.php';

// Thêm load .env và cấu hình Autoloader (để file chạy nền có thể gọi DB và JwtHelper)
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
$dotenv->load();

spl_autoload_register(function ($class) {
    $folders = [
        __DIR__ . '/../Core/',
        __DIR__ . '/../Config/',
        __DIR__ . '/../Services/',
        __DIR__ . '/../Repositories/',
        __DIR__ . '/../Models/',
        __DIR__ . '/../Helpers/',
    ];
    foreach ($folders as $folder) {
        $file = $folder . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Nạp file SocketHandler
require_once __DIR__ . '/SocketHandler.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;

// Khởi tạo Event Loop của ReactPHP
$loop = Loop::get();

// SỬA LỖI: Sử dụng từ khóa new thay vì ::create
$socket = new \React\Socket\SocketServer('0.0.0.0:8080', [], $loop);

// Khởi tạo Server Socket
$server = new IoServer(
    new HttpServer(
        new WsServer(
            new SocketHandler()
        )
    ),
    $socket,
    $loop
);

echo "WebSocket Server đang chạy tại cổng 8080...\n";

// Bắt đầu vòng lặp để giữ server luôn chạy
$server->run();