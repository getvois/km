<?php

namespace Sandbox\WebsiteBundle\Twig\Extension;


use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\Node;
use Sandbox\WebsiteBundle\Entity\Pages\HotelPage;
use Sandbox\WebsiteBundle\Entity\Pages\PackagePage;
use Twig_SimpleFunction;

class TotalPackagesExtension extends \Twig_Extension{

    /** @var  EntityManager */
    private $em;

    function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('total_packages', array($this, 'getTotalPackages')),
        );
    }

    /**
     * @param $lang
     * @return null|PackagePage
     */
    public function getTotalPackages($lang){

        $packages = $this->em->getRepository('SandboxWebsiteBundle:Pages\PackagePage')
            ->getTotalPages($lang);

        return $packages;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'total_packages';
    }
}