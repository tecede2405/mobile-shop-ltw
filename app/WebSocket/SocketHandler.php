<?php

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class SocketHandler implements MessageComponentInterface
{
    protected $clients;
    protected $userConnections = [];
    protected $adminConnections = [];
    protected $redisState;
    
    // Khai báo biến chứa Service xử lý Chat
    protected $chatService;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        
        // Khởi tạo ChatService (Dependency Injection)
        $this->chatService = new ChatService();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $querystring = $conn->httpRequest->getUri()->getQuery();
        parse_str($querystring, $query);

        if (!isset($query['token'])) {
            echo "Từ chối kết nối: Không có token.\n";
            $conn->close();
            return;
        }

        try {
        $decoded = (array) JwtHelper::verify($query['token']);

        $conn->user_id = $decoded['id'];
        $conn->role = $decoded['role'];

        $this->clients->attach($conn);
        $this->userConnections[$conn->user_id][$conn->resourceId] = $conn;

        if ($conn->role === 'admin') {
            $this->adminConnections[$conn->resourceId] = $conn;
            
            // --- GHI NHẬN ADMIN ONLINE VÀO REDIS ---
            if ($this->redisState) {
                // Lệnh sadd thêm id vào tập hợp (Set), không sợ bị trùng lặp
                $this->redisState->sadd('online_admins', $conn->user_id);
                echo "=> Đã thêm Admin ID {$conn->user_id} vào danh sách ONLINE.\n";
            }
        }

        echo "Kết nối thành công! User ID: {$conn->user_id} | Role: {$conn->role} | Conn ID: {$conn->resourceId}\n";

    } catch (Exception $e) {
            echo "Từ chối kết nối: Token không hợp lệ ({$e->getMessage()}).\n";
            $conn->close();
        }
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        // 1. Decode dữ liệu JSON gửi từ Frontend
        $data = json_decode($msg, true);
        
        // Kiểm tra Event hợp lệ
        if (!$data || !isset($data['event'])) {
            echo "Nhận data không hợp lệ từ User {$from->user_id}: {$msg}\n";
            return;
        }

        // 2. Chuyển tiếp (Delegate) luồng xử lý cho ChatService nếu event là chat_message
        if ($data['event'] === 'chat_message') {
            echo "User {$from->user_id} gửi tin nhắn đến User {$data['receiver_id']}\n";
            
            // Gọi hàm handleMessage của ChatService, truyền vào người gửi, data và danh sách các kết nối hiện tại
            $this->chatService->handleMessage($from, $data, $this->userConnections);
        }
    }

    public function onClose(ConnectionInterface $conn)
{
    $this->clients->detach($conn);

    if (isset($conn->user_id)) {
        unset($this->userConnections[$conn->user_id][$conn->resourceId]);

        // Nếu User/Admin đóng TOÀN BỘ Tab
        if (empty($this->userConnections[$conn->user_id])) {
            unset($this->userConnections[$conn->user_id]);
            
            // --- XÓA ADMIN KHỎI DANH SÁCH ONLINE TRÊN REDIS ---
            if (isset($conn->role) && $conn->role === 'admin' && $this->redisState) {
                $this->redisState->srem('online_admins', $conn->user_id);
                echo "=> Admin ID {$conn->user_id} đã OFFLINE.\n";
            }
        }

        if (isset($this->adminConnections[$conn->resourceId])) {
            unset($this->adminConnections[$conn->resourceId]);
        }

        echo "Ngắt kết nối: User ID {$conn->user_id} (Conn ID: {$conn->resourceId})\n";
    }
}

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Lỗi trên Conn ID {$conn->resourceId}: {$e->getMessage()}\n";
        $conn->close();
    }
    public function sendToAdmins($event, $data)
    {
        $payload = json_encode([
            'event' => $event,
            'data'  => $data
        ]);

        foreach ($this->adminConnections as $conn) {
            $conn->send($payload);
        }
        
        echo "Đã gửi thông báo '$event' tới " . count($this->adminConnections) . " admin(s) đang online.\n";
    }

    public function setRedisState($redis) {
    $this->redisState = $redis;
    // Xóa rác danh sách cũ (nếu có) khi server vừa khởi động lại
    $this->redisState->del('online_admins');
}
}