<?php

namespace Sandbox\WebsiteBundle\Twig\Extension;


use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\Node;
use Sandbox\WebsiteBundle\Entity\Pages\HotelPage;
use Sandbox\WebsiteBundle\Entity\Pages\PackagePage;
use Twig_SimpleFunction;

class HotelCheapestPackageExtension extends \Twig_Extension{

    /** @var  EntityManager */
    private $em;

    function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('hotel_cheapest_package', array($this, 'getCheapestPackage')),
        );
    }

    /**
     * @param HotelPage $hotelPage
     * @param $lang
     * @return null|PackagePage
     */
    public function getCheapestPackage(HotelPage $hotelPage, $lang){

        $node = $this->em->getRepository('KunstmaanNodeBundle:Node')
            ->getNodeFor($hotelPage);

        /** @var PackagePage[] $packages */
        $packages = $this->em->getRepository('SandboxWebsiteBundle:Pages\PackagePage')
            ->getPackagesByParent($lang, $node);
        if(!$packages) return null;

        $cheapest = 99999999;
        $page = null;
        foreach ($packages as $package) {
            if($package->getMinprice() && $package->getMinprice() < $cheapest){
                $cheapest = $package->getMinprice();
                $page = $package;
            }
        }

        return $page;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'hotel_cheapest_package';
    }
}