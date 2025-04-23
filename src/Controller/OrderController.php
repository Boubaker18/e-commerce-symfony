<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/admin/orders', name: 'app_order_index')]
    public function index(OrderRepository $orderRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $orders = $orderRepository->findAll();
        
        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/checkout', name: 'app_checkout')]
    public function checkout(
        CartService $cartService,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Check if user is logged in
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté pour passer une commande.');
            return $this->redirectToRoute('app_login');
        }

        // Check if cart is empty
        $cart = $cartService->getCart();
        if (empty($cart['items'])) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart');
        }

        // Create new order
        $order = new Order();
        $order->setUser($this->getUser());
        $order->setStatus('pending');
        $order->setTotal($cart['total']);

        $entityManager->persist($order);

        // Create order items
        foreach ($cart['items'] as $item) {
            $product = $productRepository->find($item['product']->getId());
            
            // Check if product is in stock
            if ($product->getStock() < $item['quantity']) {
                $this->addFlash('error', 'Le produit "' . $product->getName() . '" n\'est plus disponible en quantité suffisante.');
                return $this->redirectToRoute('app_cart');
            }

            // Update product stock
            $product->setStock($product->getStock() - $item['quantity']);
            $entityManager->persist($product);

            // Create order item
            $orderItem = new OrderItem();
            $orderItem->setOrderRef($order);
            $orderItem->setProduct($product);
            $orderItem->setQuantity($item['quantity']);
            $orderItem->setPrice($product->getPrice());
            
            $entityManager->persist($orderItem);
        }

        // Save everything to database
        $entityManager->flush();

        // Clear cart
        $cartService->clearCart();

        // Add success message
        $this->addFlash('success', 'Votre commande a été passée avec succès! Numéro de commande: ' . $order->getId());

        // Redirect to order confirmation page
        return $this->redirectToRoute('app_order_confirmation', ['id' => $order->getId()]);
    }

    #[Route('/order/confirmation/{id}', name: 'app_order_confirmation')]
    public function confirmation(Order $order): Response
    {
        // Check if the order belongs to the current user
        if ($order->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à accéder à cette commande.');
        }

        return $this->render('order/confirmation.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/my-orders', name: 'app_order_history')]
    public function history(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        return $this->render('order/history.html.twig');
    }
}