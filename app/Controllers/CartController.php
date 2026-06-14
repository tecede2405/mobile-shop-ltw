<?php

class CartController
{
    private $service;

    public function __construct()
    {
        $this->service = new CartService();
    }

    // GET /api/cart?user_id=1
    public function index()
    {
        $user = AuthMiddleware::user();

        $items = $this->service
            ->getCart($user['id']);

        echo json_encode([
            'success' => true,
            'items' => $items
        ]);
    }

    // POST /api/cart/add
    public function add()
    {
        $user = AuthMiddleware::user();

        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        $this->service->addToCart(
            $user['id'],
            $data['product_id'],
            $data['quantity'] ?? 1
        );

        echo json_encode([
            'success' => true
        ]);
    }

    // PATCH /api/cart/update
    public function update()
    {
        $user = AuthMiddleware::user();

        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        $this->service->updateCart(
            $user['id'],
            $data['product_id'],
            $data['quantity']
        );

        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật thành công'
        ]);
    }
    // DELETE /api/cart/remove/{userId}/{productId}
    public function remove($productId)
    {
        $user = AuthMiddleware::user();

        $this->service->removeFromCart(
            $user['id'],
            $productId
        );

        echo json_encode([
            'success' => true,
            'message' => 'Xóa thành công'
        ]);
    }
}