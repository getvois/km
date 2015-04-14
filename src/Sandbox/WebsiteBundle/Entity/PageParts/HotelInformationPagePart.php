<?php

namespace Sandbox\WebsiteBundle\Entity\PageParts;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\PagePartBundle\Entity\AbstractPagePart;

/**
 * HotelInformationPagePart
 *
 * @ORM\Table(name="sb_hotel_information_page_parts")
 * @ORM\Entity
 */
class HotelInformationPagePart extends AbstractPagePart
{

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
     * @return \Sandbox\WebsiteBundle\Form\PageParts\HotelInformationPagePartAdminType
     */
    public function getDefaultAdminType()
    {
        return new \Sandbox\WebsiteBundle\Form\PageParts\HotelInformationPagePartAdminType();
    }
}