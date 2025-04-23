<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private $requestStack;
    private $productRepository;

    public function __construct(
        RequestStack $requestStack,
        ProductRepository $productRepository
    ) {
        $this->requestStack = $requestStack;
        $this->productRepository = $productRepository;
    }

    public function getCart(): array
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', [
            'items' => [],
            'total' => 0,
        ]);

        return $cart;
    }

    public function addToCart(int $productId): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', [
            'items' => [],
            'total' => 0,
        ]);

        // Check if product already in cart
        $found = false;
        foreach ($cart['items'] as &$item) {
            if ($item['product']->getId() === $productId) {
                $item['quantity']++;
                $found = true;
                break;
            }
        }

        // If product not in cart, add it
        if (!$found) {
            $product = $this->productRepository->find($productId);
            if (!$product) {
                return;
            }

            $cart['items'][] = [
                'product' => $product,
                'quantity' => 1,
            ];
        }

        // Recalculate total
        $this->recalculateTotal($cart);

        $session->set('cart', $cart);
    }

    public function removeFromCart(int $productId): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', [
            'items' => [],
            'total' => 0,
        ]);

        foreach ($cart['items'] as $key => $item) {
            if ($item['product']->getId() === $productId) {
                unset($cart['items'][$key]);
                break;
            }
        }

        // Reindex array
        $cart['items'] = array_values($cart['items']);

        // Recalculate total
        $this->recalculateTotal($cart);

        $session->set('cart', $cart);
    }

    public function decreaseQuantity(int $productId): void
    {
        $session = $this->requestStack->getSession();
        $cart = $session->get('cart', [
            'items' => [],
            'total' => 0,
        ]);

        foreach ($cart['items'] as $key => &$item) {
            if ($item['product']->getId() === $productId) {
                if ($item['quantity'] > 1) {
                    $item['quantity']--;
                } else {
                    unset($cart['items'][$key]);
                    $cart['items'] = array_values($cart['items']);
                }
                break;
            }
        }

        // Recalculate total
        $this->recalculateTotal($cart);

        $session->set('cart', $cart);
    }

    public function clearCart(): void
    {
        $this->requestStack->getSession()->remove('cart');
    }

    private function recalculateTotal(array &$cart): void
    {
        $total = 0;
        foreach ($cart['items'] as $item) {
            $total += $item['product']->getPrice() * $item['quantity'];
        }
        $cart['total'] = $total;
    }
}