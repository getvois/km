<?php

namespace Sandbox\WebsiteBundle\Command;


use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Sandbox\WebsiteBundle\Entity\Pages\HotelPage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HotelliveebHotelPlaceCommand extends ContainerAwareCommand{
    protected function configure()
    {
        $this
            ->setName('travelbase:import:hotelliveeb:addplaces')
            ->setDescription('add places to hotelliveeb hotels')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $nodes = $em->getRepository('KunstmaanNodeBundle:Node')
            ->findBy(['deleted' => 0, 'refEntityName' => 'Sandbox\WebsiteBundle\Entity\Pages\HotelPage']);

        if(!$nodes) $nodes = [];

        $total = count($nodes);
        $i = 1;
        foreach ($nodes as $node) {
            var_dump($i++ . '/' . $total);
            $this->addPlaceToHotel($node);
        }

    }

    private function addPlaceToHotel(Node $node)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        /** @var NodeTranslation[] $translations */
        $translations = $node->getNodeTranslations(true);

        foreach ($translations as $translation) {
            $lang = $translation->getLang();
            /** @var HotelPage $page */
            $page = $translation->getRef($em);

            $page->removeAllPlaces();

            $city = '';
            if($page->getCity())$city = $page->getCity();
            elseif($page->getCityParish()) $city = $page->getCityParish();

            //set place to hotel based on city
            if($city){
                //find place page
                $place = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
                    ->findOneBy(['title' => $city]);
                if(!$place) {
                    var_dump('place not found in db '. $city);
                    break;
                }

                //get place page node
                $node2 = $em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($place);
                if(!$node2) {
                    var_dump('Node node found for city'. $city);
                    continue;
                }

                $translation = $node2->getNodeTranslation($lang, true);
                if($translation){
                    $placePage = $translation->getRef($em);
                    if($placePage){
                        $page->addPlace($placePage);
                        $em->persist($page);
                    }
                }
            }

        }
        $em->flush();

    }
}