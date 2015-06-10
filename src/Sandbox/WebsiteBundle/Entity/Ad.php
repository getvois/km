<?php

namespace Sandbox\WebsiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\AdminBundle\Entity\AbstractEntity;

/**
 * Ad
 *
 * @ORM\Table(name="sb_ad")
 * @ORM\Entity
 */
class Ad extends AbstractEntity
{

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="campaign", type="string", length=255)
     */
    private $campaign;

    /**
     * @var string
     *
     * @ORM\Column(name="lang", type="string", length=10)
     */
    private $lang;

    /**
     * @var array
     *
     * @ORM\Column(name="pagetypes", type="array")
     */
    private $pagetypes;

    /**
     * @var string
     *
     * @ORM\Column(name="position", type="string", length=255)
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(name="html", type="text")
     */
    private $html;

    /**
     * @var integer
     *
     * @ORM\Column(name="weight", type="integer")
     */
    private $weight;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Host")
     * @ORM\JoinTable(name="sb_host_ad")
     **/
    private $hosts;


    function __construct()
    {
        $this->hosts = new ArrayCollection();
    }


    /**
     * Set name
     *
     * @param string $name
     *
     * @return Ad
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
     * Set campaign
     *
     * @param string $campaign
     *
     * @return Ad
     */
    public function setCampaign($campaign)
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * Get campaign
     *
     * @return string
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * Set lang
     *
     * @param string $lang
     *
     * @return Ad
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get lang
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set pagetypes
     *
     * @param array $pagetypes
     *
     * @return Ad
     */
    public function setPagetypes($pagetypes)
    {
        $this->pagetypes = $pagetypes;

        return $this;
    }

    /**
     * Get pagetypes
     *
     * @return array
     */
    public function getPagetypes()
    {
        return $this->pagetypes;
    }

    /**
     * Set position
     *
     * @param string $position
     *
     * @return Ad
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set html
     *
     * @param string $html
     *
     * @return Ad
     */
    public function setHtml($html)
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Get html
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * Set weight
     *
     * @param integer $weight
     *
     * @return Ad
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Get weight
     *
     * @return integer
     */
    public function getWeight()
    {
        return $this->weight;
    }


    /**
     * Add hosts
     *
     * @param Host $hosts
     * @return Ad
     */
    public function addHost(Host $hosts)
    {
        $this->hosts[] = $hosts;

        return $this;
    }

    /**
     * Remove hosts
     *
     * @param Host $hosts
     */
    public function removeHost(Host $hosts)
    {
        $this->hosts->removeElement($hosts);
    }

    /**
     * Get hosts
     *
     * @return \Doctrine\Common\Collections\Collection|Ad[]
     */
    public function getHosts()
    {
        return $this->hosts;
    }

}

