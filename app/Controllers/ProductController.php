<?php

class ProductController
{
    private $service;

    public function __construct()
    {
        $this->service = new ProductService();
    }

    public function index()
{
    $products = $this->service
        ->getFilteredProducts($_GET);

    echo json_encode([
        'success' => true,
        'total' => count($products),
        'products' => $products
    ]);
}

    public function category($category)
    {
        $products = $this->service
            ->getProductsByCategory($category);

        if (!$products) {

            http_response_code(404);

            echo json_encode([
                'message' =>
                'Không có sản phẩm nào thuộc danh mục này'
            ]);

            return;
        }

        echo json_encode($products);
    }

    public function discount($percentage)
    {
        $products = $this->service
            ->getDiscountProducts(
                (int)$percentage
            );

        echo json_encode([
            'success' => true,
            'count' => count($products),
            'products' => $products
        ]);
    }
    public function detail($id)
    {
        $product = $this->service
            ->getProductById($id);

        if (!$product) {

            http_response_code(404);

            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm'
            ]);

            return;
        }

        echo json_encode([
            'success' => true,
            'product' => $product
        ]);
    }
}