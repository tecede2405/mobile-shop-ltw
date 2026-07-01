<?php

class AdminOrderService
{
    private $repo;

    public function __construct()
    {
        $this->repo =
            new AdminOrderRepository();
    }

    public function getOrders()
    {
        return $this->repo
            ->getAllOrders();
    }

    public function getOrder($id)
    {
        $order = $this->repo
            ->findById($id);

        if (!$order) {
            throw new Exception(
                "Không tìm thấy đơn hàng"
            );
        }

        $order['items'] = $this->repo->getItemsByOrderId($id);

        return $order;
    }

    public function updateStatus(
        $id,
        $status
    ) {
        $allow = [
            'pending',
            'confirmed',
            'shipping',
            'completed',
            'cancelled'
        ];

        if (
            !in_array(
                $status,
                $allow
            )
        ) {
            throw new Exception(
                "Trạng thái không hợp lệ"
            );
        }

        return $this->repo
            ->updateStatus(
                $id,
                $status
            );
    }
}