<?php

namespace Sandbox\WebsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\AdminBundle\Entity\AbstractEntity;
use Kunstmaan\MediaBundle\Entity\Media;

/**
 * GooglePlace
 *
 * @ORM\Table(name="sb_google_place")
 * @ORM\Entity
 */
class GooglePlace extends AbstractEntity
{


    /**
     * Set image
     *
     * @param Media $image
     * @return GooglePlace
     */
    public function setImage(Media $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return Media
     */
    public function getImage()
    {
        return $this->image;
    }


    /**
     * @var string
     *
     * @ORM\Column(name="size", type="string", length=50)
     */
    private $size;

    /**
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param string $size
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @var Media
     *
     * @ORM\ManyToOne(targetEntity="Kunstmaan\MediaBundle\Entity\Media")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="image_id", referencedColumnName="id")
     * })
     */
    private $image;


    /**
     * Set imageAltText
     *
     * @param string $imageAltText
     * @return GooglePlace
     */
    public function setImageAltText($imageAltText)
    {
        $this->imageAltText = $imageAltText;

        return $this;
    }

    /**
     * Get imageAltText
     *
     * @return string
     */
    public function getImageAltText()
    {
        return $this->imageAltText;
    }

    /**
     * Set responsive
     *
     * @param boolean $responsive
     * @return GooglePlace
     */
    public function setResponsive($responsive)
    {
        $this->responsive = $responsive;

        return $this;
    }

    /**
     * Get responsive
     *
     * @return boolean
     */
    public function getResponsive()
    {
        return $this->responsive;
    }

    /**
     * Set alt
     *
     * @param string $alt
     * @return GooglePlace
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;

        return $this;
    }

    /**
     * Get alt
     *
     * @return string
     */
    public function getAlt()
    {
        return $this->alt;
    }


    /**
     * @return string
     */
    public function getWrapperClass()
    {
        return $this->wrapperClass;
    }

    /**
     * @param string $wrapperClass
     * @return $this
     */
    public function setWrapperClass($wrapperClass)
    {
        $this->wrapperClass = $wrapperClass;
        return $this;
    }




    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $content;

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return GooglePlace
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }


    /**
     * @var string
     *
     * @ORM\Column(name="image_alt_text", type="text", nullable=true)
     */
    private $imageAltText;

    /**
     * @var boolean
     *
     * @ORM\Column(name="responsive", type="boolean", nullable=true)
     */
    private $responsive;

    /**
     * @var string
     *
     * @ORM\Column(name="alt", type="string", length=255, nullable=true)
     */
    private $alt;


    /**
     * @var string
     *
     * @ORM\Column(name="wrapper_class", type="string", length=255, nullable=true)
     */
    private $wrapperClass;

    /**
     * @var string
     *
     * @ORM\Column(name="img_class", type="string", length=255, nullable=true)
     */
    private $imgClass;

    /**
     * @return string
     */
    public function getImgClass()
    {
        return $this->imgClass;
    }

    /**
     * @param string $imgClass
     * @return $this
     */
    public function setImgClass($imgClass)
    {
        $this->imgClass = $imgClass;
        return $this;
    }




    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\WebsiteBundle\Entity\PageParts\GoogleMapPagePart", inversedBy="places")
     **/
    private $map;

    /**
     * @return mixed
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * @param mixed $contactPagePart
     * @return $this
     */
    public function setMap($contactPagePart)
    {
        $this->map = $contactPagePart;
        return $this;
    }



    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="string", length=255)
     */
    private $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="string", length=255)
     */
    private $longitude;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    private $url;


    /**
     * Set title
     *
     * @param string $title
     * @return GooglePlace
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
     * Set type
     *
     * @param string $type
     * @return GooglePlace
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     * @return GooglePlace
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
     * @return GooglePlace
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
     * Set description
     *
     * @param string $description
     * @return GooglePlace
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
     * Set url
     *
     * @param string $url
     * @return GooglePlace
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    public function __toString()
    {
        if($this->title)
            return $this->title;

        return $this->getId();
    }


}
