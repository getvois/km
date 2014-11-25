<?php

namespace Sandbox\WebsiteBundle\Entity;

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
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage")
     */
    private $place;


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


    /**
     * Set place
     *
     * @param Place\PlaceOverviewPage $place
     * @return TopImage
     */
    public function setPlace(Place\PlaceOverviewPage $place = null)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return Place\PlaceOverviewPage
     */
    public function getPlace()
    {
        return $this->place;
    }

    public function __toString()
    {
        return $this->title;
    }


}
