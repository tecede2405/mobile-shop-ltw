<?php

class OrderService
{
    private $orderRepo;
    private $cartRepo;

    public function __construct()
    {
        $this->orderRepo = new OrderRepository();
        $this->cartRepo = new CartRepository();
    }

    public function createOrder(
        $userId,
        $data
    ) {

        $cart = $this->cartRepo
            ->getCartByUserId($userId);

        if (!$cart) {
            throw new Exception(
                "Giỏ hàng trống"
            );
        }

        $items = $this->cartRepo
            ->getCartItems($cart['id']);

        if (empty($items)) {
            throw new Exception(
                "Giỏ hàng trống"
            );
        }

        $total = 0;

        foreach ($items as $item) {

            $total +=
                $item['price']
                * $item['quantity'];
        }

        $orderId =
            $this->orderRepo
                ->createOrder([
                    'user_id' => $userId,
                    'method' => $data['method'],
                    'customer_name' => $data['name'],
                    'customer_phone' => $data['phone'],
                    'customer_address' => $data['address'] ?? '',
                    'pickup_date' => $data['date'] ?? null,
                    'pickup_time' => $data['time'] ?? null,
                    'total' => $total
                ]);

        foreach ($items as $item) {

            $this->orderRepo
                ->createOrderItem(
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price']
                );
        }

        $this->cartRepo
            ->clearCart($cart['id']);

        return $orderId;
    }

    public function getOrders($userId)
        {
            return $this->orderRepo
                ->getOrdersByUser($userId);
        }

        public function getOrder(
        $userId,
        $orderId
    )
    {
        $order = $this->orderRepo
            ->findOrderById($orderId);

        if (!$order) {
            throw new Exception(
                "Không tìm thấy đơn hàng"
            );
        }

        if ($order['user_id'] != $userId) {
            throw new Exception(
                "Không có quyền truy cập"
            );
        }

        return $order;
    }
    public function cancelOrder(
        $userId,
        $orderId
    ) {
        $order = $this->orderRepo
            ->findOrderById($orderId);

        if (!$order) {
            throw new Exception(
                "Không tìm thấy đơn hàng"
            );
        }

        if ($order['user_id'] != $userId) {
            throw new Exception(
                "Không có quyền"
            );
        }

        if ($order['status'] !== 'pending') {
            throw new Exception(
                "Không thể hủy đơn hàng này"
            );
        }

        return $this->orderRepo
            ->cancelOrder($orderId);
    }
}