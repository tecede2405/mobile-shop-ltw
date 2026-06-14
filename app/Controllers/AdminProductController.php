<?php

class AdminProductController
{
    private $service;

    public function __construct()
    {
        $this->service = new AdminProductService();
    }

    public function index()
    {
        AdminMiddleware::user();

        echo json_encode([
            'success' => true,
            'products' => $this->service->getProducts()
        ]);
    }

    public function detail($id)
    {
        AdminMiddleware::user();

        echo json_encode([
            'success' => true,
            'product' => $this->service->getProduct($id)
        ]);
    }

    public function create()
    {
        AdminMiddleware::user();

        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        $id = $this->service
            ->createProduct($data);

        echo json_encode([
            'success' => true,
            'product_id' => $id
        ]);
    }

    public function update($id)
    {
        AdminMiddleware::user();

        $data = json_decode(
            file_get_contents("php://input"),
            true
        );

        $this->service
            ->updateProduct($id, $data);

        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật thành công'
        ]);
    }

    public function delete($id)
    {
        AdminMiddleware::user();

        $this->service
            ->deleteProduct($id);

        echo json_encode([
            'success' => true,
            'message' => 'Đã xóa sản phẩm'
        ]);
    }
}