<?php

namespace Sandbox\WebsiteBundle\Entity\Pages;

use Doctrine\ORM\Mapping as ORM;

/**
 * PlacesPage
 *
 * @ORM\Table(name="sb_places_pages")
 * @ORM\Entity
 */
class PlacesPage extends \Kunstmaan\NodeBundle\Entity\AbstractPage implements \Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface
{
    /**
     * @ORM\OneToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Pages\PlacesPage", mappedBy="parentPlace")
     **/
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="Sandbox\WebsiteBundle\Entity\Pages\PlacesPage", inversedBy="children")
     **/
    private $parentPlace;


    /**
     * Returns the default backend form type for this page
     *
     * @return \Sandbox\WebsiteBundle\Form\Pages\PlacesPageAdminType
     */
    public function getDefaultAdminType()
    {
        return new \Sandbox\WebsiteBundle\Form\Pages\PlacesPageAdminType();
    }

    /**
     * @return array
     */
    public function getPossibleChildTypes()
    {
        return array();
    }

    /**
     * @return string[]
     */
    public function getPagePartAdminConfigurations()
    {
        return array(
            'SandboxWebsiteBundle:main',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPageTemplates()
    {
        return array('SandboxWebsiteBundle:contentpage');
    }

    /**
     * Get the twig view.
     *
     * @return string
     */
    public function getDefaultView()
    {
        return 'SandboxWebsiteBundle:Pages:Common/view.html.twig';
    }
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $pageTitle;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set title
     *
     * @param string $title
     * @return PlacesPage
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
     * Set pageTitle
     *
     * @param string $pageTitle
     * @return PlacesPage
     */
    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;

        return $this;
    }

    /**
     * Get pageTitle
     *
     * @return string 
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    /**
     * Add children
     *
     * @param \Sandbox\WebsiteBundle\Entity\Pages\PlacesPage $children
     * @return PlacesPage
     */
    public function addChild(\Sandbox\WebsiteBundle\Entity\Pages\PlacesPage $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \Sandbox\WebsiteBundle\Entity\Pages\PlacesPage $children
     */
    public function removeChild(\Sandbox\WebsiteBundle\Entity\Pages\PlacesPage $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parentPlace
     *
     * @param \Sandbox\WebsiteBundle\Entity\Pages\PlacesPage $parentPlace
     * @return PlacesPage
     */
    public function setParentPlace(\Sandbox\WebsiteBundle\Entity\Pages\PlacesPage $parentPlace = null)
    {
        $this->parentPlace = $parentPlace;

        return $this;
    }

    /**
     * Get parentPlace
     *
     * @return \Sandbox\WebsiteBundle\Entity\Pages\PlacesPage 
     */
    public function getParentPlace()
    {
        return $this->parentPlace;
    }
}
