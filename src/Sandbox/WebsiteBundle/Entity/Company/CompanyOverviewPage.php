<?php

namespace Sandbox\WebsiteBundle\Entity\Company;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\MediaBundle\Entity\Media;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\NodeBundle\Entity\NodeVersion;
use Sandbox\WebsiteBundle\Entity\Article\ArticlePage;
use Sandbox\WebsiteBundle\Entity\Host;
use Sandbox\WebsiteBundle\Entity\MapCategory;
use Sandbox\WebsiteBundle\Entity\News\NewsPage;
use Sandbox\WebsiteBundle\Entity\Pages\OfferPage;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Sandbox\WebsiteBundle\Entity\PreferredTag;
use Sandbox\WebsiteBundle\Form\Company\CompanyOverviewPageAdminType;
use Sandbox\WebsiteBundle\PagePartAdmin\Company\CompanyOverviewPagePagePartAdminConfigurator;
use Kunstmaan\ArticleBundle\Entity\AbstractArticleOverviewPage;
use Kunstmaan\NodeBundle\Helper\RenderContext;
use Kunstmaan\PagePartBundle\PagePartAdmin\AbstractPagePartAdminConfigurator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Request;

/**
 * The article overview page which shows its articles
 *
 * @ORM\Entity(repositoryClass="Sandbox\WebsiteBundle\Repository\Company\CompanyOverviewPageRepository")
 * @ORM\Table(name="sb_company_overviewpages")
 */
