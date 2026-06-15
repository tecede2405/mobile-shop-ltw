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

    public function getRecentConversations($userId)
    {
        // Truy vấn này lấy danh sách chat, tự động tìm người đối diện (partner) và kèm tin nhắn mới nhất
        $stmt = $this->db->prepare("
            SELECT 
                c.id as conversation_id,
                u.id as partner_id,
                u.username as partner_name,
                m.content as last_message,
                m.created_at as last_message_time,
                m.is_read
            FROM conversations c
            JOIN users u ON u.id = CASE WHEN c.user1_id = ? THEN c.user2_id ELSE c.user1_id END
            LEFT JOIN messages m ON m.id = (
                SELECT id FROM messages 
                WHERE conversation_id = c.id 
                ORDER BY created_at DESC 
                LIMIT 1
            )
            WHERE c.user1_id = ? OR c.user2_id = ?
            ORDER BY last_message_time DESC
        ");
        
        // Cần truyền $userId vào 3 vị trí dấu ? trong câu SQL
        $stmt->execute([$userId, $userId, $userId]);
        
        return $stmt->fetchAll();
    }

    public function getDefaultAdminId()
    {
        // Truy vấn thông minh: Tìm Admin có tin nhắn gửi đi gần đây nhất (chứng tỏ đang trực page).
        // Nếu các admin đều chưa từng nhắn (last_active = NULL), sẽ chọn ngẫu nhiên (RAND) để chia đều tải.
        $stmt = $this->db->query("
            SELECT u.id 
            FROM users u
            LEFT JOIN (
                SELECT sender_id, MAX(created_at) as last_active 
                FROM messages 
                GROUP BY sender_id
            ) m ON u.id = m.sender_id
            WHERE u.role = 'admin'
            ORDER BY m.last_active DESC, RAND()
            LIMIT 1
        ");
        
        return $stmt->fetchColumn(); // Trả về ID của Admin
    }

}