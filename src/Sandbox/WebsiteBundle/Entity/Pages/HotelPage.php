<?php

namespace Sandbox\WebsiteBundle\Entity\Pages;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\ArticleBundle\Entity\AbstractArticlePage;
use Kunstmaan\NodeBundle\Entity\AbstractPage;
use Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface;
use Sandbox\WebsiteBundle\Entity\HotelCriteria;
use Sandbox\WebsiteBundle\Form\Pages\HotelPageAdminType;

/**
 * HotelPage
 *
 * @ORM\Table(name="sb_hotel_pages")
 * @ORM\Entity
 */
class HotelPage extends AbstractArticlePage implements HasPageTemplateInterface //AbstractPage
{
    /**
     * @var string
     *
     * @ORM\Column(name="street", type="string", length=255, nullable=true)
     */
    private $street;

    /**
     * @var int
     *
     * @ORM\Column(name="hotel_id", type="integer", nullable=true)
     */
    private $hotelId;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="city_parish", type="string", length=255, nullable=true)
     */
    private $cityParish;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="string", length=255, nullable=true)
     */
    private $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="string", length=255, nullable=true)
     */
    private $longitude;

    /**
     * @var string
     *
     * @ORM\Column(name="short_description", type="text", nullable=true)
     */
    private $shortDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="long_description", type="text", nullable=true)
     */
    private $longDescription;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\HotelCriteria")
     * @ORM\JoinTable(name="sb_hotel_page_hotel_criteria",
     *   joinColumns={
     *     @ORM\JoinColumn(name="hotel_page_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="hotel_criteria_id", referencedColumnName="id")
     *   }
     * )
     */
    private $criterias;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->criterias = new ArrayCollection();
    }

    /**
     * Set street
     *
     * @param string $street
     * @return HotelPage
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string 
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @return int
     */
    public function getHotelId()
    {
        return $this->hotelId;
    }

    /**
     * @param int $hotelId
     */
    public function setHotelId($hotelId)
    {
        $this->hotelId = $hotelId;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return HotelPage
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set cityParish
     *
     * @param string $cityParish
     * @return HotelPage
     */
    public function setCityParish($cityParish)
    {
        $this->cityParish = $cityParish;

        return $this;
    }

    /**
     * Get cityParish
     *
     * @return string 
     */
    public function getCityParish()
    {
        return $this->cityParish;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return HotelPage
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string 
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     * @return HotelPage
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return string 
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     * @return HotelPage
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string 
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set shortDescription
     *
     * @param string $shortDescription
     * @return HotelPage
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    /**
     * Get shortDescription
     *
     * @return string 
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * Set longDescription
     *
     * @param string $longDescription
     * @return HotelPage
     */
    public function setLongDescription($longDescription)
    {
        $this->longDescription = $longDescription;

        return $this;
    }

    /**
     * Get longDescription
     *
     * @return string 
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }

    /**
     * Add criterias
     *
     * @param HotelCriteria $criterias
     * @return HotelPage
     */
    public function addCriteria(HotelCriteria $criterias)
    {
        $this->criterias[] = $criterias;

        return $this;
    }

    /**
     * Remove criterias
     *
     * @param HotelCriteria $criterias
     */
    public function removeCriteria(HotelCriteria $criterias)
    {
        $this->criterias->removeElement($criterias);
    }

    /**
     * Get criterias
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCriterias()
    {
        return $this->criterias;
    }
    /**
     * Returns the default backend form type for this page
     *
     * @return HotelPageAdminType
     */
    public function getDefaultAdminType()
    {
        return new HotelPageAdminType();
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
        return 'SandboxWebsiteBundle:Hotel:view.html.twig';
    }
}