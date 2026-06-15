<?php

class ChatController
{
    private $service;

    public function __construct()
    {
        $this->service = new ChatService();
    }

    public function history($receiverId)
    {
        // Yêu cầu phải đăng nhập mới được xem chat
        $user = AuthMiddleware::user();

        // Gọi service lấy lịch sử giữa người đang login ($user['id']) và người cần xem ($receiverId)
        $messages = $this->service->getHistory($user['id'], $receiverId);

        echo json_encode([
            'success' => true,
            'data' => $messages
        ]);
    }
}