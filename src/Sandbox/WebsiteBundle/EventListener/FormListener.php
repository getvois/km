<?php
namespace Sandbox\WebsiteBundle\EventListener;

use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\NodeBundle\Entity\NodeVersion;
use Kunstmaan\NodeBundle\Event\NodeEvent;
use Kunstmaan\TaggingBundle\Entity\Taggable;
use Kunstmaan\TaggingBundle\Entity\Tagging;
use Kunstmaan\TaggingBundle\Repository\TagRepository;
use Sandbox\WebsiteBundle\Entity\Article\ArticlePage;
use Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage;
use Sandbox\WebsiteBundle\Entity\ICompany;
use Sandbox\WebsiteBundle\Entity\ICopyFields;
use Sandbox\WebsiteBundle\Entity\IHostable;
use Sandbox\WebsiteBundle\Entity\IPlaceFromTo;
use Sandbox\WebsiteBundle\Entity\News\NewsPage;
use Sandbox\WebsiteBundle\Entity\Pages\HotelPage;
use Sandbox\WebsiteBundle\Entity\Pages\PackagePage;
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

        /** @var PlaceOverviewPage $page */
        if($page instanceof PlaceOverviewPage){

            $this->copyHostsToChildren($page, $nodeEvent->getNode());
            $this->em->flush();

            $translations = $nodeEvent->getNode()->getNodeTranslations(true);

            foreach ($translations as $translation) {
                if($originalLanguage == $translation->getLang()) {
                    continue;
                }

                /** @var PlaceOverviewPage $pp */
                $pp = $translation->getRef($this->em);

                $pp->setCityId($page->getCityId());
                $pp->setCountryCode($page->getCountryCode());
                $pp->setLatitude($page->getLatitude());
                $pp->setLongitude($page->getLongitude());
            }

            $this->em->flush();

        }

        //copy companies to all translations
        if($page instanceof ICompany){
            $translations = $nodeEvent->getNode()->getNodeTranslations(true);

            foreach ($translations as $translation) {
                if($originalLanguage == $translation->getLang()) continue;

                /** @var ICompany $pp */
                $pp = $translation->getRef($this->em);

                foreach ($pp->getCompanies() as $company) {
                    $pp->removeCompany($company);
                }
                foreach ($page->getCompanies() as $company) {
                    $pp->addCompany($company);
                }
                $this->em->persist($pp);
            }
            $this->em->flush();
        }

        //copy hosts to all translations
        if($page instanceof IHostable){
            $translations = $nodeEvent->getNode()->getNodeTranslations(true);

            foreach ($translations as $translation) {
                if($originalLanguage == $translation->getLang()) continue;

                /** @var IHostable $pp */
                $pp = $translation->getRef($this->em);

                foreach ($pp->getHosts() as $host) {
                    $pp->removeHost($host);
                }
                foreach ($page->getCompanies() as $host) {
                    $pp->addHost($host);
                }
                $this->em->persist($pp);
            }
            $this->em->flush();
        }

        //copy tags to all translations
        if($page instanceof Taggable){
            $translations = $nodeEvent->getNode()->getNodeTranslations(true);

            /** @var TagRepository $repo */
            $repo = $this->em->getRepository('KunstmaanTaggingBundle:Tag');

            foreach ($translations as $translation) {
                if($originalLanguage == $translation->getLang()) continue;
                /** @var Taggable $pp */
                $pp = $translation->getRef($this->em);

                $repo->copyTags($page, $pp);
            }
        }

        //copy common fields to all translations
        if($page instanceof ICopyFields){
            /** @var $page ICopyFields */
            $translations = $nodeEvent->getNode()->getNodeTranslations(true);

            foreach ($translations as $translation) {
                if($originalLanguage == $translation->getLang()) continue;

                /** @var ICopyFields $pp */
                $pp = $translation->getRef($this->em);

                $pp->setDate($page->getDate());
                $pp->setImage($page->getImage());
                $pp->setTopImage($page->getTopImage());
                $pp->setPriceFromLabel($page->getPriceFromLabel());

                $this->em->persist($pp);
            }
            $this->em->flush();
        }

        if($page instanceof ArticlePage){
            /** @var $page ArticlePage */
            $translations = $nodeEvent->getNode()->getNodeTranslations(true);

            foreach ($translations as $translation) {
                if($originalLanguage == $translation->getLang()) continue;

                /** @var ArticlePage $pp */
                $pp = $translation->getRef($this->em);

                $pp->setImgSize($page->getImgSize());
                $pp->setImageOnlyOnPreview($page->isImageOnlyOnPreview());

                $this->em->persist($pp);
            }
            $this->em->flush();
        }

        if($page instanceof NewsPage){
            /** @var $page NewsPage */
            $translations = $nodeEvent->getNode()->getNodeTranslations(true);

            foreach ($translations as $translation) {
                if($originalLanguage == $translation->getLang()) continue;

                /** @var NewsPage $pp */
                $pp = $translation->getRef($this->em);

                $pp->setDateUntil($page->getDateUntil());
                $pp->setImgSize($page->getImgSize());

                $this->em->persist($pp);
            }
            $this->em->flush();
        }


        //copy hotel fields
        if($page instanceof HotelPage){
            /** @var $page HotelPage */
            $translations = $nodeEvent->getNode()->getNodeTranslations(true);

            foreach ($translations as $translation) {
                if($originalLanguage == $translation->getLang()) continue;

                /** @var HotelPage $pp */
                $pp = $translation->getRef($this->em);

                $pp->setWww($page->getWww());
                $pp->setMapCategory($page->getMapCategory());
                $pp->setLatitude($page->getLatitude());
                $pp->setLongitude($page->getLongitude());
                $pp->setCity($page->getCity());
                $pp->setCityParish($page->getCityParish());
                $pp->setMainPhoto($page->getMainPhoto());
                $pp->setStreet($page->getStreet());
                $pp->setBuildingPhoto($page->getBuildingPhoto());
                $pp->setCountry($page->getCountry());
                $pp->setShowOnMap($page->isShowOnMap());

                $this->em->persist($pp);
            }
            $this->em->flush();

            $this->setCountryPlaces($originalLanguage, $page);
        }

        //copy package fields
        if($page instanceof PackagePage){
            /** @var $page PackagePage */
            $translations = $nodeEvent->getNode()->getNodeTranslations(true);

            foreach ($translations as $translation) {
                if($originalLanguage == $translation->getLang()) continue;

                /** @var PackagePage $pp */
                $pp = $translation->getRef($this->em);

                $pp->setOrderNumber($page->getOrderNumber());
                $pp->setMapCategory($page->getMapCategory());

                $this->em->persist($pp);
            }
            $this->em->flush();
        }


        //copy company fields
        if($page instanceof CompanyOverviewPage){
            /** @var $page CompanyOverviewPage */
            $translations = $nodeEvent->getNode()->getNodeTranslations(true);

            foreach ($translations as $translation) {
                if($originalLanguage == $translation->getLang()) continue;

                /** @var CompanyOverviewPage $pp */
                $pp = $translation->getRef($this->em);

                $pp->setLogo($page->getLogo());
                $pp->setLogoAltText($page->getLogoAltText());
                $pp->setLinkUrl($page->getLinkUrl());
                $pp->setAffiliateLink($page->getAffiliateLink());
                $pp->setCashBackValue($page->getCashBackValue());
                $pp->setCompanyId($page->getCompanyId());
                $pp->setLongitude($page->getLongitude());
                $pp->setLatitude($page->getLatitude());
                $pp->setAddress($page->getAddress());
                $pp->setMapCategory($page->getMapCategory());
                $pp->setPhoto($page->getPhoto());

                $this->em->persist($pp);
            }
            $this->em->flush();
        }

    }

    private function copyHostsToChildren(PlaceOverviewPage $page, Node $node)
    {
        $translations = $node->getNodeTranslations(true);

        //save hosts to add
        $hosts = [];
        foreach ($page->getHosts() as $host) {
            $hosts[] = $host;
        }


        //copy host to all translations
        foreach ($translations as $translation) {

            /** @var PlaceOverviewPage $pp */
            $pp = $translation->getRef($this->em);

            if($pp instanceof PlaceOverviewPage){
                foreach ($pp->getHosts() as $host) {
                    $pp->removeHost($host);
                }

                foreach ($hosts as $host) {
                    $pp->addHost($host);
                }

                $this->em->persist($pp);
            }

        }

        foreach ($node->getChildren() as $child) {
            $this->copyHostsToChildren($page, $child);
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



    private function setCountryPlaces($originalLanguage, HotelPage $page)
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
            /** @var HotelPage $hotelPage */
            $hotelPage = $nodeTranslation->getPublicNodeVersion()->getRef($this->em);

            //add selected places to other translations
            if($page->getCountryPlace()){
                /** @var NodeVersion $nodeVersion */
                $nodeVersion = $this->em->getRepository('KunstmaanNodeBundle:NodeVersion')
                    ->findOneBy([
                        'refId' => $page->getCountryPlace()->getId(),
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
                $hotelPage->setCountryPlace($placePage);
            }

            $this->em->persist($hotelPage);
        }
        $this->em->flush();
    }
} 