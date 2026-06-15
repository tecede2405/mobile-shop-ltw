<?php

class OrderService
{
    private $orderRepo;
    private $cartRepo;
    private $redis;

    public function __construct()
    {
        $this->orderRepo = new OrderRepository();
        $this->cartRepo = new CartRepository();

        // Khởi tạo kết nối Redis
        try {
            $this->redis = new \Predis\Client([
                'scheme' => 'tcp',
                'host'   => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
                'port'   => $_ENV['REDIS_PORT'] ?? 6379,
            ]);
        } catch (\Exception $e) {
            $this->redis = null;
        }
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

        if ($this->redis) {
            try {
                $payload = json_encode([
                    'event' => 'new_order',
                    'data'  => [
                        'order_id' => $orderId,
                        'total'    => $total,
                        'customer' => $data['name']
                    ]
                ]);
                
                // Publish vào channel 'ecommerce_notifications'
                $this->redis->publish('ecommerce_notifications', $payload);
            } catch (\Exception $e) {
                // Bỏ qua lỗi Redis để không làm gián đoạn việc tạo đơn hàng của User
                error_log("Redis Publish Error: " . $e->getMessage());
            }
        }

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