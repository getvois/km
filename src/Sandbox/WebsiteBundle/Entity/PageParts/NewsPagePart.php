<?php

namespace Sandbox\WebsiteBundle\Entity\PageParts;

use Doctrine\ORM\Mapping as ORM;

/**
 * NewsPagePart
 *
 * @ORM\Table(name="sb_news_page_parts")
 * @ORM\Entity
 */
class NewsPagePart extends \Kunstmaan\PagePartBundle\Entity\AbstractPagePart
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
     * @ORM\Column(name="sub_title", type="string", length=255, nullable=true)
     */
    private $subTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="short_desc", type="text", nullable=true)
     */
    private $shortDesc;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text", nullable=true)
     */
    private $text;

    /**
     * @var string
     *
     * @ORM\Column(name="image_alt_text", type="text", nullable=true)
     */
    private $imageAltText;

    /**
     * @var \Kunstmaan\MediaBundle\Entity\Media
     *
     * @ORM\ManyToOne(targetEntity="Kunstmaan\MediaBundle\Entity\Media")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="image_id", referencedColumnName="id")
     * })
     */
    private $image;


    /**
     * Set title
     *
     * @param string $title
     * @return NewsPagePart
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
     * Set subTitle
     *
     * @param string $subTitle
     * @return NewsPagePart
     */
    public function setSubTitle($subTitle)
    {
        $this->subTitle = $subTitle;

        return $this;
    }

    /**
     * Get subTitle
     *
     * @return string 
     */
    public function getSubTitle()
    {
        return $this->subTitle;
    }

    /**
     * Set shortDesc
     *
     * @param string $shortDesc
     * @return NewsPagePart
     */
    public function setShortDesc($shortDesc)
    {
        $this->shortDesc = $shortDesc;

        return $this;
    }

    /**
     * Get shortDesc
     *
     * @return string 
     */
    public function getShortDesc()
    {
        return $this->shortDesc;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return NewsPagePart
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set imageAltText
     *
     * @param string $imageAltText
     * @return NewsPagePart
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
     * Set image
     *
     * @param \Kunstmaan\MediaBundle\Entity\Media $image
     * @return NewsPagePart
     */
    public function setImage(\Kunstmaan\MediaBundle\Entity\Media $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return \Kunstmaan\MediaBundle\Entity\Media 
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
        return 'SandboxWebsiteBundle:PageParts:NewsPagePart/view.html.twig';
    }

    /**
     * Get the admin form type.
     *
     * @return \Sandbox\WebsiteBundle\Form\PageParts\NewsPagePartAdminType
     */
    public function getDefaultAdminType()
    {
        return new \Sandbox\WebsiteBundle\Form\PageParts\NewsPagePartAdminType();
    }
}