<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Beacon;
use AppBundle\Entity\Poi;

class PoiData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $poi = new Poi();
        $poi->setAddress("Médiaquitaine, 15 Rue de Naudet, 33175 Gradignan");
        $poi->setLat('44.7905933');
        $poi->setLon('-0.6113625');
        $poi->setCaption('IUT de Bordeaux 1');
        $poi->setDescription('Notre IUT !');

        $idents = [32120, 32099, 32289, 32319];
        for($i=0; $i<4; $i++) {
        	$b = new Beacon();
        	$b->setCaption('Capteur '.$i);
        	$b->setBtName('MiniBeacon_'.$idents[$i]);

        	$poi->addBeacon($b);
        }

        $manager->persist($poi);
        $manager->flush();
    }
}
