<?php

namespace Sandbox\WebsiteBundle\Entity\PageParts;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\PagePartBundle\Entity\AbstractPagePart;
use Sandbox\WebsiteBundle\Entity\GooglePlace;
use Sandbox\WebsiteBundle\Form\PageParts\GoogleMapPagePartAdminType;

/**
 * GoogleMapPagePart
 *
 * @ORM\Table(name="sb_google_map_page_parts")
 * @ORM\Entity
 */
class GoogleMapPagePart extends AbstractPagePart
{
    public function deepClone()
    {
        $places = $this->getPlaces();
        $this->places = new ArrayCollection();
        if($places)
        foreach ($places as $place) {
            $cloneContact = clone $place;
            $this->addPlace($cloneContact);
        }
    }

    function __clone()
    {
        $this->deepClone();
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
     * @ORM\Column(name="only_map", type="boolean", nullable=true)
     */
    private $onlyMap;

    /**
     * @return string
     */
    public function getOnlyMap()
    {
        return $this->onlyMap;
    }

    /**
     * @param string $onlyMap
     * @return $this
     */
    public function setOnlyMap($onlyMap)
    {
        $this->onlyMap = $onlyMap;
        return $this;
    }


    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Sandbox\WebsiteBundle\Entity\GooglePlace", mappedBy="map", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $places;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->places = new ArrayCollection();
    }

    /**
     * Set title
     *
     * @param string $title
     * @return GoogleMapPagePart
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
     * Set content
     *
     * @param string $content
     * @return GoogleMapPagePart
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
     * Add places
     *
     * @param GooglePlace $places
     * @return GoogleMapPagePart
     */
    public function addPlace(GooglePlace $places)
    {
        $places->setMap($this);
        $this->places->add($places);
        //$this->places[] = $places;

        return $this;
    }

    /**
     * Remove places
     *
     * @param GooglePlace $places
     */
    public function removePlace(GooglePlace $places)
    {
        $this->places->removeElement($places);
    }

    /**
     * Get places
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPlaces()
    {
        return $this->places;
    }

    /**
     * Get the twig view.
     *
     * @return string
     */
    public function getDefaultView()
    {
        return 'SandboxWebsiteBundle:PageParts:GoogleMapPagePart/view.html.twig';
    }

    /**
     * Get the admin form type.
     *
     * @return GoogleMapPagePartAdminType
     */
    public function getDefaultAdminType()
    {
        return new GoogleMapPagePartAdminType();
    }
}