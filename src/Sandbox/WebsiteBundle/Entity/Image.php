<?php

namespace Sandbox\WebsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\AdminBundle\Entity\AbstractEntity;
use Kunstmaan\MediaBundle\Entity\Media;
use Sandbox\WebsiteBundle\Entity\PageParts\GalleryPagePart;

/**
 * Image
 *
 * @ORM\Table(name="sb_image")
 * @ORM\Entity
 */
class Image extends AbstractEntity
{

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
     * @ORM\ManyToOne(targetEntity="Sandbox\WebsiteBundle\Entity\PageParts\GalleryPagePart", inversedBy="images")
     **/
    private $galleryPagePart;

    /**
     * @param GalleryPagePart $galleryPagePart
     */
    public function setGalleryPagePart(GalleryPagePart $galleryPagePart)
    {
        $this->galleryPagePart = $galleryPagePart;
    }

    /**
     * @return GalleryPagePart
     */
    public function getGalleryPagePart()
    {
        return $this->galleryPagePart;
    }



    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

//    /**
//     * @var string
//     *
//     * @ORM\Column(name="copyright", type="string", length=255, nullable=true)
//     */
//    private $copyright;
//
//    /**
//     * @var string
//     *
//     * @ORM\Column(name="copyright_url", type="string", length=255, nullable=true)
//     */
//    private $copyrightUrl;

//    /**
//     * @var string
//     *
//     * @ORM\Column(name="class", type="string", length=255, nullable=true)
//     */
//    private $class;

    /**
     * @var string
     *
     * @ORM\Column(name="wrapper_class", type="string", length=255, nullable=true)
     */
    private $wrapperClass;


    /**
     * Set title
     *
     * @param string $title
     * @return Image
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

//    /**
//     * Set copyright
//     *
//     * @param string $copyright
//     * @return Image
//     */
//    public function setCopyright($copyright)
//    {
//        $this->copyright = $copyright;
//
//        return $this;
//    }
//
//    /**
//     * @return string
//     */
//    public function getCopyrightUrl()
//    {
//        return $this->copyrightUrl;
//    }
//
//    /**
//     * @param string $copyrightUrl
//     * @return $this
//     */
//    public function setCopyrightUrl($copyrightUrl)
//    {
//        $this->copyrightUrl = $copyrightUrl;
//        return $this;
//    }
//
//    /**
//     * Get copyright
//     *
//     * @return string
//     */
//    public function getCopyright()
//    {
//        return $this->copyright;
//    }

//    /**
//     * Set class
//     *
//     * @param string $class
//     * @return Image
//     */
//    public function setClass($class)
//    {
//        $this->class = $class;
//
//        return $this;
//    }
//
//    /**
//     * Get class
//     *
//     * @return string
//     */
//    public function getClass()
//    {
//        return $this->class;
//    }

    /**
     * Set class
     *
     * @param string $class
     * @return Image
     */
    public function setWrapperClass($class)
    {
        $this->wrapperClass = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return string
     */
    public function getWrapperClass()
    {
        return $this->wrapperClass;
    }
}
