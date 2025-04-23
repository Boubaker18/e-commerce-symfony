<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Repository\OrderRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        ProductRepository $productRepository,
        UserRepository $userRepository,
        OrderRepository $orderRepository,
        CartService $cartService
    ): Response
    {
        $products = $productRepository->findAll();
        
        $data = [
            'products' => $products,
            'cart' => $cartService->getCart(),
        ];
        
        // Add statistics for admin users
        if ($this->isGranted('ROLE_ADMIN')) {
            $data['productCount'] = count($products);
            $data['userCount'] = count($userRepository->findAll());
            
            // Get order statistics
            $orders = $orderRepository->findAll();
            $data['orderCount'] = count($orders);
            
            // Calculate total sales
            $totalSales = 0;
            foreach ($orders as $order) {
                $totalSales += $order->getTotal();
            }
            $data['totalSales'] = $totalSales;
        }
        
        return $this->render('home/index.html.twig', $data);
    }
}