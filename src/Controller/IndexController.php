<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Commande;
use App\Entity\Reservation;
use App\Entity\Tag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {   
        //Nous récupérons l'utilisateur
        $user = $this->getUser();
        // Notre fonction index() nous présente tous les produits de notre application
        // A cette fin, nous récupérons l'Entity MAnager et le Repositopry Product
        $entityManager = $this->getDoctrine()->getManager();
        $productRepository = $entityManager->getRepository(Product::class);
        // Nous récupérons la liste des catégories à transmettre au header
        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();
        // Nous récupérons TOUS les éléments de la table Product
        $products = $productRepository->findAll();
        shuffle($products);
        // Nous transmettons les variables pertinentes à notre vue Index
        return $this->render('index/index.html.twig', [
            'user' => $user,
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    /**
     * @Route("/category/{categoryId}", name="index_category")
     */
    public function indexCategory($categoryId=false)
    {
        //Nous récupérons l'utilisateur
        $user = $this->getUser();

        $entityManager = $this->getDoctrine()->getManager();
        $categoryRepository = $entityManager->getRepository(Category::class);
        $category = $categoryRepository->find($categoryId);
        $categories = $categoryRepository->findAll();

        if (!$category) {
            return $this->redirect($this->generateUrl('index'));
        }

        $products = $category->getProducts()->toArray(); // toArray() rend un tableau pour la fonction shuffle
        shuffle($products);

        return $this->render('index/index.html.twig', [
            'user' => $user,
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    /**
     * @Route("/tag/{tagId}", name="index_tag")
     */
    public function indexTag($tagId = false)
    {
        //Nous récupérons l'utilisateur
        $user = $this->getUser();
        
        $entityManager = $this->getDoctrine()->getManager();

        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();

        $tagRepository = $entityManager->getRepository(Tag::class);
        $tag = $tagRepository->find($tagId);

        if (!$tag) {
            return $this->redirect($this->generateUrl('index'));
        }

        $products = $tag->getProducts()->toArray(); // toArray() rend un tableau pour la fonction shuffle
        shuffle($products);

        return $this->render('index/index.html.twig', [
            'user' => $user,
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    /**
     * @Route("/taglist", name="tag_list")
     */
    public function tagList() {
        //Cette fonction affiche une liste de tous les Tags afin de laisser à l'utilisateur la possibilité de parcourir les fichiers liés à ces derniers
        $entityManager = $this->getDoctrine()->getManager();
        $tagRepository = $entityManager->getRepository(Tag::class);
        $tags = $tagRepository->findAll();

        //Nous récupérons la liste des catégories
        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();

        //Nous renvoyons la liste des Tags vers notre vue
        return $this->render('index/taglist.html.twig', [
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }



    /**
     * @Route("/product/fiche/{productId}", name="product_fiche")
     */
    public function ficheProduit(Request $request, $productId=false): Response
    {
        //Nous récupérons l'utilisateur en cours
        $user=$this->getUser();
        
        $entityManager = $this->getDoctrine()->getManager();
        $productRepository = $entityManager->getRepository(Product::class);
        $product = $productRepository->find($productId);

        // Juste pour l'affichage des catégories dans la navbar
        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();
        // ---------
    
        if (!$product) {
            return $this->redirect($this->generateUrl('index'));
        }

        // Nous créons un formulaire afin de pouvoir communiquer la quantité que nous désirons acheter
        $buyForm = $this->createFormBuilder()
                        ->add('quantite', IntegerType::class, [
                            'label' => 'quantité',
                            'attr' => [
                                'min' => 1,
                            ]
                        ])
                        ->add('valider', SubmitType::class, [
                            'label' => 'Acheter',
                            'attr' => [
                                'class' => 'w3-button w3-black w3-margin-bottom',
                                'style' => 'margin-top:5px;',
                            ]
                        ])
                        ->getForm();
        // Nous appliquons la Request sur notre formulaire
        $buyForm->handleRequest($request);
        //Si notre formulaire est validé, nous appliquons l'achat
        if($user && $request->isMethod('post') && $buyForm->isValid() && ($product->getStock()>0) ) {
            // Nous récupérons les valeurs du formulaire sous forme de tableau associatif
            $data = $buyForm->getData();
            $quantity = $data['quantite'];

            $reservation = new Reservation;
            $reservation->setProduct($product);

            //Si le produit existe, nous procédons à une décrémentation de la valeur de sa variable $stock
            //Mais seulemnent si le stock n'est pas inférieur à la quantité demandée
            if($product->getStock() > $quantity) {
                $product->setStock($product->getStock() - $quantity);
                $reservation->setQuantity($quantity);
            } else {
                $reservation->setQuantity($product->getStock());
                $product->setStock(0);
            }

            $commandeRepository = $entityManager->getRepository(Commande::class);
            $status = 'panier';
            $userCommande = $commandeRepository->findOneBy(['status' => $status, 'user' => $user]);

            if (!$userCommande) {
                $userCommande = new Commande;
                $userCommande->setUser($user); // Nous lui attribuons l'utilisateur actuellement connecté
                $reservation->setCommande($userCommande);
            } else {
                $reservation->setCommande($userCommande);
            }

            $entityManager->persist($userCommande);
            $entityManager->persist($product);
            $entityManager->persist($reservation);
            
            $entityManager->flush();

            // Ici nous renvoyons vers la fonction chargée spécifiquement d'afficher la fiche produit pour une question de cohérence au niveau de la modularité
            return $this->redirect($this->generateUrl('product_fiche', [
                'productId' => $productId,
            ]));
        }

        return $this->render('index/fiche-produit.html.twig', [
            'user' =>$user,
            'categories' => $categories,
            'product' => $product,
            'buyForm' => $buyForm->createView(),
        ]);
    }
 
    /**
     * @Route("/product/buy/{productId}", name="product_buy")
     */
    /* public function buyProduct($productId=false)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();

        $productRepository = $entityManager->getRepository(Product::class);
        $product = $productRepository->find($productId);

        if(!$product) {
            return $this->redirect($this->generateUrl('index'));
        }

        if($product->getStock() > 0) {
            $quantity = 1;
            $product->setStock($product->getStock() - $quantity);
            $entityManager->persist($product);
            $entityManager->flush();
        }

        // Ici nous renvoyons vers la fonction chargée spécifiquement d'afficher la fiche produit pour une question de cohérence au niveau de la modularité
        return $this->redirect($this->generateUrl('product_fiche',[
            'productId' => $productId,
        ]));
    } */
 
}
