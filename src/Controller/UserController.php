<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\CartService;

class UserController extends AbstractController
{
    #[Route('/admin/users', name: 'app_user_index')]
    public function index(CartService $cartService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        return $this->render('user/index.html.twig', [
            'cart' => $cartService->getCart(),
        ]);
    }
}