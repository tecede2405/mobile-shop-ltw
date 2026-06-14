<?php

class AdminProductService
{
    private $repo;

    public function __construct()
    {
        $this->repo = new AdminProductRepository();
    }

    public function getProducts()
    {
        return $this->repo->getAll();
    }

    public function getProduct($id)
    {
        $product = $this->repo->findById($id);

        if (!$product) {
            throw new Exception(
                "Không tìm thấy sản phẩm"
            );
        }

        return $product;
    }

    public function createProduct($data)
    {
        return $this->repo->create($data);
    }

    public function updateProduct($id, $data)
    {
        return $this->repo->update($id, $data);
    }

    public function deleteProduct($id)
    {
        return $this->repo->delete($id);
    }
}