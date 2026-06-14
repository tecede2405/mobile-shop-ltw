<?php

class CartService
{
    private $repo;

    public function __construct()
    {
        $this->repo = new CartRepository();
    }

    private function getOrCreateCart($userId)
    {
        $cart = $this->repo
            ->getCartByUserId($userId);

        if (!$cart) {

            $cartId = $this->repo
                ->createCart($userId);

            return [
                'id' => $cartId
            ];
        }

        return $cart;
    }

    public function getCart($userId)
    {
        $cart = $this->getOrCreateCart(
            $userId
        );

        return $this->repo
            ->getCartItems($cart['id']);
    }

    public function addToCart(
        $userId,
        $productId,
        $quantity
    ) {
        $cart = $this->getOrCreateCart(
            $userId
        );

        $item = $this->repo
            ->findCartItem(
                $cart['id'],
                $productId
            );

        if ($item) {

            $this->repo->updateQuantity(
                $cart['id'],
                $productId,
                $item['quantity'] + $quantity
            );

            return;
        }

        $this->repo->addItem(
            $cart['id'],
            $productId,
            $quantity
        );
    }

    public function updateCart(
        $userId,
        $productId,
        $quantity
    ) {
        $cart = $this->getOrCreateCart(
            $userId
        );

        return $this->repo
            ->updateQuantity(
                $cart['id'],
                $productId,
                $quantity
            );
    }

    public function removeFromCart(
        $userId,
        $productId
    ) {
        $cart = $this->getOrCreateCart(
            $userId
        );

        return $this->repo
            ->removeItem(
                $cart['id'],
                $productId
            );
    }
}