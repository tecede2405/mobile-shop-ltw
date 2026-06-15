<?php

class ChatRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Tự sinh/tạo mới cuộc hội thoại nếu chưa có
    public function createConversation($userId1, $userId2)
    {
        $stmt = $this->db->prepare("
            INSERT INTO conversations (user1_id, user2_id) 
            VALUES (?, ?)
        ");
        
        $stmt->execute([$userId1, $userId2]);
        
        return $this->db->lastInsertId();
    }

    // Hàm save: Yêu cầu bắt buộc trả về message_id và created_at
    public function save($conversationId, $senderId, $receiverId, $content)
    {
        $stmt = $this->db->prepare("
            INSERT INTO messages (conversation_id, sender_id, receiver_id, content, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([$conversationId, $senderId, $receiverId, $content]);
        
        $msgId = $this->db->lastInsertId();
        
        // Lấy lại thời gian tạo chính xác từ Database
        $stmt2 = $this->db->prepare("SELECT created_at FROM messages WHERE id = ?");
        $stmt2->execute([$msgId]);
        
        return [
            'message_id' => $msgId,
            'created_at' => $stmt2->fetchColumn()
        ];
    }

    public function getConversationId($userId1, $userId2)
    {
        $stmt = $this->db->prepare("
            SELECT id FROM conversations 
            WHERE (user1_id = ? AND user2_id = ?) 
               OR (user1_id = ? AND user2_id = ?)
            LIMIT 1
        ");
        
        // Kiểm tra cả 2 chiều (A nhắn cho B, hoặc B nhắn cho A)
        $stmt->execute([$userId1, $userId2, $userId2, $userId1]);
        
        return $stmt->fetchColumn(); // Trả về ID hoặc false
    }

    public function getMessages($conversationId)
    {
        $stmt = $this->db->prepare("
            SELECT id, sender_id, receiver_id, content, is_read, created_at 
            FROM messages 
            WHERE conversation_id = ? 
            ORDER BY created_at ASC
        ");
        
        $stmt->execute([$conversationId]);
        
        return $stmt->fetchAll();
    }
}