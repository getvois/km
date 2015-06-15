<?php

namespace Sandbox\WebsiteBundle\Entity\Place;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\NodeBundle\Controller\SlugActionInterface;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\NodeBundle\Entity\NodeVersion;
use Sandbox\WebsiteBundle\Entity\Article\ArticlePage;
use Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage;
use Sandbox\WebsiteBundle\Entity\Host;
use Sandbox\WebsiteBundle\Entity\ICompany;
use Sandbox\WebsiteBundle\Entity\IHostable;
use Sandbox\WebsiteBundle\Entity\News\NewsPage;
use Sandbox\WebsiteBundle\Entity\PreferredTag;
use Sandbox\WebsiteBundle\Entity\TopImage;
use Sandbox\WebsiteBundle\Form\Place\PlaceOverviewPageAdminType;
use Sandbox\WebsiteBundle\PagePartAdmin\Place\PlaceOverviewPagePagePartAdminConfigurator;
use Kunstmaan\ArticleBundle\Entity\AbstractArticleOverviewPage;
use Kunstmaan\NodeBundle\Helper\RenderContext;
use Kunstmaan\PagePartBundle\PagePartAdmin\AbstractPagePartAdminConfigurator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Request;

/**
 * The article overview page which shows its articles
 *
 * @ORM\Entity(repositoryClass="Sandbox\WebsiteBundle\Repository\Place\PlaceOverviewPageRepository")
 * @ORM\Table(name="sb_place_overviewpages")
 */
class PlaceOverviewPage extends AbstractArticleOverviewPage implements IHostable, ICompany, SlugActionInterface
{

    /**
     * @var string
     *
     * @ORM\Column(name="iata", type="string", nullable=true)
     */
    private $iata;

    /**
     * @return string
     */
    public function getIata()
    {
        return $this->iata;
    }

    /**
     * @param string $iata
     */
    public function setIata($iata)
    {
        $this->iata = $iata;
    }

