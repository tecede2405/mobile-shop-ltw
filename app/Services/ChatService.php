<?php

use Ratchet\ConnectionInterface;

class ChatService
{
    private $chatRepo;

    public function __construct()
    {
        $this->chatRepo = new ChatRepository();
    }

    // Tiếp nhận và quản lý luồng tin nhắn
    public function handleMessage(ConnectionInterface $from, array $data, array $userConnections)
    {
        // 1. Kiểm tra conversation_id, nếu là tin đầu tiên thì tạo mới
        $convId = $data['conversation_id'] ?? null;
        if (!$convId) {
            $convId = $this->chatRepo->createConversation($from->user_id, $data['receiver_id']);
        }

        // 2. Lưu vào DB (Bước 2.1)
        $savedMsg = $this->chatRepo->save(
            $convId,
            $from->user_id,
            $data['receiver_id'],
            $data['content']
        );

        // 3. Đóng gói phản hồi (Bước 2.2)
        $response = $this->buildResponse(
            $convId,
            $savedMsg['message_id'],
            $from->user_id,
            $data['receiver_id'],
            $data['content'],
            $savedMsg['created_at']
        );

        // 4. Định tuyến & Gửi tin (Bước 2.3)
        $this->routeMessage($userConnections, $from, $response);
    }

    // Đóng gói Payload đầy đủ để FE tiện xử lý
    private function buildResponse($convId, $msgId, $senderId, $receiverId, $content, $createdAt)
    {
        return [
            'event'           => 'chat_message',
            'conversation_id' => $convId,
            'message_id'      => $msgId,
            'sender_id'       => $senderId,
            'receiver_id'     => $receiverId,
            'content'         => $content,
            'created_at'      => $createdAt
        ];
    }

    // Định tuyến (Stateless - Không tự lưu mảng connection)
    public function routeMessage(array $userConnections, ConnectionInterface $from, array $response)
    {
        $receiverId = $response['receiver_id'];
        $payload = json_encode($response);

        // Gửi lại chính JSON đó cho người gửi để FE cập nhật trạng thái "Đã gửi"
        $from->send($payload);

        // Gửi cho người nhận nếu họ đang online (Duyệt qua tất cả các Tab của họ)
        if (isset($userConnections[$receiverId])) {
            foreach ($userConnections[$receiverId] as $receiverConn) {
                $receiverConn->send($payload);
            }
        }
    }

    public function getHistory($userId1, $userId2)
    {
        // 1. Tìm xem 2 user này đã từng chat chưa
        $convId = $this->chatRepo->getConversationId($userId1, $userId2);
        
        // Nếu chưa từng chat, trả về mảng rỗng
        if (!$convId) {
            return [];
        }

        // 2. Nếu đã có, lấy toàn bộ tin nhắn
        return $this->chatRepo->getMessages($convId);
    }
}