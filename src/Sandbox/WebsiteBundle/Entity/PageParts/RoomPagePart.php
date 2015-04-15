<?php

namespace Sandbox\WebsiteBundle\Entity\PageParts;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoomPagePart
 *
 * @ORM\Table(name="sb_room_page_parts")
 * @ORM\Entity
 */
class RoomPagePart extends \Kunstmaan\PagePartBundle\Entity\AbstractPagePart
{
    /**
     * @var integer
     *
     * @ORM\Column(name="room_id", type="integer", nullable=true)
     */
    private $roomId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;


    /**
     * Set roomId
     *
     * @param integer $roomId
     * @return RoomPagePart
     */
    public function setRoomId($roomId)
    {
        $this->roomId = $roomId;

        return $this;
    }

    /**
     * Get roomId
     *
     * @return integer 
     */
    public function getRoomId()
    {
        return $this->roomId;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return RoomPagePart
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
     * Set image
     *
     * @param string $image
     * @return RoomPagePart
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return RoomPagePart
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get the twig view.
     *
     * @return string
     */
    public function getDefaultView()
    {
        return 'SandboxWebsiteBundle:PageParts:RoomPagePart/view.html.twig';
    }

    /**
     * Get the admin form type.
     *
     * @return \Sandbox\WebsiteBundle\Form\PageParts\RoomPagePartAdminType
     */
    public function getDefaultAdminType()
    {
        return new \Sandbox\WebsiteBundle\Form\PageParts\RoomPagePartAdminType();
    }
}