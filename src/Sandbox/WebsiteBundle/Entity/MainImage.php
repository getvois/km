<?php

namespace Sandbox\WebsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\AdminBundle\Entity\AbstractEntity;
use Kunstmaan\MediaBundle\Entity\Media;

/**
 * MainImage
 *
 * @ORM\Table(name="sb_header_image")
 * @ORM\Entity
 */
class MainImage extends AbstractEntity
{
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
     * @var string
     *
     * @ORM\Column(name="external", type="string", length=255, nullable=true)
     */
    private $external;

    /**
     * @var string
     *
     * @ORM\Column(name="place_url", type="string", length=255, nullable=true)
     */
    private $placeUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * Set picture
     *
     * @param Media $picture
     * @return MainImage
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
     * Set external
     *
     * @param string $external
     * @return MainImage
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
     * Set placeUrl
     *
     * @param string $placeUrl
     * @return MainImage
     */
    public function setPlaceUrl($placeUrl)
    {
        $this->placeUrl = $placeUrl;

        return $this;
    }

    /**
     * Get placeUrl
     *
     * @return string 
     */
    public function getPlaceUrl()
    {
        return $this->placeUrl;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return MainImage
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
}
