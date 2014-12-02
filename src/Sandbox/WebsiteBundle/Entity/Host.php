<?php

namespace Sandbox\WebsiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\AdminBundle\Entity\AbstractEntity;

/**
 * Host
 *
 * @ORM\Table(name="sb_host")
 * @ORM\Entity(repositoryClass="Sandbox\WebsiteBundle\Repository\HostRepository")
 */
class Host extends AbstractEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="multi_language", type="boolean", nullable=true)
     */
    private $multiLanguage;

    /**
     * @var string
     *
     * @ORM\Column(name="lang", type="string", length=2, nullable=true)
     */
    private $lang;

    /**
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage", mappedBy="hosts")
     **/
    private $places;
    /**
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage", mappedBy="news")
     **/
    private $news;
    /**
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage", mappedBy="articles")
     **/
    private $articles;

    /**
     * Set name
     *
     * @param string $name
     * @return Host
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set multiLanguage
     *
     * @param boolean $multiLanguage
     * @return Host
     */
    public function setMultiLanguage($multiLanguage)
    {
        $this->multiLanguage = $multiLanguage;

        return $this;
    }

    /**
     * Get multiLanguage
     *
     * @return boolean 
     */
    public function getMultiLanguage()
    {
        return $this->multiLanguage;
    }


    /**
     * Set lang
     *
     * @param string $lang
     * @return Host
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get lang
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->places = new ArrayCollection();
        $this->news = new ArrayCollection();
        $this->articles = new ArrayCollection();
    }

    /**
     * Add places
     *
     * @param Place\PlaceOverviewPage $places
     * @return Host
     */
    public function addPlace(Place\PlaceOverviewPage $places)
    {
        $this->places[] = $places;

        return $this;
    }

    /**
     * Remove places
     *
     * @param Place\PlaceOverviewPage $places
     */
    public function removePlace(Place\PlaceOverviewPage $places)
    {
        $this->places->removeElement($places);
    }

    /**
     * Get places
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPlaces()
    {
        return $this->places;
    }

    public function __toString()
    {
        return $this->name;
    }



    /**
     * Add news
     *
     * @param Place\PlaceOverviewPage $news
     * @return Host
     */
    public function addNews(Place\PlaceOverviewPage $news)
    {
        $this->news[] = $news;

        return $this;
    }

    /**
     * Remove news
     *
     * @param Place\PlaceOverviewPage $news
     */
    public function removeNews(Place\PlaceOverviewPage $news)
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
     * Add articles
     *
     * @param Place\PlaceOverviewPage $articles
     * @return Host
     */
    public function addArticle(Place\PlaceOverviewPage $articles)
    {
        $this->articles[] = $articles;

        return $this;
    }

    /**
     * Remove articles
     *
     * @param Place\PlaceOverviewPage $articles
     */
    public function removeArticle(Place\PlaceOverviewPage $articles)
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
}
