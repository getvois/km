<?php

namespace Sandbox\WebsiteBundle\Entity\Pages;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\NodeBundle\Entity\AbstractPage;
use Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface;
use Sandbox\WebsiteBundle\Form\Pages\PlacesPageAdminType;

/**
 * PlacesPage
 *
 * @ORM\Table(name="sb_places_pages")
 * @ORM\Entity
 */
class PlacesPage extends AbstractPage implements HasPageTemplateInterface
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
     * @return PlacesPageAdminType
     */
    public function getDefaultAdminType()
    {
        return new PlacesPageAdminType();
    }

    /**
     * @return array
     */
    public function getPossibleChildTypes()
    {
        return array(
            array(
                'name' => 'HotelPage',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Pages\HotelPage'
            ),
            array(
                'name' => 'Company Place',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Pages\CompanyPlacePage'
            ),
        );
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
        $this->children = new ArrayCollection();
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
     * @param PlacesPage $children
     * @return PlacesPage
     */
    public function addChild(PlacesPage $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param PlacesPage $children
     */
    public function removeChild(PlacesPage $children)
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
     * @param PlacesPage $parentPlace
     * @return PlacesPage
     */
    public function setParentPlace(PlacesPage $parentPlace = null)
    {
        $this->parentPlace = $parentPlace;

        return $this;
    }

    /**
     * Get parentPlace
     *
     * @return PlacesPage
     */
    public function getParentPlace()
    {
        return $this->parentPlace;
    }
}
