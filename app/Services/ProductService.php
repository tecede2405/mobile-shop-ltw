<?php

class ProductService
{
    private $repo;

    public function __construct()
    {
        $this->repo = new ProductRepository();
    }

    public function getAllProducts()
    {
        return $this->repo->getAll();
    }

    public function getFilteredProducts($filters)
    {
        return $this->repo->filter($filters);
    }

    public function searchProducts($name)
    {
        return $this->repo->searchByName($name);
    }

    public function getProductsByCategory($category)
    {
        return $this->repo->findByCategory($category);
    }

    public function getDiscountProducts($percent)
    {
        return $this->repo->findDiscountGreaterThan($percent);
    }
    public function getProductById($id)
    {
        return $this->repo->findById($id);
    }
}