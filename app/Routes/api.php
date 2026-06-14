<?php

// Auth
$router->post(
    '/api/auth/register',
    [AuthController::class, 'register']
);

$router->post(
    '/api/auth/login',
    [AuthController::class, 'login']
);

// Products
$router->get(
    '/api/products',
    [ProductController::class, 'index']
);

$router->get(
    '/api/products/{id}',
    [ProductController::class, 'detail']
);

$router->get(
    '/api/products/search',
    [ProductController::class, 'search']
);

$router->get(
    '/api/products/category/{category}',
    [ProductController::class, 'category']
);

$router->get(
    '/api/products/discount/{percentage}',
    [ProductController::class, 'discount']
);

// Cart
$router->get(
    '/api/cart',
    [CartController::class, 'index']
);

$router->post(
    '/api/cart/add',
    [CartController::class, 'add']
);

$router->post(
    '/api/cart/update',
    [CartController::class, 'update']
);

$router->post(
    '/api/cart/remove/{productId}',
    [CartController::class, 'remove']
);

// order
$router->post(
    '/api/orders',
    [OrderController::class, 'create']
);

$router->get(
    '/api/orders',
    [OrderController::class, 'index']
);

$router->get(
    '/api/orders/{id}',
    [OrderController::class, 'detail']
);

$router->post(
    '/api/orders/cancel/{id}',
    [OrderController::class, 'cancel']
);

// Admin Orders
$router->get(
    '/api/admin/orders',
    [AdminOrderController::class, 'index']
);

$router->get(
    '/api/admin/orders/{id}',
    [AdminOrderController::class, 'detail']
);

$router->post(
    '/api/admin/orders/status/{id}',
    [AdminOrderController::class, 'updateStatus']
);

// Admin Products
$router->get(
    '/api/admin/products',
    [AdminProductController::class, 'index']
);

$router->get(
    '/api/admin/products/{id}',
    [AdminProductController::class, 'detail']
);

$router->post(
    '/api/admin/products/create',
    [AdminProductController::class, 'create']
);

$router->post(
    '/api/admin/products/update/{id}',
    [AdminProductController::class, 'update']
);

$router->post(
    '/api/admin/products/delete/{id}',
    [AdminProductController::class, 'delete']
);