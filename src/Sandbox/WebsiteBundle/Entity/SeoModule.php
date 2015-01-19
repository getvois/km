<?php

namespace Sandbox\WebsiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\AdminBundle\Entity\AbstractEntity;

/**
 * SeoModule
 *
 * @ORM\Table(name="sb_seo_module")
 * @ORM\Entity
 */
class SeoModule extends AbstractEntity
{
    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Host", inversedBy="seoModules")
     * @ORM\JoinTable(name="sb_seomodules_hosts")
     */
    private $hosts;

    /**
     * @var string
     *
     * @ORM\Column(name="lang", type="string", length=2)
     */
    private $lang;

    /**
     * @var string
     *
     * @ORM\Column(name="entity", type="string", length=255)
     */
    private $entity;

    /**
     * @var string
     *
     * @ORM\Column(name="top", type="text", nullable=true)
     */
    private $top;

    /**
     * @var string
     *
     * @ORM\Column(name="bottom", type="text", nullable=true)
     */
    private $bottom;

    /**
     * @var string
     *
     * @ORM\Column(name="footer", type="text", nullable=true)
     */
    private $footer;


    /**
     * Set lang
     *
     * @param string $lang
     * @return SeoModule
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
     * Set entity
     *
     * @param string $entity
     * @return SeoModule
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get entity
     *
     * @return string 
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set top
     *
     * @param string $top
     * @return SeoModule
     */
    public function setTop($top)
    {
        $this->top = $top;

        return $this;
    }

    /**
     * Get top
     *
     * @return string 
     */
    public function getTop()
    {
        return $this->top;
    }

    /**
     * Set bottom
     *
     * @param string $bottom
     * @return SeoModule
     */
    public function setBottom($bottom)
    {
        $this->bottom = $bottom;

        return $this;
    }

    /**
     * Get bottom
     *
     * @return string 
     */
    public function getBottom()
    {
        return $this->bottom;
    }

    /**
     * Set footer
     *
     * @param string $footer
     * @return SeoModule
     */
    public function setFooter($footer)
    {
        $this->footer = $footer;

        return $this;
    }

    /**
     * Get footer
     *
     * @return string 
     */
    public function getFooter()
    {
        return $this->footer;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->hosts = new ArrayCollection();
    }

    /**
     * Add hosts
     *
     * @param Host $hosts
     * @return SeoModule
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
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getHosts()
    {
        return $this->hosts;
    }
}
