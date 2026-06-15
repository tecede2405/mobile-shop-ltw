<?php

class ChatController
{
    private $service;

    public function __construct()
    {
        $this->service = new ChatService();
    }

    // API: Lấy lịch sử chat của 1 người
    public function history($receiverId)
    {
        $user = AuthMiddleware::user();
        $messages = $this->service->getHistory($user['id'], $receiverId);

        echo json_encode([
            'success' => true,
            'data' => $messages
        ]);
    }

    // API: Lấy danh bạ (những người đã từng chat)
    public function recent()
    {
        $user = AuthMiddleware::user();
        $chats = $this->service->getRecentChats($user['id']);

        echo json_encode([
            'success' => true,
            'data' => $chats
        ]);
    }

    // API: Tìm ID của Admin đang trực page
    public function getAdminInfo()
    {
        try {
            $adminId = $this->service->getAdmin();
            
            echo json_encode([
                'success' => true,
                'admin_id' => $adminId
            ]);
            
        } catch (Exception $e) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}