<?php

class AdminOrderRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAllOrders()
    {
        $stmt = $this->db->query("
            SELECT *
            FROM orders
            ORDER BY id DESC
        ");

        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM orders
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        return $stmt->fetch();
    }

    public function getItemsByOrderId($orderId)
    {
        $stmt = $this->db->prepare("
            SELECT oi.*, p.name as product_name
            FROM order_items oi
            LEFT JOIN products p ON p.id = oi.product_id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function updateStatus(
        $id,
        $status
    ) {
        $stmt = $this->db->prepare("
            UPDATE orders
            SET status = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $status,
            $id
        ]);
    }
}