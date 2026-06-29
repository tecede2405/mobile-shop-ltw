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
    // 1. Ưu tiên 1: Lấy Admin ĐANG ONLINE từ Redis
    try {
        $redis = new \Predis\Client([
            'scheme' => 'tcp',
            'host'   => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
            'port'   => $_ENV['REDIS_PORT'] ?? 6379,
        ]);
        
        // Lấy danh sách ID của các admin đang trực page
        $onlineAdmins = $redis->smembers('online_admins');
        
        if (!empty($onlineAdmins)) {
            // Random 1 admin để cân bằng tải (nếu có nhiều admin cùng onl)
            $selectedAdmin = $onlineAdmins[array_rand($onlineAdmins)];
            return $selectedAdmin;
        }
    } catch (\Exception $e) {
        // Log lại lỗi nếu Redis chưa bật, không làm đứng web
        error_log("Không lấy được Admin Online từ Redis: " . $e->getMessage());
    }

    // 2. Ưu tiên 2 (Backup): Nếu ĐÊM KHUYA không có Admin nào online, 
    // lấy Admin có tin nhắn gần nhất trong Database (đoạn SQL cũ của bạn).
    $sql = "SELECT id FROM users WHERE role = 'admin' LIMIT 1"; 
    // (Lưu ý: Thay dòng $sql này bằng câu truy vấn SELECT JOIN thông minh mà bạn đã viết trước đó nhé).

    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchColumn();
}

}