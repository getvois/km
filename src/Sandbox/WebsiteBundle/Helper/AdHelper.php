<?php

namespace Sandbox\WebsiteBundle\Helper;


use Doctrine\ORM\EntityManager;
use Sandbox\WebsiteBundle\Entity\Ad;

class AdHelper {
    private $em;

    function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getAds($page, $lang, $host = null)
    {
        $class = get_class($page);

        /** @var Ad[] $ads */
        $ads = $this->em->createQueryBuilder()
            ->select('a')
            ->from('SandboxWebsiteBundle:Ad', 'a')
            ->join('a.hosts', 'h')
            //->where('a.pagetypes = :class')
            //->setParameter(':class', $class)
            ->where('a.lang = :lang')
            ->setParameter(':lang', $lang)
            ->getQuery()
            ->getResult();

        if(!$ads) $ads = [];

        $result = [];

        //filter by page type
        foreach ($ads as $ad) {
            if(in_array($class, $ad->getPagetypes())){
                $result[] = $ad;
            }
        }

        //filter by host
        $filtered = [];
        if($host){
            /** @var Ad $ad */
            foreach ($result as $ad) {
                if($ad->getHosts()->contains($host)){
                    $filtered[] = $ad;
                }
            }
            $result = $filtered;
        }

        //sort by weight
        usort($result, function (Ad $a,Ad $b)
        {
            if ($a->getWeight() == $b->getWeight()) {
                return 0;
            }
            return ($a->getWeight() < $b->getWeight()) ? -1 : 1;
        });

        return $result;
    }
}