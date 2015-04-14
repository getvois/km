<?php

namespace Sandbox\WebsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\AdminBundle\Entity\AbstractEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ContactInfo
 *
 * @ORM\Table(name="sb_hotel_image")
 * @ORM\Entity()
 */
class HotelImage extends AbstractEntity {

    /**
     * @ORM\Column(name="image_url", type="string", length=512)
     * @Assert\NotBlank()
     */
    private $imageUrl;

    /**
     * @return mixed
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @param mixed $imageUrl
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
    }


    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\WebsiteBundle\Entity\PageParts\HotelGalleryPagePart", inversedBy="images")
     **/
    private $galleryPagePart;

    /**
     * @return mixed
     */
    public function getGalleryPagePart()
    {
        return $this->galleryPagePart;
    }

    /**
     * @param mixed $galleryPagePart
     */
    public function setGalleryPagePart($galleryPagePart)
    {
        $this->galleryPagePart = $galleryPagePart;
    }

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\WebsiteBundle\Entity\PageParts\HotelInformationPagePart", inversedBy="images", cascade={"persist"})
     **/
    private $infoPagePart;

    /**
     * @return mixed
     */
    public function getInfoPagePart()
    {
        return $this->infoPagePart;
    }

    /**
     * @param mixed $infoPagePart
     */
    public function setInfoPagePart($infoPagePart)
    {
        $this->infoPagePart = $infoPagePart;
    }


}