<?php

namespace App\Controller;

use LogicException;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passEncoder)
    {
        //Cette route a pour fonction de créer un nouvel Utilisateur pour notre connexion
        //Nous allons utiliser un formulaire interne afin de créer notre utilisateur
        // Pour enregistrer l'Utilisateur, nous devons d'abord récupérer l'Entity Manager
        $entityManager = $this->getDoctrine()->getManager();
        //Nous créons notre formulaire interne
        $userForm = $this->createFormBuilder()
                        ->add('username', TextType::class, [
                            'label' => 'Nom de l\'utilisateur',
                            'attr' => [
                                'class' => 'w3-input w3-border w3-round w3-light-grey',
                            ],
                        ])
                        ->add('password', RepeatedType::class, [
                            'type' => PasswordType::class,
                            'required' => true,
                            'first_options' => ['label' => 'Mot de passe',
                                'attr' => [
                                    'class' => 'w3-input w3-border w3-round w3-light-grey',
                                ],
                            ],
                            'second_options' => ['label' => 'Confirmation de mot de passe',
                                'attr' => [
                                    'class' => 'w3-input w3-border w3-round w3-light-grey',
                                ],
                            ],
                        ])
                        ->add('roles', ChoiceType::class, [
                            'choices' => [
                                'Role: USER' => 'ROLE_USER',
                                'Role: ADMIN' => 'ROLE_ADMIN',
                            ],
                            'expanded' => false,
                            'multiple' => true,
                            'attr' => [
                                'class' => 'w3-input w3-border w3-round w3-light-grey',
                            ],
                        ])
                        ->add('register', SubmitType::class, [
                            'label' => 'Créer son compte',
                            'attr' => [
                                'class' => 'w3-button w3-green w3-margin-bottom',
                                'style' => 'margin-top:5px;'
                            ],
                        ])
                        ->getForm()
                        ;
                //Nous traions les données reçues au sein de notre formulaire
                $userForm->handleRequest($request);
                if($request->isMethod('post') && $userForm->isValid()) {
                    //On récupère les informations du formulaire
                    $data = $userForm->getData();
                    //Nous créons et renseignons notre Entity User
                    $user = new User;
                    $user->setUsername($data['username']);
                    $user->setPassword($passEncoder->encodePassword($user, $data['password']));
                    $user->setRoles($data['roles']);
                    $entityManager->persist($user);
                    $entityManager->flush();
                    return $this->redirect($this->generateUrl('app_login'));
                }
                //Si le formulaire n'est pas validé, nous le prsentons à l'utilisateur
                return $this->render('index/dataform.html.twig', [
                    'formName' => 'Inscription Utilisateur',
                    'dataForm' => $userForm->createView(),
                ]);
    }
    
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
