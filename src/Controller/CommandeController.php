<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Commande;
use App\Entity\Reservation;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/commande")
 * @Security("is_granted('ROLE_USER')")
 */
class CommandeController extends AbstractController
{
    /**
     * @Route("/backoffice", name="commande_backoffice")
     */
    public function commandeBackoffice(): Response
    {
        //Nous récupérons l'utilisateur
        $user = $this->getUser();
        //Cette fonction a pour but de nous aider à traiter les différentes commandes de Product passées en tant qu'utilisateur
        //Pour obtenir la liste des Commandes et Reservation, nous devons faire appel à l'Entity Manager ainsi qu'au Repository pertinent
        $entityManager = $this->getDoctrine()->getManager();
        $categoriesRepository = $entityManager->getRepository(Category::class);
        $categories = $categoriesRepository->findAll();
        $commandeRepository = $entityManager->getRepository(Commande::class);
        //Nous récupérons la totalité des commandes avant de déceller celle en mode panier via une boucle
        $commandes = $commandeRepository->findBy(['user' => $user]);
        //On initialise $activeCommande et $pastCommande afin de les utiliser pendant et après la boucle
        $activeCommande = null;
        $pastCommandes = [];
        foreach($commandes as $commande) {
            if($commande->getStatus() == "panier") {
                $activeCommande = $commande;
            } else {
                array_push($pastCommandes, $commande); // array_push est une fonction php qui place une entrée dans un tableau (ici, $commande dans le tableau $pastCommandes)
            }
        }
        //Nous transmettons les deux résultats à notre vue Twig
        return $this->render('commande/commande-backoffice.html.twig', [
            'user' => $user,
            'categories' => $categories,
            'activeCommande' => $activeCommande,
            'commandes' => $pastCommandes,
        ]);
    }

    /* ATTENTION: Si on veut que l'administrateur puisse agir sur les commandes et réservations, il est préférable de recréer les fonctions delete,update,validate dans le AdminController avec une adaptation des tests/restrictions */

    /**
     * @Route("/deleteReservation/{reservationId}", name="delete_reservation")
     */
    public function deleteReservation($reservationId=false)
    {
        $user = $this->getUser();

        $entityManager = $this->getDoctrine()->getManager();
        $reservationRepository = $entityManager->getRepository(Reservation::class);
        $reservation = $reservationRepository->find($reservationId);

        if(!$reservation || !($reservation->getCommande()) || ($user != $reservation->getCommande()->getUser()) || ($reservation->getCommande()->getStatus() != 'panier')) {
            return $this->redirect($this->generateUrl('commande_backoffice'));
        }

        //Avant de supprimer la Reservation, nous restituons la quantité réservée au stock de la référence Product
        $product = $reservation->getProduct();
        $product->setStock($product->getStock() + $reservation->getQuantity());

        $commande = $reservation->getCommande();

        /* if(!$commande) {
            return $this->redirect($this->generateUrl('commande_backoffice'));
        } */

        $commande->removeReservation($reservation);

        $entityManager->persist($product);
        /* $entityManager->persist($commande); */ // Ce persist n'est pas utile car reservation est dominant sur commande
        $entityManager->remove($reservation);

        if($commande->getReservations()->isEmpty() && $commande->getStatus() == 'panier') {
            $entityManager->remove($commande);
        }

        $entityManager->flush();

        return $this->redirect($this->generateUrl('commande_backoffice'));

    }

    /**
     * @Route("/deleteCommande/{commandeId}", name="delete_commande")
     */
    public function deleteCommande($commandeId=false)
    {
        $user = $this->getUser();
        
        $entityManager = $this->getDoctrine()->getManager();
        $commandeRepository = $entityManager->getRepository(Commande::class);
        $commande = $commandeRepository->find($commandeId);

        if (!$commande || ($user != $commande->getUser()) || ($commande->getStatus() != 'panier')) {
            return $this->redirect($this->generateUrl('commande_backoffice'));
        }
        
        $reservations = $commande->getReservations();
        foreach ($reservations as $reservation) {
            $product = $reservation->getProduct();
            $product->setStock($product->getStock() + $reservation->getQuantity());
            $entityManager->persist($product);
            $entityManager->remove($reservation);
        }

        $entityManager->remove($commande);

        $entityManager->flush();

        return $this->redirect($this->generateUrl('commande_backoffice'));

    }

    /**
     * @Route("/validateCommande/{commandeId}", name="validate_commande")
     */
    public function validateCommande($commandeId=false)
    {
        $user = $this->getUser();
        
        $entityManager = $this->getDoctrine()->getManager();
        $commandeRepository = $entityManager->getRepository(Commande::class);
        $commande = $commandeRepository->find($commandeId);

        if(!$commande || ($commande->getUser() != $user ) || ($commande->getStatus() != "panier")) {
            return $this->redirect($this->generateUrl('commande_backoffice'));
        } else {
            $commande->setStatus('validee');
            $entityManager->persist($commande);
        }

        $entityManager->flush();

        return $this->redirect($this->generateUrl('commande_backoffice'));

    }
}
