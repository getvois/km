<?php

namespace Sandbox\WebsiteBundle\Helper;


use Doctrine\ORM\EntityManager;

class AdHelper {
    private $em;

    function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getAds($page, $lang)
    {
        $class = get_class($page);
        $ads = $this->em->createQueryBuilder()
            ->select('a')
            ->from('SandboxWebsiteBundle:Ad', 'a')
            ->join('a.hosts', 'h')
            ->where('a.pagetypes = :class')
            ->setParameter(':class', $class)
            ->andWhere('a.lang = :lang')
            ->setParameter(':lang', $lang)
            ->getQuery()
            ->getResult();

        return $ads;
    }
}