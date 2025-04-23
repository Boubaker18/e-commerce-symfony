<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FavoritesController extends AbstractController
{
    #[Route('/favorites', name: 'app_favorites')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        /** @var User $user */
        $user = $this->getUser();
        
        return $this->render('favorites/index.html.twig', [
            'favorites' => $user->getFavorites(),
        ]);
    }

    #[Route('/favorites/toggle/{id}', name: 'app_favorites_toggle')]
    public function toggle(Product $product, EntityManagerInterface $entityManager, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        /** @var User $user */
        $user = $this->getUser();
        
        $isFavorite = false;
        foreach ($user->getFavorites() as $favorite) {
            if ($favorite->getId() === $product->getId()) {
                $isFavorite = true;
                break;
            }
        }
        
        if ($isFavorite) {
            $user->removeFavorite($product);
            $this->addFlash('success', 'Produit retiré des favoris.');
        } else {
            $user->addFavorite($product);
            $this->addFlash('success', 'Produit ajouté aux favoris.');
        }
        
        $entityManager->flush();
        
        // Redirect back to the referring page
        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('app_product_index'));
    }
}