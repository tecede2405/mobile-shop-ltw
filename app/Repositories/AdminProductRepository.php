<?php

class AdminProductRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll()
    {
        $stmt = $this->db->query("
            SELECT *
            FROM products
            WHERE deleted = 0
            ORDER BY id DESC
        ");

        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM products
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);

        return $stmt->fetch();
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO products
            (
                name,
                brand,
                category,
                price,
                discount,
                thumbnail,
                status
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['name'],
            $data['brand'],
            $data['category'],
            $data['price'],
            $data['discount'] ?? 0,
            $data['thumbnail'],
            'active'
        ]);

        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare("
            UPDATE products
            SET
                name = ?,
                brand = ?,
                category = ?,
                price = ?,
                discount = ?,
                thumbnail = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['name'],
            $data['brand'],
            $data['category'],
            $data['price'],
            $data['discount'] ?? 0,
            $data['thumbnail'],
            $id
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("
            UPDATE products
            SET deleted = 1
            WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }
}