    /**
     * @return bool
     */
    public function hasCoordinates()
    {
        if($this->getLatitude() && $this->getLongitude())
            return true;
        return false;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="string", length=255, nullable=true)
     */
    private $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="string", length=255, nullable=true)
     */
    private $longitude;

    /**
     * Set latitude
     *
     * @param string $latitude
     * @return PlaceOverviewPage
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     * @return PlaceOverviewPage
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }


    /**
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage", mappedBy="places")
     */
    private $companies;

    /**
     * Add companies
     *
     * @param CompanyOverviewPage $companies
     * @return NewsPage
     */
    public function addCompany(CompanyOverviewPage $companies)
    {
        $this->companies[] = $companies;

        return $this;
    }

    /**
     * Remove companies
     *
     * @param CompanyOverviewPage $companies
     */
    public function removeCompany(CompanyOverviewPage $companies)
    {
        $this->companies->removeElement($companies);
    }

    /**
     * Get companies
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCompanies()
    {
        return $this->companies;
    }


    /**
     * @var TopImage
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\WebsiteBundle\Entity\TopImage")
     */
    private $topImage;

    /**
     * @ORM\OneToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage", mappedBy="parentPlace")
     **/
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage", inversedBy="children")
     **/
    private $parentPlace;


    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Host", inversedBy="places")
     * @ORM\JoinTable(name="sb_host_place")
     **/
    private $hosts;


    /**
     * @var string
     *
     * @ORM\Column(name="country_code", type="string", length=2, nullable=true)
     */
    private $countryCode;

    /**
     * @var int
     *
     * @ORM\Column(name="city_id", type="integer", nullable=true)
     */
    private $cityId;

    /**
     * @return int
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * @param int $cityId
     * @return $this
     */
    public function setCityId($cityId)
    {
        $this->cityId = $cityId;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     * @return $this
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
        return $this;
    }


    /**
     *
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\News\NewsPage", mappedBy="places")
     */
    private $news;

    /**
     *
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\News\NewsPage", mappedBy="fromPlaces")
     */
    private $fromNews;

    /**
     *
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Article\ArticlePage", mappedBy="places")
     */
    private $articles;

    /**
     *
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Article\ArticlePage", mappedBy="fromPlaces")
     */
    private $fromArticles;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->fromNews = new ArrayCollection();
        $this->news = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->fromArticles = new ArrayCollection();
        $this->hosts = new ArrayCollection();
        $this->companies = new ArrayCollection();
    }

    /**
     * @return AbstractPagePartAdminConfigurator[]
     */
    public function getPagePartAdminConfigurations()
    {
        return array(new PlaceOverviewPagePagePartAdminConfigurator());
    }

    public function getControllerAction()
    {
        return "SandboxWebsiteBundle:PlaceOverviewPage:service";
    }

    public function getSubNews(Node $node, $locale,ObjectManager $em, &$news = [], $host)
    {
        if($node->getRefEntityName() != 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage')
            return;

        $nodeTranslation = $node->getNodeTranslation($locale);
        if($nodeTranslation && $nodeTranslation->isOnline()){
            /** @var PlaceOverviewPage $placeOverviewPage */
            $placeOverviewPage = $nodeTranslation->getPublicNodeVersion()->getRef($em);
            /** @var NewsPage $item */
            foreach ($placeOverviewPage->getNews() as $item) {

                //get node version
                /** @var NodeVersion $nodeVersion */
                $nodeVersion = $em->getRepository('KunstmaanNodeBundle:NodeVersion')->getNodeVersionFor($item);
                $nodeVersion = ($nodeVersion)?$nodeVersion->getNodeTranslation()->getPublicNodeVersion():null;
                //check node online and lang
                if($nodeVersion
                    && $nodeVersion->getNodeTranslation()->isOnline()
                    && $nodeVersion->getNodeTranslation()->getLang() == $locale
                ){
                    //check host
                    /** @var Host $host */
                    if($host){
                        /** @var Host $itemHost */
                        foreach ($item->getHosts() as $itemHost) {
                            if($itemHost->getId() == $host->getId()){
                                $news[$nodeVersion->getNodeTranslation()->getId()] = $nodeVersion->getRef($em);//$item;
                                break;
                            }
                        }
                    }else {
                        $news[$nodeVersion->getNodeTranslation()->getId()] = $nodeVersion->getRef($em);//$item;
                    }
                }
            }
        }

        foreach ($node->getChildren() as $child) {
            $this->getSubNews($child, $locale, $em, $news, $host);
        }

    }

    public function getSubArticles(Node $node, $locale,ObjectManager $em, &$articles = [], $host)
    {
        if($node->getRefEntityName() != 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage')
            return;

        $nodeTranslation = $node->getNodeTranslation($locale);
        if($nodeTranslation && $nodeTranslation->isOnline()){
            /** @var PlaceOverviewPage $placeOverviewPage */
            $placeOverviewPage = $nodeTranslation->getPublicNodeVersion()->getRef($em);
            /** @var NewsPage $item */
            foreach ($placeOverviewPage->getArticles() as $item) {
                //get node version
                /** @var NodeVersion $nodeVersion */
                $nodeVersion = $em->getRepository('KunstmaanNodeBundle:NodeVersion')->getNodeVersionFor($item);
                $nodeVersion = ($nodeVersion)?$nodeVersion->getNodeTranslation()->getPublicNodeVersion():null;
                //check node online and lang
                if($nodeVersion
                    && $nodeVersion->getNodeTranslation()->isOnline()
                    && $nodeVersion->getNodeTranslation()->getLang() == $locale
                ){
                    //check host
                    /** @var Host $host */
                    if($host){
                        /** @var Host $itemHost */
                        foreach ($item->getHosts() as $itemHost) {
                            if($itemHost->getId() == $host->getId()){
                                $articles[$nodeVersion->getNodeTranslation()->getId()] = $nodeVersion->getRef($em);//$item;
                                break;
                            }
                        }
                    }else {
                        $articles[$nodeVersion->getNodeTranslation()->getId()] = $nodeVersion->getRef($em);//$item;
                    }
                }
            }
        }

        foreach ($node->getChildren() as $child) {
            $this->getSubArticles($child, $locale, $em, $articles, $host);
        }

    }

    /**
     * @param ContainerInterface $container
     * @param Request $request
     * @param RenderContext $context
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|void
     */
    public function service(ContainerInterface $container, Request $request, RenderContext $context)
    {
        parent::service($container, $request, $context);

        $locale = $request->getLocale();//page language code
        $placesLocale = [];//array of translated online nodes to return to template

        /** @var NodeTranslation $nodeTranslation */
        $nodeTranslation = $context->getArrayCopy()['nodetranslation'];
        $nodeChildren = $nodeTranslation->getNode()->getChildren();

        /** @var Node $node */
        foreach ($nodeChildren as $node) {
            $translation = $node->getNodeTranslation($locale);
            if($translation && $translation->isOnline()){
                $placesLocale[] = $translation;
            }
        }

        $em = $container->get('doctrine.orm.entity_manager');
        $host = $em->getRepository('SandboxWebsiteBundle:Host')
            ->findOneBy(['name' => $request->getHost()]);
        $this->getSubNews($nodeTranslation->getNode(), $locale, $em, $news, $host);
        $this->getSubArticles($nodeTranslation->getNode(), $locale, $em, $articles, $host);

        $tags = [];
        if($articles)
        foreach ($articles as $article) {
            foreach ($article->getTags() as $tag) {
                $tags[$tag->getId()] = $tag;
            }
        }
        if($news)
        foreach ($news as $article) {
            foreach ($article->getTags() as $tag) {
                $tags[$tag->getId()] = $tag;
            }
        }

        //preferred tags
        /** @var PreferredTag[] $preferredTags */
        $preferredTags = $em->getRepository('SandboxWebsiteBundle:PreferredTag')
            ->findAll();

        //delete preferred tags from tags
        foreach ($preferredTags as $index => $tag) {
            if(($key = array_search($tag->getTag(), $tags)) !== false){
                unset($tags[$key]);
            }else{
                //unset($preferredTags[$index]);
            }
        }

        $context['preferredtags'] = $preferredTags;
        $context['tags'] = $tags;
        $context['places'] = $placesLocale;
        $context['news'] = $news;
        $context['articles'] = $articles;
        $context['lang'] = $locale;
        $context['em'] = $em;

    }

    public function getArticleRepository($em)
    {
        return $em->getRepository('SandboxWebsiteBundle:Place\PlacePage');
    }

    /**
     * @return string
     */
    public function getDefaultView()
    {
        return 'SandboxWebsiteBundle:Place/PlaceOverviewPage:view.html.twig';
    }

    /**
     * Returns the default backend form type for this page
     *
     * @return AbstractType
     */
    public function getDefaultAdminType()
    {
        return new PlaceOverviewPageAdminType();
    }


    public function getPossibleChildTypes()
    {
        return[
            array(
                'name' => 'Place',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage'
            ),
            array(
                'name' => 'CompanyPlace',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Pages\CompanyPlacePage'
            ),
            array(
                'name' => 'HotelOverviewPage',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Pages\HotelOverviewPage'
            ),];
    }



    /**
     * Add children
     *
     * @param PlacePage $children
     * @return PlacePage
     */
    public function addChild(PlacePage $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param PlacePage $children
     */
    public function removeChild(PlacePage $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parentPlace
     *
     * @param PlacePage $parentPlace
     * @return PlacePage
     */
    public function setParentPlace(PlacePage $parentPlace = null)
    {
        $this->parentPlace = $parentPlace;

        return $this;
    }

    /**
     * Get parentPlace
     *
     * @return PlacePage
     */
    public function getParentPlace()
    {
        return $this->parentPlace;
    }

    /**
     * Add news
     *
     * @param NewsPage $news
     * @return PlacePage
     */
    public function addNews(NewsPage $news)
    {
        $this->news[] = $news;

        return $this;
    }

    /**
     * Remove news
     *
     * @param NewsPage $news
     */
    public function removeNews(NewsPage $news)
    {
        $this->news->removeElement($news);
    }

    /**
     * Get news
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNews()
    {
        return $this->news;
    }


    /**
     * Get articles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * Add fromNews
     *
     * @param NewsPage $fromNews
     * @return PlaceOverviewPage
     */
    public function addFromNews(NewsPage $fromNews)
    {
        $this->fromNews[] = $fromNews;

        return $this;
    }

    /**
     * Remove fromNews
     *
     * @param NewsPage $fromNews
     */
    public function removeFromNews(NewsPage $fromNews)
    {
        $this->fromNews->removeElement($fromNews);
    }

    /**
     * Get fromNews
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFromNews()
    {
        return $this->fromNews;
    }

    /**
     * Add articles
     *
     * @param ArticlePage $articles
     * @return PlaceOverviewPage
     */
    public function addArticle(ArticlePage $articles)
    {
        $this->articles[] = $articles;

        return $this;
    }

    /**
     * Remove articles
     *
     * @param ArticlePage $articles
     */
    public function removeArticle(ArticlePage $articles)
    {
        $this->articles->removeElement($articles);
    }

    /**
     * Add fromArticles
     *
     * @param ArticlePage $fromArticles
     * @return PlaceOverviewPage
     */
    public function addFromArticle(ArticlePage $fromArticles)
    {
        $this->fromArticles[] = $fromArticles;

        return $this;
    }

    /**
     * Remove fromArticles
     *
     * @param ArticlePage $fromArticles
     */
    public function removeFromArticle(ArticlePage $fromArticles)
    {
        $this->fromArticles->removeElement($fromArticles);
    }

    /**
     * Get fromArticles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFromArticles()
    {
        return $this->fromArticles;
    }

    /**
     * Set topImage
     *
     * @param TopImage $topImage
     * @return NewsPage
     */
    public function setTopImage(TopImage $topImage = null)
    {
        $this->topImage = $topImage;

        return $this;
    }

    /**
     * Get topImage
     *
     * @return TopImage
     */
    public function getTopImage()
    {
        return $this->topImage;
    }

    /**
     * Add hosts
     *
     * @param Host $hosts
     * @return PlaceOverviewPage
     */
    public function addHost(Host $hosts)
    {
        $this->hosts[] = $hosts;

        return $this;
    }

    /**
     * Remove hosts
     *
     * @param Host $hosts
     */
    public function removeHost(Host $hosts)
    {
        $this->hosts->removeElement($hosts);
    }

    /**
     * Get hosts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getHosts()
    {
        return $this->hosts;
    }
}
