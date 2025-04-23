<?php

namespace App\Twig;

use App\Service\CartService;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CartExtension extends AbstractExtension
{
    private $cartService;
    private $requestStack;

    public function __construct(CartService $cartService, RequestStack $requestStack)
    {
        $this->cartService = $cartService;
        $this->requestStack = $requestStack;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_cart', [$this, 'getCart']),
        ];
    }

    public function getCart()
    {
        return $this->cartService->getCart();
    }
}