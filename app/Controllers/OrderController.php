<?php

class OrderController
{
    private $service;

    public function __construct()
    {
        $this->service = new OrderService();
    }

    public function create()
    {
        $user = AuthMiddleware::user();

        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        $orderId =
            $this->service
                ->createOrder(
                    $user['id'],
                    $data
                );

        echo json_encode([
            'success' => true,
            'order_id' => $orderId
        ]);
    }

    public function index()
    {
        $user = AuthMiddleware::user();

        echo json_encode([
            'success' => true,
            'orders' => $this->service
                ->getOrders($user['id'])
        ]);
    }

    public function detail($id)
    {
        $user = AuthMiddleware::user();

        echo json_encode([
            'success' => true,
            'order' => $this->service
                ->getOrder(
                    $user['id'],
                    $id
                )
        ]);
    }
    public function cancel($id)
    {
        $user = AuthMiddleware::user();

        $this->service->cancelOrder(
            $user['id'],
            $id
        );

        echo json_encode([
            'success' => true,
            'message' => 'Đã hủy đơn hàng'
        ]);
    }
}