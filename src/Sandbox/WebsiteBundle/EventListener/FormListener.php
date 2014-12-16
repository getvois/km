<?php
namespace Sandbox\WebsiteBundle\EventListener;

use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\NodeBundle\Entity\NodeVersion;
use Kunstmaan\NodeBundle\Event\NodeEvent;
use Sandbox\WebsiteBundle\Entity\ICompany;
use Sandbox\WebsiteBundle\Entity\IPlaceFromTo;
use Sandbox\WebsiteBundle\Entity\News\NewsPage;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Sandbox\WebsiteBundle\Entity\Place\PlacePage;
use Symfony\Component\DependencyInjection\Container;

class FormListener {

    private $locales;//array of possible languages from config.yml
    private $container;
    /** @var \Doctrine\ORM\EntityManager $em */
    private $em;
    private $user;

    function __construct($locales = [], Container $container)
    {
        $this->locales = $locales;
        $this->container = $container;
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->user = $container->get('security.context')->getToken()->getUser();
    }

    public function onFormPostPersist(NodeEvent $nodeEvent)
    {

        $node = $nodeEvent->getNode();
        $page = $nodeEvent->getPage();
        /** @var NodeTranslation[] $translations */
        $translations = $node->getNodeTranslations(true);

        $originalLanguage = $nodeEvent->getNodeTranslation()->getLang();

        if($page instanceof NewsPage){
            /** @var NewsPage $page */
            if($page->isTranslate()){
                //check translations
                $languages = [];//list of existing translations
                foreach ($translations as $translation) {
                    $languages[] = $translation->getLang();
                }
                $missingLanguages = array_diff($this->locales, $languages);
                //add missing translations
                foreach ($missingLanguages as $language) {
                    //original code from src/Kunstmaan/NodeBundle/Controller/NodeAdminController
                    //func line:118
                    //code line:128
                    $otherLanguageNodeTranslation = $node->getNodeTranslation($originalLanguage, true);
                    $otherLanguageNodeNodeVersion = $otherLanguageNodeTranslation->getPublicNodeVersion();
                    /** @var NewsPage $otherLanguagePage */
                    $otherLanguagePage = $otherLanguageNodeNodeVersion->getRef($this->em);
                    $otherLanguagePage->removeAllPlaces();

                    $myLanguagePage = $this->container->get('kunstmaan_admin.clone.helper')->deepCloneAndSave($otherLanguagePage);
                    $this->em->getRepository('KunstmaanNodeBundle:NodeTranslation')->createNodeTranslationFor($myLanguagePage, $language, $node, $this->user);
                }
            }
        }

        if($page instanceof IPlaceFromTo){
            $this->selectPlaces($originalLanguage, $page);
        }

        if($page instanceof PlaceOverviewPage){
            $translations = $nodeEvent->getNode()->getNodeTranslations(true);

            foreach ($translations as $translation) {
                if($originalLanguage == $translation->getLang()) continue;

                /** @var PlaceOverviewPage $pp */
                $pp = $translation->getRef($this->em);

                foreach ($page->getHosts() as $host) {
                    $pp->addHost($host);
                }

                $this->em->persist($pp);

            }

            $this->em->flush();

        }

        if($page instanceof ICompany){
            $translations = $nodeEvent->getNode()->getNodeTranslations(true);

            foreach ($translations as $translation) {
                if($originalLanguage == $translation->getLang()) continue;

                /** @var ICompany $pp */
                $pp = $translation->getRef($this->em);

                foreach ($page->getCompanies() as $company) {
                    $pp->addCompany($company);
                }

                $this->em->persist($pp);

            }

            $this->em->flush();
        }

    }


    private function selectPlaces($originalLanguage,IPlaceFromTo $page)
    {
        //check for place
        //for each locale get news page in lang
        $checkLangs = array_diff($this->locales, [$originalLanguage]);
        foreach ($checkLangs as $locale) {

            /** @var NodeVersion $nodeVersion */
            $nodeVersion = $this->em->getRepository('KunstmaanNodeBundle:NodeVersion')
                ->findOneBy([
                    'refId' => $page->getId(),
                    'refEntityName' => $page->getEntityName(),
                    'type' => 'public'
                ]);

            if(!$nodeVersion) continue;

            //get news translation
            $nodeTranslation = $nodeVersion->getNodeTranslation()->getNode()->getNodeTranslation($locale, true);
            if(!$nodeTranslation) continue;
            //get news page
            /** @var NewsPage $newsPage */
            $newsPage = $nodeTranslation->getPublicNodeVersion()->getRef($this->em);
            $newsPage->removeAllPlaces();
            $newsPage->removeAllFromPlaces();

            //add selected places to other translations
            foreach ($page->getPlaces() as $place) {
                /** @var NodeVersion $nodeVersion */
                $nodeVersion = $this->em->getRepository('KunstmaanNodeBundle:NodeVersion')
                    ->findOneBy([
                        'refId' => $place->getId(),
                        'refEntityName' => 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage',
                        'type' => 'public'
                    ]);

                if(!$nodeVersion) continue;

                //get place translation
                $nodeTranslation = $nodeVersion->getNodeTranslation()->getNode()->getNodeTranslation($locale, true);

                if(!$nodeTranslation) continue;
                //get place page
                /** @var PlaceOverviewPage $placePage */
                $placePage = $nodeTranslation->getPublicNodeVersion()->getRef($this->em);
                //add place to news
                $newsPage->addPlace($placePage);
            }

            //add selected from places to other translations
            foreach ($page->getFromPlaces() as $place) {
                /** @var NodeVersion $nodeVersion */
                $nodeVersion = $this->em->getRepository('KunstmaanNodeBundle:NodeVersion')
                    ->findOneBy([
                        'refId' => $place->getId(),
                        'refEntityName' => 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage',
                        'type' => 'public'
                    ]);

                if(!$nodeVersion) continue;

                //get place translation
                $nodeTranslation = $nodeVersion->getNodeTranslation()->getNode()->getNodeTranslation($locale, true);

                if(!$nodeTranslation) continue;
                //get place page
                /** @var PlaceOverviewPage $placePage */
                $placePage = $nodeTranslation->getPublicNodeVersion()->getRef($this->em);
                //add place to news
                $newsPage->addFromPlace($placePage);

            }
            $this->em->persist($newsPage);
        }
        $this->em->flush();
    }
} 