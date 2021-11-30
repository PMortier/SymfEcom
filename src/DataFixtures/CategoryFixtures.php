<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $categoryNames = ['Armoire', 'Bureau', 'Canape', 'Chaise', 'Lit', 'Autre'];

        $categoryDescription = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
        
        for($i=0;$i<count($categoryNames);++$i) {
            $category = new Category();
            $category->setName($categoryNames[$i]);
            $category->setDescription($categoryDescription);

            $manager->persist($category);
        }
        
        $manager->flush();
    }
}
