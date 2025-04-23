<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\CartService;

class PageController extends AbstractController
{
    #[Route('/about', name: 'app_about')]
    public function about(CartService $cartService): Response
    {
        return $this->render('page/about.html.twig', [
            'cart' => $cartService->getCart(),
        ]);
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(CartService $cartService): Response
    {
        return $this->render('page/contact.html.twig', [
            'cart' => $cartService->getCart(),
        ]);
    }

    #[Route('/profile', name: 'app_profile')]
    public function profile(CartService $cartService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        return $this->render('page/profile.html.twig', [
            'cart' => $cartService->getCart(),
        ]);
    }

    #[Route('/favorites', name: 'app_favorites')]
    public function favorites(CartService $cartService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        return $this->render('page/favorites.html.twig', [
            'cart' => $cartService->getCart(),
        ]);
    }

    #[Route('/social/{network}', name: 'app_social')]
    public function social(string $network): Response
    {
        $urls = [
            'facebook' => 'https://facebook.com/ecommerce-symfony',
            'twitter' => 'https://twitter.com/ecommerce-symfony',
            'instagram' => 'https://instagram.com/ecommerce-symfony',
            'linkedin' => 'https://linkedin.com/company/ecommerce-symfony',
        ];
        
        if (isset($urls[$network])) {
            return $this->redirect($urls[$network]);
        }
        
        return $this->redirectToRoute('app_product_index');
    }
}