class CompanyOverviewPage extends AbstractArticleOverviewPage
{

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=512, nullable=true)
     */
    private $address;

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @var MapCategory
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\WebsiteBundle\Entity\MapCategory")
     */
    private $mapCategory;

    /**
     * @return MapCategory
     */
    public function getMapCategory()
    {
        return $this->mapCategory;
    }

    /**
     * @param MapCategory $mapCategory
     */
    public function setMapCategory($mapCategory)
    {
        $this->mapCategory = $mapCategory;
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
     * @return OfferPage
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
     * @return OfferPage
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
     * @ORM\Column(name="affiliate_link", type="string", length=255, nullable=true)
     */
    private $affiliateLink;

    /**
     * @var float
     *
     * @ORM\Column(name="cash_back_value", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $cashBackValue;

    /**
     * @var float
     *
     * @ORM\Column(name="cash_back_type", type="string", length=3, nullable=true)
     */
    private $cashBackType;

    /**
     * @return string
     */
    public function getAffiliateLink()
    {
        return $this->affiliateLink;
    }

    /**
     * @param string $affiliateLink
     * @return $this
     */
    public function setAffiliateLink($affiliateLink)
    {
        $this->affiliateLink = $affiliateLink;
        return $this;
    }

    /**
     * @return float
     */
    public function getCashBackType()
    {
        return $this->cashBackType;
    }

    /**
     * @param float $cashBackType
     * @return $this
     */
    public function setCashBackType($cashBackType)
    {
        $this->cashBackType = $cashBackType;
        return $this;
    }

    /**
     * @return float
     */
    public function getCashBackValue()
    {
        return $this->cashBackValue;
    }

    /**
     * @param float $cashBackValue
     * @return $this
     */
    public function setCashBackValue($cashBackValue)
    {
        $this->cashBackValue = $cashBackValue;
        return $this;
    }



    /**
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Article\ArticlePage", mappedBy="companies")
     **/
    private $articles;
    /**
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\News\NewsPage", mappedBy="companies")
     **/
    private $news;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage", inversedBy="companies")
     * @ORM\JoinTable(name="companies_places")
     */
    private $places;

    /**
     * @param $place
     * @return $this
     */
    public function addPlace($place)
    {
        $this->places->add($place);
        return $this;
    }

    /**
     * @param $place
     * @return $this
     */
    public function removePlace($place)
    {
        $this->places->removeElement($place);
        return $this;
    }
    /**
     * @return PlaceOverviewPage[]
     */
    public function getPlaces()
    {
        return $this->places;
    }

    /**
     * @param PlaceOverviewPage $place
     * @return $this
     */
    public function setPlaces($place)
    {
        $this->places = $place;
        return $this;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="logo_alt_text", type="text", nullable=true)
     */
    private $logoAltText;

    /**
     * @var string
     *
     * @ORM\Column(name="link_url", type="string", nullable=true)
     */
    private $linkUrl;

    /**
     * @var Media
     *
     * @ORM\ManyToOne(targetEntity="Kunstmaan\MediaBundle\Entity\Media")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="logo_id", referencedColumnName="id")
     * })
     */
    private $logo;


    /**
     * Set description
     *
     * @param string $description
     * @return CompanyPage
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set logoAltText
     *
     * @param string $logoAltText
     * @return CompanyPage
     */
    public function setLogoAltText($logoAltText)
    {
        $this->logoAltText = $logoAltText;

        return $this;
    }

    /**
     * Get logoAltText
     *
     * @return string
     */
    public function getLogoAltText()
    {
        return $this->logoAltText;
    }

    /**
     * Set linkUrl
     *
     * @param string $linkUrl
     * @return CompanyPage
     */
    public function setLinkUrl($linkUrl)
    {
        $this->linkUrl = $linkUrl;

        return $this;
    }

    /**
     * Get linkUrl
     *
     * @return string
     */
    public function getLinkUrl()
    {
        return $this->linkUrl;
    }

    /**
     * Set logo
     *
     * @param Media $logo
     * @return CompanyPage
     */
    public function setLogo(Media $logo = null)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo
     *
     * @return Media
     */
    public function getLogo()
    {
        return $this->logo;
    }
    /**
     * @var integer
     *
     * @ORM\Column(name="company_id", type="integer", nullable=true)
     */
    private $companyId;

    /**
     * @return int
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * @param int $companyId
     * @return $this
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
        return $this;
    }

    /**
     * @return AbstractPagePartAdminConfigurator[]
     */
    public function getPagePartAdminConfigurations()
    {
        return array(new CompanyOverviewPagePagePartAdminConfigurator());
    }

    public function getSubNews(Node $node, $locale,ObjectManager $em, &$news = [], $host)
    {
        $nodeTranslation = $node->getNodeTranslation($locale);
        if($nodeTranslation && $nodeTranslation->isOnline()){
            /** @var PlaceOverviewPage $placeOverviewPage */
            $placeOverviewPage = $nodeTranslation->getPublicNodeVersion()->getRef($em);
            /** @var NewsPage $item */
            foreach ($placeOverviewPage->getNews() as $item) {

                //get node version
                /** @var NodeVersion $nodeVersion */
                $nodeVersion = $em->getRepository('KunstmaanNodeBundle:NodeVersion')->getNodeVersionFor($item);
                $nodeVersion = $nodeVersion->getNodeTranslation()->getPublicNodeVersion();
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
        $nodeTranslation = $node->getNodeTranslation($locale);
        if($nodeTranslation && $nodeTranslation->isOnline()){
            /** @var PlaceOverviewPage $placeOverviewPage */
            $placeOverviewPage = $nodeTranslation->getPublicNodeVersion()->getRef($em);
            /** @var NewsPage $item */
            foreach ($placeOverviewPage->getArticles() as $item) {
                //get node version
                /** @var NodeVersion $nodeVersion */
                $nodeVersion = $em->getRepository('KunstmaanNodeBundle:NodeVersion')->getNodeVersionFor($item);
                $nodeVersion = $nodeVersion->getNodeTranslation()->getPublicNodeVersion();
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
                unset($preferredTags[$index]);
            }
        }

        $context['preferredtags'] = $preferredTags;
        $context['tags'] = $tags;
        $context['places'] = $placesLocale;
        $context['news'] = $news;
        $context['articles'] = $articles;
        $context['lang'] = $locale;
        $context['em'] = $em;

        parent::service($container, $request, $context);
    }

    public function getArticleRepository($em)
    {
        return $em->getRepository('SandboxWebsiteBundle:Company\CompanyPage');
    }

    /**
     * @return string
     */
    public function getDefaultView()
    {
        return 'SandboxWebsiteBundle:Company/CompanyOverviewPage:view.html.twig';
    }

    /**
     * Returns the default backend form type for this page
     *
     * @return AbstractType
     */
    public function getDefaultAdminType()
    {
        return new CompanyOverviewPageAdminType();
    }

    /**
     * @return array
     */
    public function getPossibleChildTypes()
    {
        return array();
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->places = new ArrayCollection();
    }

    /**
     * Add articles
     *
     * @param ArticlePage $articles
     * @return CompanyOverviewPage
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
     * Get articles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * Add news
     *
     * @param NewsPage $news
     * @return CompanyOverviewPage
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
}
