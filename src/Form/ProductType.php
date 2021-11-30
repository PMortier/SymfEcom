<?php

namespace App\Form;

use App\Entity\Tag;
use App\Entity\Product;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
                'attr' => [
                    'class' => 'w3-input w3-border w3-round w3-light-grey w3-margin-bottom w3-margin-top'
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description du produit',
                'attr' => [
                    'class' => 'w3-input w3-border w3-round w3-light-grey w3-margin-bottom w3-margin-top'
                ],
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix du produit',
                'attr' => [
                    'class' => 'w3-input w3-border w3-round w3-light-grey w3-margin-bottom w3-margin-top'
                ],
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Nombre de produits disponibles',
                'attr' => [
                    'class' => 'w3-input w3-border w3-round w3-light-grey w3-margin-bottom w3-margin-top'
                ],
            ])
            ->add('tags', EntityType::class, [
                'label' => 'Choisir un ou plusieurs tags',
                'attr' => [
                    'class' => 'w3-margin-bottom w3-margin-top',
                ],
                'class' => Tag::class,
                'choice_label' => 'name', //L'attribut de notre Entity que nous voulons utiliser comme label
                'expanded' => true, // expanded = boutons de choix
                'multiple' => true, // multiple = choix multiple => true car nous sommes supposé accueillir plusieurs tag (cf. ManyToMany)
            ])
            ->add('category', EntityType::class, [
                'label' => 'Choisir une catégorie',
                'attr' => [
                    'class' => 'w3-input w3-border w3-round w3-light-grey w3-margin-bottom w3-margin-top',
                ],
                'class' => Category::class,
                'choice_label' => 'name', //L'attribut de notre Entity que nous voulons utiliser comme label
                'expanded' => false, // expanded = boutons de choix
                'multiple' => false, // choix non multiple car 1 catégorie par produit (OneToMany)
            ])
            ->add('valider', SubmitType::class, [
                'label' => 'Valider',
                'attr' => [
                    'class' => 'w3-button w3-black w3-margin-bottom',
                    'style' => 'margin-top:5px;'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
