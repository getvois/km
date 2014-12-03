<?php

namespace Sandbox\WebsiteBundle\Entity\PageParts;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\MediaBundle\Entity\Media;
use Kunstmaan\PagePartBundle\Entity\AbstractPagePart;
use Sandbox\WebsiteBundle\Form\PageParts\ImageThumbnailPagePartAdminType;

/**
 * ImageThumbnailPagePart
 *
 * @ORM\Table(name="sb_image_thumbnail_page_parts")
 * @ORM\Entity
 */
class ImageThumbnailPagePart extends AbstractPagePart
{

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

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
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
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
     * @return ImageThumbnailPagePart
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
     * @ORM\Column(name="link_url", type="string", nullable=true)
     */
    private $linkUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="link_text", type="string", nullable=true)
     */
    private $linkText;

    /**
     * @var boolean
     *
     * @ORM\Column(name="link_new_window", type="boolean", nullable=true)
     */
    private $linkNewWindow;


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
     * @return ImageThumbnailPagePart
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
     * @return ImageThumbnailPagePart
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
     * @return ImageThumbnailPagePart
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
     * Set linkUrl
     *
     * @param string $linkUrl
     * @return ImageThumbnailPagePart
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
     * Set linkText
     *
     * @param string $linkText
     * @return ImageThumbnailPagePart
     */
    public function setLinkText($linkText)
    {
        $this->linkText = $linkText;

        return $this;
    }

    /**
     * Get linkText
     *
     * @return string 
     */
    public function getLinkText()
    {
        return $this->linkText;
    }

    /**
     * Set linkNewWindow
     *
     * @param boolean $linkNewWindow
     * @return ImageThumbnailPagePart
     */
    public function setLinkNewWindow($linkNewWindow)
    {
        $this->linkNewWindow = $linkNewWindow;

        return $this;
    }

    /**
     * Get linkNewWindow
     *
     * @return boolean 
     */
    public function getLinkNewWindow()
    {
        return $this->linkNewWindow;
    }

    /**
     * Set image
     *
     * @param Media $image
     * @return ImageThumbnailPagePart
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
     * Get the twig view.
     *
     * @return string
     */
    public function getDefaultView()
    {
        return 'SandboxWebsiteBundle:PageParts:ImageThumbnailPagePart/view.html.twig';
    }

    /**
     * Get the admin form type.
     *
     * @return ImageThumbnailPagePartAdminType
     */
    public function getDefaultAdminType()
    {
        return new ImageThumbnailPagePartAdminType();
    }
}