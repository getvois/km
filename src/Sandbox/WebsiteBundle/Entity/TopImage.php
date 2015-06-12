<?php

namespace Sandbox\WebsiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\AdminBundle\Entity\AbstractEntity;
use Kunstmaan\MediaBundle\Entity\Media;

/**
 * TopImage
 *
 * @ORM\Table(name="sb_top_image")
 * @ORM\Entity(repositoryClass="Sandbox\WebsiteBundle\Repository\TopImageRepository")
 */
class TopImage extends AbstractEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="external", type="string", length=255, nullable=true)
     */
    private $external;

    /**
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage")
     **/
    private $places;


    /**
     * @var Media
     *
     * @ORM\ManyToOne(targetEntity="Kunstmaan\MediaBundle\Entity\Media")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="picture_id", referencedColumnName="id")
     * })
     */
    private $picture;

    /**
     * @var bool
     *
     * @ORM\Column(name="visible", type="boolean", nullable=true)
     */
    private $visible;

    /**
     * @return boolean
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * @param boolean $visible
     * @return $this
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Set picture
     *
     * @param Media $picture
     * @return TopImage
     */
    public function setPicture(Media $picture = null)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Get picture
     *
     * @return Media
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return TopImage
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set external
     *
     * @param string $external
     * @return TopImage
     */
    public function setExternal($external)
    {
        $this->external = $external;

        return $this;
    }

    /**
     * Get external
     *
     * @return string 
     */
    public function getExternal()
    {
        return $this->external;
    }

    public function __toString()
    {
        return $this->title;
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->places = new ArrayCollection();
        $this->hosts = new ArrayCollection();
    }

    /**
     * Get visible
     *
     * @return boolean 
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Add places
     *
     * @param Place\PlaceOverviewPage $places
     * @return TopImage
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

    /**
     * @var boolean
     *
     * @ORM\Column(name="dark_image", type="boolean", nullable=true)
     */
    private $darkImage;

    /**
     * @return mixed
     */
    public function getDarkImage()
    {
        return $this->darkImage;
    }

    /**
     * @param mixed $darkImage
     */
    public function setDarkImage($darkImage)
    {
        $this->darkImage = $darkImage;
    }

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Host")
     * @ORM\JoinTable(name="sb_host_topimage")
     **/
    private $hosts;

    /**
     * Add hosts
     *
     * @param Host $hosts
     * @return TopImage
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
