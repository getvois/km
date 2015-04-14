<?php

namespace Sandbox\WebsiteBundle\Entity\PageParts;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\AdminBundle\Entity\DeepCloneInterface;
use Kunstmaan\PagePartBundle\Entity\AbstractPagePart;
use Sandbox\WebsiteBundle\Entity\HotelImage;
use Sandbox\WebsiteBundle\Form\PageParts\HotelGalleryPagePartAdminType;

/**
 * HotelGalleryPagePart
 *
 * @ORM\Table(name="sb_hotel_gallery_page_parts")
 * @ORM\Entity
 */
class HotelGalleryPagePart extends AbstractPagePart implements DeepCloneInterface
{

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Sandbox\WebsiteBundle\Entity\HotelImage", mappedBy="galleryPagePart", cascade={"persist", "remove"}, orphanRemoval=true)
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
        $this->images = $images;
    }

    /**
     * @param HotelImage $contactInfo
     */
    public function addImage(HotelImage $contactInfo)
    {
        $contactInfo->setImageUrl($this);

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
     * Get the twig view.
     *
     * @return string
     */
    public function getDefaultView()
    {
        return 'SandboxWebsiteBundle:PageParts:HotelGalleryPagePart/view.html.twig';
    }

    /**
     * Get the admin form type.
     *
     * @return HotelGalleryPagePartAdminType
     */
    public function getDefaultAdminType()
    {
        return new HotelGalleryPagePartAdminType();
    }
}