<?php

namespace Sandbox\WebsiteBundle\Entity\Article;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DoctrineExtensions\Taggable\Doctrine;
use Kunstmaan\ArticleBundle\Entity\AbstractArticlePage;
use Kunstmaan\TaggingBundle\Entity\Taggable;
use Sandbox\WebsiteBundle\Entity\Article\ArticleAuthor;
use Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage;
use Sandbox\WebsiteBundle\Entity\Host;
use Sandbox\WebsiteBundle\Entity\ICompany;
use Sandbox\WebsiteBundle\Entity\IHostable;
use Sandbox\WebsiteBundle\Entity\IPlaceFromTo;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Sandbox\WebsiteBundle\Entity\Place\PlacePage;
use Sandbox\WebsiteBundle\Entity\TopImage;
use Sandbox\WebsiteBundle\Form\Article\ArticlePageAdminType;
use Sandbox\WebsiteBundle\PagePartAdmin\Article\ArticlePagePagePartAdminConfigurator;
use Symfony\Component\Form\AbstractType;

/**
 * @ORM\Entity(repositoryClass="Sandbox\WebsiteBundle\Repository\Article\ArticlePageRepository")
 * @ORM\Table(name="sb_article_pages")
 * @ORM\HasLifecycleCallbacks
 */
class ArticlePage extends AbstractArticlePage implements IPlaceFromTo, IHostable, Taggable, ICompany
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->places = new ArrayCollection();
        $this->fromPlaces = new ArrayCollection();
        $this->hosts = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->companies = new ArrayCollection();
    }

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage", inversedBy="articles")
     * @ORM\JoinTable(name="companies_articles")
     */
    private $companies;

    /**
     * Add companies
     *
     * @param CompanyOverviewPage $companies
     * @return ArticlePage
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
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Host", inversedBy="articles")
     * @ORM\JoinTable(name="sb_host_article")
     **/
    private $hosts;

    /**
     * @var TopImage
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\WebsiteBundle\Entity\TopImage")
     */
    private $topImage;

    /**
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage", inversedBy="fromArticles")
     * @ORM\JoinTable(name="sb_article_from_place_overview")
     **/
    private $fromPlaces;

    /**
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage", inversedBy="articles")
     * @ORM\JoinTable(name="sb_article_place_overview")
     **/
    private $places;


    /**
     * @var ArticleAuthor
     *
     * @ORM\ManyToOne(targetEntity="ArticleAuthor")
     * @ORM\JoinColumn(name="article_author_id", referencedColumnName="id")
     */
    protected $author;

    /**
     * Returns the default backend form type for this page
     *
     * @return AbstractType
     */
    public function getDefaultAdminType()
    {
        return new ArticlePageAdminType();
    }

    /**
     * @return array
     */
    public function getPagePartAdminConfigurations()
    {
        return array(new ArticlePagePagePartAdminConfigurator());
    }

    public function setAuthor($author)
    {
        $this->author = $author;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getDefaultView()
    {
        return 'SandboxWebsiteBundle:Article/ArticlePage:view.html.twig';
    }

    /**
     * Before persisting this entity, check the date.
     * When no date is present, fill in current date and time.
     *
     * @ORM\PrePersist
     */
    public function _prePersist()
    {
        // Set date to now when none is set
        if ($this->date == null) {
            $this->setDate(new \DateTime());
        }
    }
    /**
     * Add children
     *
     * @param PlacePage $children
     * @return PlacePage
     */
    public function addChild(PlacePage $children)
    {
        $this->places[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param PlacePage $children
     */
    public function removeChild(PlacePage $children)
    {
        $this->places->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPlaces()
    {
        return $this->places;
    }

    /**
     * Add places
     *
     * @param PlaceOverviewPage $places
     * @return ArticlePage
     */
    public function addPlace(PlaceOverviewPage $places)
    {
        $this->places[] = $places;

        return $this;
    }

    /**
     * Remove places
     *
     * @param PlaceOverviewPage $places
     */
    public function removePlace(PlaceOverviewPage $places)
    {
        $this->places->removeElement($places);
    }

    /**
     * Add fromPlaces
     *
     * @param PlaceOverviewPage $fromPlaces
     * @return ArticlePage
     */
    public function addFromPlace(PlaceOverviewPage $fromPlaces)
    {
        $this->fromPlaces[] = $fromPlaces;

        return $this;
    }

    /**
     * Remove fromPlaces
     *
     * @param PlaceOverviewPage $fromPlaces
     */
    public function removeFromPlace(PlaceOverviewPage $fromPlaces)
    {
        $this->fromPlaces->removeElement($fromPlaces);
    }

    /**
     * Get fromPlaces
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFromPlaces()
    {
        return $this->fromPlaces;
    }

    /**
     * Remove all places
     */
    public function removeAllPlaces()
    {
        $this->places->clear();
    }
    /**
     * Remove all from places
     */
    public function removeAllFromPlaces()
    {
        $this->fromPlaces->clear();
    }

    /**
     * Get full entity name
     * @return string
     */
    public function getEntityName()
    {
        return 'Sandbox\WebsiteBundle\Entity\Article\ArticlePage';
    }

    /**
     * Set topImage
     *
     * @param TopImage $topImage
     * @return ArticlePage
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
     * @return ArticlePage
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


    /**
     * Returns the unique taggable resource type
     *
     * @return string
     */
    function getTaggableType()
    {
        return $this->getEntityName();
    }

    /**
     * Returns the unique taggable resource identifier
     *
     * @return string
     */
    function getTaggableId()
    {
        return $this->getId();
    }


    protected $tags;
    /**
     * Returns the collection of tags for this Taggable entity
     *
     * @return Collection
     */
    function getTags()
    {
        $this->tags = $this->tags ?: new ArrayCollection();
        return $this->tags;
    }

    public function setTags($tags)
    {
        $this->tags = $tags;
    }
}
