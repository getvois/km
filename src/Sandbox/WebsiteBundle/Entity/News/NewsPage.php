<?php

namespace Sandbox\WebsiteBundle\Entity\News;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DoctrineExtensions\Taggable\Doctrine;
use Kunstmaan\ArticleBundle\Entity\AbstractArticlePage;
use Kunstmaan\TaggingBundle\Entity\Taggable;
use Sandbox\WebsiteBundle\Entity\Host;
use Sandbox\WebsiteBundle\Entity\IHostable;
use Sandbox\WebsiteBundle\Entity\IPlaceFromTo;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Sandbox\WebsiteBundle\Entity\Place\PlacePage;
use Sandbox\WebsiteBundle\Entity\TopImage;
use Sandbox\WebsiteBundle\Form\News\NewsPageAdminType;
use Sandbox\WebsiteBundle\PagePartAdmin\News\NewsPagePagePartAdminConfigurator;
use Symfony\Component\Form\AbstractType;

/**
 * @ORM\Entity(repositoryClass="Sandbox\WebsiteBundle\Repository\News\NewsPageRepository")
 * @ORM\Table(name="sb_news_pages")
 * @ORM\HasLifecycleCallbacks
 */
class NewsPage extends AbstractArticlePage implements IPlaceFromTo, IHostable, Taggable
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

    }


    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Host", inversedBy="news")
     * @ORM\JoinTable(name="sb_host_news")
     **/
    private $hosts;



    /**
     * @var TopImage
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\WebsiteBundle\Entity\TopImage")
     */
    private $topImage;

    /**
     * @var boolean
     *
     * @ORM\Column(name="translate", type="boolean", nullable=true)
     */
    private $translate;

    /**
     * @return boolean
     */
    public function isTranslate()
    {
        return $this->translate;
    }

    /**
     * @param boolean $translate
     *
     * @return $this NewsPage
     */
    public function setTranslate($translate)
    {
        $this->translate = $translate;

        return $this;
    }

    /**
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage", inversedBy="fromNews")
     * @ORM\JoinTable(name="sb_news_from_place_overview")
     **/
    private $fromPlaces;


    /**
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage", inversedBy="news")
     * @ORM\JoinTable(name="sb_news_place_overview")
     **/
    private $places;

    /**
     * @var NewsAuthor
     *
     * @ORM\ManyToOne(targetEntity="NewsAuthor")
     * @ORM\JoinColumn(name="news_author_id", referencedColumnName="id")
     */
    protected $author;

    /**
     * Returns the default backend form type for this page
     *
     * @return AbstractType
     */
    public function getDefaultAdminType()
    {
        return new NewsPageAdminType();
    }

    /**
     * @return array
     */
    public function getPagePartAdminConfigurations()
    {
        return array(new NewsPagePagePartAdminConfigurator());
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
        return 'SandboxWebsiteBundle:News/NewsPage:view.html.twig';
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
     * Add place
     *
     * @param PlaceOverviewPage $children
     * @return PlaceOverviewPage
     */
    public function addPlace(PlaceOverviewPage $children)
    {
        $this->places[] = $children;

        return $this;
    }

    /**
     * Remove place
     *
     * @param PlaceOverviewPage $children
     */
    public function removePlace(PlaceOverviewPage $children)
    {
        $this->places->removeElement($children);
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
     * Get places
     *
     * @return \Doctrine\Common\Collections\Collection|PlaceOverviewPage[]
     */
    public function getPlaces()
    {
        return $this->places;
    }

    /**
     * Add fromPlaces
     *
     * @param PlaceOverviewPage $fromPlaces
     * @return NewsPage
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
     * Get full entity name
     * @return string
     */
    public function getEntityName()
    {
        return 'Sandbox\WebsiteBundle\Entity\News\NewsPage';
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
     * Get translate
     *
     * @return boolean 
     */
    public function getTranslate()
    {
        return $this->translate;
    }

    /**
     * Add hosts
     *
     * @param Host $hosts
     * @return NewsPage
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
