<?php

class CartRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getCartByUserId($userId)
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM carts
            WHERE user_id = ?
            LIMIT 1
        ");

        $stmt->execute([$userId]);

        return $stmt->fetch();
    }

    public function createCart($userId)
    {
        $stmt = $this->db->prepare("
            INSERT INTO carts(user_id)
            VALUES(?)
        ");

        $stmt->execute([$userId]);

        return $this->db->lastInsertId();
    }

    public function getCartItems($cartId)
    {
        $stmt = $this->db->prepare("
            SELECT
                ci.id,
                ci.product_id,
                ci.quantity,
                p.name,
                p.price,
                p.thumbnail
            FROM cart_items ci
            JOIN products p
                ON p.id = ci.product_id
            WHERE ci.cart_id = ?
        ");

        $stmt->execute([$cartId]);

        return $stmt->fetchAll();
    }

    public function findCartItem($cartId, $productId)
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM cart_items
            WHERE cart_id = ?
            AND product_id = ?
            LIMIT 1
        ");

        $stmt->execute([
            $cartId,
            $productId
        ]);

        return $stmt->fetch();
    }

    public function addItem($cartId, $productId, $quantity)
    {
        $stmt = $this->db->prepare("
            INSERT INTO cart_items
            (
                cart_id,
                product_id,
                quantity
            )
            VALUES (?, ?, ?)
        ");

        return $stmt->execute([
            $cartId,
            $productId,
            $quantity
        ]);
    }

    public function updateQuantity(
        $cartId,
        $productId,
        $quantity
    ) {
        $stmt = $this->db->prepare("
            UPDATE cart_items
            SET quantity = ?
            WHERE cart_id = ?
            AND product_id = ?
        ");

        return $stmt->execute([
            $quantity,
            $cartId,
            $productId
        ]);
    }

    public function removeItem(
        $cartId,
        $productId
    ) {
        $stmt = $this->db->prepare("
            DELETE FROM cart_items
            WHERE cart_id = ?
            AND product_id = ?
        ");

        return $stmt->execute([
            $cartId,
            $productId
        ]);
    }
    public function clearCart($cartId)
{
    $stmt = $this->db->prepare("
        DELETE FROM cart_items
        WHERE cart_id = ?
    ");

    return $stmt->execute([$cartId]);
}
}