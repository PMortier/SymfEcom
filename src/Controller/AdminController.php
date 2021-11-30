<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagType;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Commande;
use App\Form\ProductType;
use App\Entity\Reservation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin")
 * @Security("is_granted('ROLE_ADMIN')")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/backoffice", name="admin_backoffice")
     */
    public function adminBackoffice()
    {
        $entityManager = $this->getDoctrine()->getManager();

        $productRepository = $entityManager->getRepository(Product::class);
        $products = $productRepository->findAll();

        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();

        $tagRepository = $entityManager->getRepository(Tag::class);
        $tags = $tagRepository->findAll();

        return $this->render('admin/admin-backoffice.html.twig', [
            'categories' => $categories,
            'products' => $products,
            'tags' => $tags,
        ]);
    }

    //EXERCICE
    /**
     * @Route("/backoffice/commande", name="admin_backoffice_commande")
     */
    /* public function adminBackofficeCommande()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $categoriesRepository = $entityManager->getRepository(Category::class);
        $categories = $categoriesRepository->findAll();
        $commandeRepository = $entityManager->getRepository(Commande::class);
        
        $commandes = $commandeRepository->findAll();
        $commandes = array_reverse($commandes);

        return $this->render('admin/admin-backoffice-commande.html.twig', [
            'categories' => $categories,
            'commandes' => $commandes,
        ]);
    } */

    //CORRECTION!
    /**
     * @Route("/backoffice-commande", name="commande_backoffice_admin")
     */
    public function adminCommandeBackoffice(): Response
    {
        //Nous récupérons l'Utilisateur
        $user = $this->getUser();
        //Cette fonction a pour but de récupérer toutes les Commandes et de les présenter par Utilisateur, en offrant uniquement les fonctions de Validation et de Suppression de Commande/Reservation à la commande Active
        //Pour obtenir la liste des Utilisateurs, nous devons faire appel à l'Entity Manager ainsi qu'aux Repository pertinent
        $entityManager = $this->getDoctrine()->getManager();
        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();
        $userRepository = $entityManager->getRepository(\App\Entity\User::class);
        //Nous récupérons la totalité des Users
        $users = $userRepository->findAll();
        //Il s'agit ici de préparer un tableau de paires de commandes User
        //Tableau des utilisateurs: commandeUsers
        //Tableau des deux types des commandes: commandeArray
        //Deux tableaux de Commande: activeArray et pastArray
        $commandeUsers = [];
        foreach ($users as $userUnit) {
            //On récupère les commandes de l'utilisateur:
            $userCommandes = $userUnit->getCommandes();
            //On initialise $activeCommande et $pastCommandes afin de les utiliser pendant et après la boucle
            $activeCommandes = [];
            $pastCommandes = [];
            foreach ($userCommandes as $commande) {
                if ($commande->getStatus() == 'panier') {
                    array_push($activeCommandes, $commande);
                } else {
                    array_push($pastCommandes, $commande); //array_push est une commande PHP qui place une entrée dans un tableau (ici, $commande dans le tableau $pastCommandes)
                }
            }
            //On crée le tableau qui contiendra les deux types de commandes avant de le placer dans notre tableau de tableaux
            if(!empty($activeCommandes) || !empty($pastCommandes)){
                $commandeArray = [$activeCommandes, $pastCommandes];
                array_push($commandeUsers, $commandeArray);
            }
            
        }

        //Nous transmettons les deux résultats à notre vue Twig:
        return $this->render('admin/commande-backoffice-admin.html.twig', [
            'categories' => $categories,
            'user' => $user,
            'commandes' => $commandeUsers,
        ]);
    }


    /**
     * @Route("/product/create", name="product_create")
     */
    public function createProduct(Request $request): Response
    {
        // Cette route a pour objectif de créer un nouveau produit selon les informations transmises par l'utilisateur via un formulaire
        // Nous commençons donc par récupérer l'Entity Manager afin de dialoguer avec la BDD
        $entityManager = $this->getDoctrine()->getManager();

        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();
        // Nous créons notre objet Product et le formulaire lié
        $product = new Product;
        $productForm = $this->createForm(ProductType::class, $product);
        // Nous appliquons la Request à notre formulaire et si valide, nous persistons $product
        $productForm->handleRequest($request);
        if($request->isMethod('post') && $productForm->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->redirect($this->generateUrl('admin_backoffice'));
        }
        //Si le formulaire n'est pas validé, nous le transmettons par la vue
        return $this->render('index/dataform.html.twig', [
            'categories' => $categories,
            'formName' => 'Création de Produit',
            'dataForm' => $productForm->createView(),
        ]);
    }

    /**
     * @Route("/tag/create", name="tag_create")
     */
    public function createTag(Request $request): Response 
    {
        // Cette route a pour objectif de créer un nouveau produit selon les informations transmises par l'utilisateur via un formulaire
        // Nous commençons donc par récupérer l'Entity Manager afin de dialoguer avec la BDD
        $entityManager = $this->getDoctrine()->getManager();

        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();
        // Nous créons notre objet Tag et le formulaire lié
        $tag = new Tag;
        $tagForm = $this->createForm(TagType::class, $tag);
        // Nous appliquons la Request à notre formulaire et si valide, nous persistons $tag
        $tagForm->handleRequest($request);
        if ($request->isMethod('post') && $tagForm->isValid()) {
            $entityManager->persist($tag);
            $entityManager->flush();
            return $this->redirect($this->generateUrl('admin_backoffice'));
        }
        //Si le formulaire n'est pas validé, nous le transmettons par la vue
        return $this->render('index/dataform.html.twig', [
            'categories' => $categories,
            'formName' => 'Création de Tag',
            'dataForm' => $tagForm->createView(),
        ]);
    }

    /**
     * @Route("/product/update/{productId}", name="product_update")
     */
    public function updateProduct(Request $request, $productId = false): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $productRepository = $entityManager->getRepository(Product::class);

        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();

        $product = $productRepository->find($productId);

        if (!$product) {
            return $this->redirect($this->generateUrl('admin_backoffice'));
        }

        $productForm = $this->createForm(ProductType::class, $product);

        $productForm->handlerequest($request);

        if($request->isMethod('post') && $productForm->isValid()) {
            $productName = $product->getName();
            $products = $productRepository->findBy(['name' => $productName]);
            foreach($products as $productUnit){
                if($productUnit->getId()!= $product->getId()) {
                    return new Response('Un produit possède déjà ce nom');
                }
            }

            $entityManager->persist($product);
            $entityManager->flush();
            return $this->redirect($this->generateUrl('admin_backoffice'));
        }

        return $this->render('index/dataform.html.twig', [
            'categories' => $categories,
            'formName' => 'Modification du Produit',
            'dataForm' => $productForm->createview(),
        ]);
    }

    /**
     * @Route("/tag/update/{tagId}", name="tag_update")
     */
    public function updateTag(Request $request, $tagId = false): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $tagRepository = $entityManager->getRepository(Tag::class);

        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();

        $tag = $tagRepository->find($tagId);

        if (!$tag) {
            return $this->redirect($this->generateUrl('admin_backoffice'));
        }

        $tagForm = $this->createForm(TagType::class, $tag);

        $tagForm->handlerequest($request);

        if ($request->isMethod('post') && $tagForm->isValid()) {
            $tagName = $tag->getName();
            $tags = $tagRepository->findBy(['name' => $tagName]);
            foreach ($tags as $tagUnit) {
                if ($tagUnit->getId() != $tag->getId()) {
                    return new Response('Un tag possède déjà ce nom');
                }
            }

            $entityManager->persist($tag);
            $entityManager->flush();
            return $this->redirect($this->generateUrl('admin_backoffice'));
        }

        return $this->render('index/dataform.html.twig', [
            'categories' => $categories, 
            'formName' => 'Modification du Tag',
            'dataForm' => $tagForm->createview(),
        ]);
    }

    /**
     * @Route("/product/delete/{productId}", name="product_delete")
     */
    public function deleteProduct($productId=false)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $productRepository = $entityManager->getRepository(Product::class);
        $product = $productRepository->find($productId);

        if (!$product) {
            return $this->redirect($this->generateUrl('admin_backoffice'));
        }

        $entityManager->remove($product);
        $entityManager->flush();
        return $this->redirect($this->generateUrl('admin_backoffice'));
    }

    /**
     * @Route("/tag/delete/{tagId}", name="tag_delete")
     */
    public function deleteTag($tagId=false)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $tagRepository = $entityManager->getRepository(Tag::class);
        $tag = $tagRepository->find($tagId);

        if (!$tag) {
            return $this->redirect($this->generateUrl('admin_backoffice'));
        }

        $entityManager->remove($tag);
        $entityManager->flush();
        return $this->redirect($this->generateUrl('admin_backoffice'));
    }




    /**
     * @Route("/deleteReservation/{reservationId}", name="delete_reservation_admin")
     */
    public function adminDeleteReservation($reservationId = false)
    {

        $entityManager = $this->getDoctrine()->getManager();
        $reservationRepository = $entityManager->getRepository(Reservation::class);
        $reservation = $reservationRepository->find($reservationId);

        if (!$reservation || !($reservation->getCommande()) || ($reservation->getCommande()->getStatus() != 'panier')) {
            return $this->redirect($this->generateUrl('commande_backoffice_admin'));
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

        if ($commande->getReservations()->isEmpty() && $commande->getStatus() == 'panier') {
            $entityManager->remove($commande);
        }

        $entityManager->flush();

        return $this->redirect($this->generateUrl('commande_backoffice_admin'));
    }

    /**
     * @Route("/deleteCommande/{commandeId}", name="delete_commande_admin")
     */
    public function adminDeleteCommande($commandeId = false)
    {

        $entityManager = $this->getDoctrine()->getManager();
        $commandeRepository = $entityManager->getRepository(Commande::class);
        $commande = $commandeRepository->find($commandeId);

        if (!$commande || ($commande->getStatus() != 'panier')) {
            return $this->redirect($this->generateUrl('commande_backoffice_admin'));
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

        return $this->redirect($this->generateUrl('commande_backoffice_admin'));
    }

    /**
     * @Route("/validateCommande/{commandeId}", name="validate_commande_admin")
     */
    public function adminValidateCommande($commandeId = false)
    {

        $entityManager = $this->getDoctrine()->getManager();
        $commandeRepository = $entityManager->getRepository(Commande::class);
        $commande = $commandeRepository->find($commandeId);

        if (!$commande || ($commande->getStatus() != "panier")) {
            return $this->redirect($this->generateUrl('commande_backoffice_admin'));
        } else {
            $commande->setStatus('validee');
            $entityManager->persist($commande);
        }

        $entityManager->flush();

        return $this->redirect($this->generateUrl('commande_backoffice_admin'));
    }

}
