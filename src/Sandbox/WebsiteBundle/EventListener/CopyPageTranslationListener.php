<?php

namespace Sandbox\WebsiteBundle\EventListener;


use Kunstmaan\NodeBundle\Entity\NodeVersion;
use Kunstmaan\NodeBundle\Event\CopyPageTranslationNodeEvent;
use Kunstmaan\NodeBundle\Repository\NodeRepository;
use Kunstmaan\PagePartBundle\Helper\HasPagePartsInterface;
use Kunstmaan\PagePartBundle\Helper\PagePartConfigurationReader;
use Kunstmaan\PagePartBundle\Repository\PagePartRefRepository;
use Sandbox\WebsiteBundle\Entity\ICompany;
use Sandbox\WebsiteBundle\Entity\IPlaceFromTo;
use Symfony\Component\DependencyInjection\Container;

class CopyPageTranslationListener {
    private $container;
    /** @var \Doctrine\ORM\EntityManager $em */
    private $em;
    private $user;
    private $kernel;

    function __construct(Container $container)
    {
        $this->kernel = $container->get('kernel');
        $this->container = $container;
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->user = $container->get('security.context')->getToken()->getUser();
    }

    public function onCopyPageTranslation(CopyPageTranslationNodeEvent $nodeEvent)
    {
        $page = $nodeEvent->getOriginalPage();
        $langPage = $nodeEvent->getPage();
        if($page instanceof HasPagePartsInterface){

            $pagePartConfigurationReader = new PagePartConfigurationReader($this->kernel);
            $contexts = $pagePartConfigurationReader->getPagePartContexts($page);
            /** @var PagePartRefRepository $pagePartRepo */
            $pagePartRepo = $this->em->getRepository('KunstmaanPagePartBundle:PagePartRef');
            foreach ($contexts as $context) {
                if(!$pagePartRepo->hasPageParts($langPage, $context)) {
                    $pagePartRepo->copyPageParts($this->em, $page, $langPage, $context);
                }
            }
        }

        if($page instanceof IPlaceFromTo && $langPage instanceof IPlaceFromTo){
            $langPage->removeAllPlaces();
            $langPage->removeAllFromPlaces();

            $this->selectPlaces($page, $langPage, $nodeEvent->getNodeTranslation()->getLang());
        }

        if($page instanceof ICompany && $langPage instanceof ICompany){
            foreach ($langPage->getCompanies() as $company) {
                $langPage->removeCompany($company);
            }

            foreach ($page->getCompanies() as $company) {
                $langPage->addCompany($company);
            }

        }
    }

    private function selectPlaces(IPlaceFromTo $page, IPlaceFromTo $newPage, $newLang)
    {
        //add selected places to other translations
        foreach ($page->getPlaces() as $place) {
            /** @var NodeRepository $nodeRepo */
            $nodeRepo = $this->em->getRepository('KunstmaanNodeBundle:Node');
            $node = $nodeRepo->getNodeFor($place);
            $translation = $node->getNodeTranslation($newLang, true);

            if(!$translation) continue;

            $placePage = $translation->getPublicNodeVersion()->getRef($this->em);

            //add place to news
            $newPage->addPlace($placePage);
        }

        //add selected from places to other translations
        foreach ($page->getFromPlaces() as $place) {
            /** @var NodeRepository $nodeRepo */
            $nodeRepo = $this->em->getRepository('KunstmaanNodeBundle:Node');
            $node = $nodeRepo->getNodeFor($place);
            $translation = $node->getNodeTranslation($newLang, true);

            if(!$translation) continue;

            $placePage = $translation->getPublicNodeVersion()->getRef($this->em);

            //add place to news
            $newPage->addFromPlace($placePage);

        }
        $this->em->persist($newPage);
        $this->em->flush();
    }
}