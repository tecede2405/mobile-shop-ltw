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
use Clue\React\Redis\Factory as RedisFactory;

// Khởi tạo Event Loop của ReactPHP
$loop = Loop::get();

$socket = new \React\Socket\SocketServer('0.0.0.0:8080', [], $loop);

// Tách SocketHandler ra một biến để tái sử dụng
$socketHandler = new SocketHandler();

// Khởi tạo Server Socket
$server = new IoServer(
    new HttpServer(
        new WsServer(
            $socketHandler
        )
    ),
    $socket,
    $loop
);

$redisFactory = new RedisFactory($loop);
$redisUrl = $_ENV['REDIS_URL'] ?? '127.0.0.1:6379';

// --- 1. KẾT NỐI REDIS ĐỂ LẮNG NGHE ĐƠN HÀNG (SUBSCRIBE) ---

$redisFactory->createClient($redisUrl)->then(function ($redis) use ($socketHandler) {
    echo "Đã kết nối Redis Async thành công!\n";
    
    $redis->on('message', function ($channel, $payload) use ($socketHandler) {
        $data = json_decode($payload, true);
        if ($data && isset($data['event']) && $data['event'] === 'new_order') {
            // Đẩy sang SocketHandler để gửi cho Admin
            $socketHandler->sendToAdmins('new_order', $data['data']);
        }
    });

    $redis->subscribe('ecommerce_notifications');
}, function (Exception $e) {
    echo "Lỗi kết nối Redis Subscriber: " . $e->getMessage() . "\n";
});
// --- 2. KẾT NỐI REDIS ĐỂ QUẢN LÝ TRẠNG THÁI (STATE) ---
$redisFactory->createClient($redisUrl)->then(function ($redisState) use ($socketHandler) {
    echo "Đã kết nối Redis Async (State Manager)!\n";
    // Truyền vào SocketHandler để nó cập nhật trạng thái Online
    $socketHandler->setRedisState($redisState);
}, function (Exception $e) {
    echo "Lỗi Redis State: " . $e->getMessage() . "\n";
});

echo "WebSocket Server đang chạy tại cổng 8080...\n";

// Bắt đầu vòng lặp để giữ server luôn chạy
$server->run();