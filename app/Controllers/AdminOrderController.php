<?php

class AdminOrderController
{
    private $service;

    public function __construct()
    {
        $this->service =
            new AdminOrderService();
    }

    public function index()
    {
        AdminMiddleware::user();

        echo json_encode([
            'success' => true,
            'orders' => $this->service
                ->getOrders()
        ]);
    }

    public function detail($id)
    {
        AdminMiddleware::user();

        echo json_encode([
            'success' => true,
            'order' => $this->service
                ->getOrder($id)
        ]);
    }

    public function updateStatus($id)
    {
        AdminMiddleware::user();

        $data = json_decode(
            file_get_contents(
                "php://input"
            ),
            true
        );

        $this->service
            ->updateStatus(
                $id,
                $data['status']
            );

        echo json_encode([
            'success' => true,
            'message' =>
                'Cập nhật thành công'
        ]);
    }
}