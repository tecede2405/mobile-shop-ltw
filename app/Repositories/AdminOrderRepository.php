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