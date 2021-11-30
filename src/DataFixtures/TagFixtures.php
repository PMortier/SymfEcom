<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class TagFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $tagNames = ['Bureau','Cuisine','Chambre','Sejour','Salle de bain','Jardin','Ecologique','Détente','Vacances','Quotidien'];

        for($i=0;$i<count($tagNames);++$i) {
            $tag = new Tag();
            $tag->setName($tagNames[$i]);
            
            $manager->persist($tag);
        }
        
        $manager->flush();
    }
}
