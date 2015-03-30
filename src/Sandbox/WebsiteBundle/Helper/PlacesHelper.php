<?php

namespace Sandbox\WebsiteBundle\Helper;


use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PlacesHelper {

    private $em;
    private $host;
    /** @var \Symfony\Component\HttpFoundation\Request  */
    private $request;

    //CACHE
    private static $root;
    private static $places;

    function __construct(EntityManager $em,ContainerInterface $container)
    {
        $this->em = $em;
        $this->host = $container->get('hosthelper')->getHost();
        $this->request = $container->get('request');
    }

    /**
     * @return array
     */
    public function getRoot()
    {
        //get from cache
        if(self::$root)
            return self::$root;

        $lang = $this->request->getLocale();
        $root = $this->em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
            ->getRoot($lang, $this->host);
        //cache result
        self::$root = $root;

        return self::$root;
    }


    /**
     * @return array
     */
    public function getPlaces()
    {
        //get from cache
        if(self::$places)
            return self::$places;


        $lang = $this->request->getLocale();

        $root = $this->getRoot();

        $topPlaces = $this->em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
            ->getByRoot($root['id'], $lang, $this->host);

        //get list of places ids
        $topPlacesIds = [];
        foreach ($topPlaces as $place) {
            $topPlacesIds[] = $place['id'];
        }

        $topChildren = $this->em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
            ->getByParentIds($topPlacesIds, $lang, $this->host);

        //get sub children ids
        $topChildrenChildrenIds = [];
        foreach ($topChildren as $topChildrenChild) {
            $topChildrenChildrenIds[] = $topChildrenChild['id'];
        }

        $topChildrenChildren = $this->em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
            ->getByParentIds($topChildrenChildrenIds, $lang, $this->host);


        //get sub children ids
        $topChildrenChildrenChildrenIds = [];
        foreach ($topChildrenChildren as $topChildrenChildrenChild) {
            $topChildrenChildrenChildrenIds[] = $topChildrenChildrenChild['id'];
        }

        $topChildrenChildrenChildren = $this->em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
            ->getByParentIds($topChildrenChildrenIds, $lang, $this->host);


        //bind children to places
        $placesNodes = [];
        foreach ($topPlaces as $place) {
            $children = [];
            foreach ($topChildren as $child) {
                if($child['parent'] == $place['id']){
                    //$children[] = $child;
                    $childrenChildren = [];
                    foreach ($topChildrenChildren as $childrenChild) {
                        if($child['id'] == $childrenChild['parent']){
                            //$childrenChildren[] = $childrenChild;
                            $childrenChildrenChildren = [];
                            foreach ($topChildrenChildrenChildren as $childrenChildrenChild) {
                                if($childrenChild['id'] == $childrenChildrenChild['parent']){
                                    $childrenChildrenChildren[] = $childrenChildrenChild;
                                }
                            }
                            $childrenChildren[] = ['parent' =>$childrenChild, 'children' => $childrenChildrenChildren];
                        }
                    }
                    $children[] = ['parent' =>$child, 'children' => $childrenChildren];
                }
            }
            $placesNodes[] = ['parent' =>$place, 'children' => $children];
        }

        $topPlaces = null;
        $topPlacesIds = null;
        $topChildren = null;
        $topChildrenChildrenIds = null;
        $topChildrenChildren = null;
        $topChildrenChildrenChildrenIds = null;
        $topChildrenChildrenChildren = null;

        //cache result
        self::$places = $placesNodes;

        return self::$places;
    }

}