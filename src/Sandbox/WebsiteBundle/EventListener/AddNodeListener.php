<?php

namespace Sandbox\WebsiteBundle\EventListener;


use Kunstmaan\NodeBundle\Event\NodeEvent;
use Sandbox\WebsiteBundle\Entity\IHostable;
use Sandbox\WebsiteBundle\Entity\IPlaceFromTo;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AddNodeListener {
    private $container;
    /** @var \Doctrine\ORM\EntityManager $em */
    private $em;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine.orm.entity_manager');
    }

    public function onAddNode(NodeEvent $nodeEvent)
    {
        $lang = $nodeEvent->getNodeTranslation()->getLang();
        $page = $nodeEvent->getPage();

        if($page instanceof IHostable){
            //if hosts were defined manually return
            if($page->getHosts()->count() > 0) return;

            $node = $nodeEvent->getNode();
            //get node parent
            $parent = $node->getParent();
            //get parent node page
            $parentPage = $parent->getNodeTranslation($lang, true)->getRef($this->em);

            //PlaceOverviewPage add hosts from parentPage
            if($parentPage instanceof PlaceOverviewPage){
                //add hosts to page
                /** @var PlaceOverviewPage $parentPage  */
                foreach ($parentPage->getHosts() as $host) {
                    $page->addHost($host);
                }
             //if current page is IPlaceFromTo(news and articles) get hosts from places.
            }else if($page instanceof IPlaceFromTo){

                $hosts = [];
                //get unique hosts from places
                foreach ($page->getPlaces() as $place) {
                    foreach ($place->getHosts() as $host) {
                        $hosts[$host->getId()] = $host;
                    }
                }
                foreach ($page->getFromPlaces() as $place) {
                    foreach ($place->getHosts() as $host) {
                        $hosts[$host->getId()] = $host;
                    }
                }
                //add hosts to page
                foreach ($hosts as $host) {
                    /** @var IHostable $page */
                    $page->addHost($host);
                }

            }

            //save page hosts to db
            $this->em->persist($page);
            $this->em->flush();

        }
    }
} 