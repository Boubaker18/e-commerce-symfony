<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    #[Route('', name: 'app_cart', methods: ['GET'])]
    public function index(CartService $cartService): Response
    {
        $cart = $cartService->getCart();
        
        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add')]
    public function add(Product $product, CartService $cartService, Request $request): Response
    {
        $cartService->addToCart($product->getId());
        
        $this->addFlash('success', 'Produit ajouté au panier avec succès.');
        
        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('app_product_index'));
    }

    #[Route('/remove/{id}', name: 'app_cart_remove')]
    public function remove(Product $product, CartService $cartService, Request $request): Response
    {
        $cartService->removeFromCart($product->getId());
        
        $this->addFlash('success', 'Produit retiré du panier.');
        
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/decrease/{id}', name: 'app_cart_decrease')]
    public function decrease(Product $product, CartService $cartService, Request $request): Response
    {
        $cartService->decreaseQuantity($product->getId());
        
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/clear', name: 'app_cart_clear')]
    public function clear(CartService $cartService): Response
    {
        $cartService->clearCart();
        
        $this->addFlash('success', 'Votre panier a été vidé.');
        
        return $this->redirectToRoute('app_cart');
    }
}