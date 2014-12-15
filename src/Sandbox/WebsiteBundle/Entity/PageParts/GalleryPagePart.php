<?php

namespace Sandbox\WebsiteBundle\Entity\PageParts;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\PagePartBundle\Entity\AbstractPagePart;
use Sandbox\WebsiteBundle\Entity\Image;
use Sandbox\WebsiteBundle\Form\PageParts\GalleryPagePartAdminType;

/**
 * GalleryPagePart
 *
 * @ORM\Table(name="sb_gallery_page_parts")
 * @ORM\Entity
 */
class GalleryPagePart extends AbstractPagePart
{
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Image", mappedBy="galleryPagePart", cascade={"persist", "remove"}, orphanRemoval=true)
     **/
    private $images;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    /**
     * @param ArrayCollection $images
     */
    public function setImages($images)
    {
        $this->images = $images;
    }

    /**
     * @return ArrayCollection
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @param Image $contactInfo
     */
    public function addImage(Image $contactInfo)
    {
        $contactInfo->setGalleryPagePart($this);

        $this->images->add($contactInfo);
    }

    /**
     * @param Image $contactInfo
     */
    public function removeImage(Image $contactInfo)
    {
        $this->images->removeElement($contactInfo);
    }

    /**
     * When cloning this entity, we must also clone all entities in the ArrayCollection
     */
    public function deepClone()
    {
        $images = $this->getImages();
        $this->images = new ArrayCollection();
        if($images)
            foreach ($images as $image) {
                $cloneContact = clone $image;
                $this->addImage($cloneContact);
            }
    }

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

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
     * @return GalleryPagePart
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
     * Set description
     *
     * @param string $description
     * @return GalleryPagePart
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
     * Set wrapperClass
     *
     * @param string $wrapperClass
     * @return GalleryPagePart
     */
    public function setWrapperClass($wrapperClass)
    {
        $this->wrapperClass = $wrapperClass;

        return $this;
    }

    /**
     * Get wrapperClass
     *
     * @return string 
     */
    public function getWrapperClass()
    {
        return $this->wrapperClass;
    }

    /**
     * Get the twig view.
     *
     * @return string
     */
    public function getDefaultView()
    {
        return 'SandboxWebsiteBundle:PageParts:GalleryPagePart/view.html.twig';
    }

    /**
     * Get the admin form type.
     *
     * @return GalleryPagePartAdminType
     */
    public function getDefaultAdminType()
    {
        return new GalleryPagePartAdminType();
    }

    function __clone()
    {
        $this->deepClone();
    }


}