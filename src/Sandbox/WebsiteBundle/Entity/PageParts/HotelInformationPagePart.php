<?php

namespace Sandbox\WebsiteBundle\Entity\PageParts;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\AdminBundle\Entity\DeepCloneInterface;
use Kunstmaan\PagePartBundle\Entity\AbstractPagePart;
use Sandbox\WebsiteBundle\Entity\HotelImage;
use Sandbox\WebsiteBundle\Form\PageParts\HotelInformationPagePartAdminType;

/**
 * HotelInformationPagePart
 *
 * @ORM\Table(name="sb_hotel_information_page_parts")
 * @ORM\Entity
 */
class HotelInformationPagePart extends AbstractPagePart implements DeepCloneInterface
{


    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Sandbox\WebsiteBundle\Entity\HotelImage", mappedBy="infoPagePart", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $images;

    /**
     * @return ArrayCollection
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @param ArrayCollection $images
     */
    public function setImages($images)
    {
        foreach ($images as $image) {
            $this->addImage($image);
        }
    }

    /**
     * @param HotelImage $contactInfo
     */
    public function addImage(HotelImage $contactInfo)
    {
        $contactInfo->setInfoPagePart($this);

        $this->images->add($contactInfo);
    }

    /**
     * @param HotelImage $contactInfo
     */
    public function removeImage(HotelImage $contactInfo)
    {
        $this->images->removeElement($contactInfo);
    }

    public function deepClone()
    {
        $contacts = $this->getImages();
        $this->images = new ArrayCollection();
        foreach ($contacts as $contact) {
            $cloneContact = clone $contact;
            $this->addImage($cloneContact);
        }
    }

    /**
     * @var HotelGalleryPagePart
     * @ORM\ManyToOne(targetEntity="Sandbox\WebsiteBundle\Entity\PageParts\HotelGalleryPagePart")
     */
    private $gallery;

    /**
     * @return HotelGalleryPagePart
     */
    public function getGallery()
    {
        return $this->gallery;
    }

    /**
     * @param HotelGalleryPagePart $gallery
     */
    public function setGallery($gallery)
    {
        $this->gallery = $gallery;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;


    /**
     * Set name
     *
     * @param string $name
     * @return HotelInformationPagePart
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
     * Set description
     *
     * @param string $description
     * @return HotelInformationPagePart
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
     * Get the twig view.
     *
     * @return string
     */
    public function getDefaultView()
    {
        return 'SandboxWebsiteBundle:PageParts:HotelInformationPagePart/view.html.twig';
    }

    /**
     * Get the admin form type.
     *
     * @return HotelInformationPagePartAdminType
     */
    public function getDefaultAdminType()
    {
        return new HotelInformationPagePartAdminType();
    }
}