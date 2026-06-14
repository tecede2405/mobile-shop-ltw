<?php

class ProductRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll()
    {
        $sql = "
            SELECT *
            FROM products
            WHERE deleted = 0
            AND status = 'active'
        ";

        return $this->db->query($sql)->fetchAll();
    }

    public function filter($filters)
{
    $sql = "
        SELECT *
        FROM products
        WHERE deleted = 0
        AND status = 'active'
    ";

    $params = [];

    if (!empty($filters['name'])) {
        $sql .= " AND name LIKE ?";
        $params[] = "%" . $filters['name'] . "%";
    }

    if (!empty($filters['category'])) {
        $sql .= " AND category = ?";
        $params[] = $filters['category'];
    }

    if (!empty($filters['brand'])) {
        $sql .= " AND brand LIKE ?";
        $params[] = "%" . $filters['brand'] . "%";
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

    public function searchByName($name)
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM products
            WHERE name LIKE ?
            AND deleted = 0
            AND status = 'active'
        ");

        $stmt->execute(["%{$name}%"]);

        return $stmt->fetchAll();
    }

    public function findByCategory($category)
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM products
            WHERE category = ?
            AND status = 'active'
            LIMIT 20
        ");

        $stmt->execute([$category]);

        return $stmt->fetchAll();
    }

    public function findDiscountGreaterThan($percent)
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM products
            WHERE discount > ?
            AND status = 'active'
            LIMIT 10
        ");

        $stmt->execute([$percent]);

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
}
