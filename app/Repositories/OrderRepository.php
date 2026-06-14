<?php

class OrderRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function createOrder($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO orders
            (
                user_id,
                method,
                customer_name,
                customer_phone,
                customer_address,
                pickup_date,
                pickup_time,
                total
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['user_id'],
            $data['method'],
            $data['customer_name'],
            $data['customer_phone'],
            $data['customer_address'],
            $data['pickup_date'],
            $data['pickup_time'],
            $data['total']
        ]);

        return $this->db->lastInsertId();
    }

    public function createOrderItem(
        $orderId,
        $productId,
        $quantity,
        $price
    ) {
        $stmt = $this->db->prepare("
            INSERT INTO order_items
            (
                order_id,
                product_id,
                quantity,
                price
            )
            VALUES (?, ?, ?, ?)
        ");

        return $stmt->execute([
            $orderId,
            $productId,
            $quantity,
            $price
        ]);
    }

    public function getOrdersByUser($userId)
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM orders
            WHERE user_id = ?
            ORDER BY id DESC
        ");

        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    public function findOrderById($id)
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
    public function cancelOrder($id)
    {
        $stmt = $this->db->prepare("
            UPDATE orders
            SET status = 'cancelled'
            WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }
}