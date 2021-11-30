<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $productNames = ['Tulipe', 'Rose', 'Lila', 'Pivoine', 'Hortensia', 'Margueritte', 'Geranium', 'Tournesol', 'Coquelicot', 'Violette', 'Jacinthe', 'Mimosa', 'Muguet', 'Oeillet', 'Lis'];
       
        $productDescription = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.';
        
        for($i=0;$i<15;++$i) {
            $product = new Product();
            $product->setName($productNames[rand(0, (count($productNames) - 1))] . ' #' . rand(1, 999));
            $product->setDescription($productDescription);
            $product->setPrice(rand(98, 998) + 0.99);
            $product->setStock(rand(1, 100));

            $manager->persist($product);
        }

        $manager->flush();
    }
}
