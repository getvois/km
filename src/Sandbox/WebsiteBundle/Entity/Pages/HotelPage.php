<?php

namespace Sandbox\WebsiteBundle\Entity\Pages;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\ArticleBundle\Entity\AbstractArticlePage;
use Kunstmaan\NodeBundle\Helper\RenderContext;
use Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface;
use Sandbox\WebsiteBundle\Entity\HotelCriteria;
use Sandbox\WebsiteBundle\Entity\IPlaceFromTo;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Sandbox\WebsiteBundle\Form\Pages\HotelPageAdminType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * HotelPage
 *
 * @ORM\Table(name="sb_hotel_pages")
 * @ORM\Entity(repositoryClass="Sandbox\WebsiteBundle\Repository\HotelPageRepository")
 */
class HotelPage extends AbstractArticlePage implements HasPageTemplateInterface, IPlaceFromTo //AbstractPage
{

    public function service(ContainerInterface $container, Request $request, RenderContext $context)
    {
        parent::service($container, $request, $context);

        $em = $container->get('doctrine.orm.entity_manager');

        $page = $context['page'];

        $node = $em->getRepository('KunstmaanNodeBundle:Node')
            ->getNodeFor($page);

        $packages = $em->getRepository('SandboxWebsiteBundle:Pages\PackagePage')
            ->getPackagesByParent($request->getLocale(), $node);

        if(!$packages) $packages = [];

        $context['packages'] = $packages;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", scale=2, precision=8, nullable=true)
     */
    private $price;

    /**
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param string $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @var PackagePage
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\WebsiteBundle\Entity\Pages\PackagePage")
     */
    private $cheapestPackage;

    /**
     * @return PackagePage
     */
    public function getCheapestPackage()
    {
        return $this->cheapestPackage;
    }

    /**
     * @param PackagePage $cheapestPackage
     */
    public function setCheapestPackage($cheapestPackage)
    {
        $this->cheapestPackage = $cheapestPackage;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="www", type="string", length=512, nullable=true)
     */
    private $www;

    /**
     * @return string
     */
    public function getWww()
    {
        return $this->www;
    }

    /**
     * @param string $www
     */
    public function setWww($www)
    {
        $this->www = $www;
    }

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
     * @var PlaceOverviewPage
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage")
     */
    private $countryPlace;

    /**
     * @return PlaceOverviewPage
     */
    public function getCountryPlace()
    {
        return $this->countryPlace;
    }

    /**
     * @param PlaceOverviewPage $countryPlace
     */
    public function setCountryPlace($countryPlace)
    {
        $this->countryPlace = $countryPlace;
    }

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
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage")
     * @ORM\JoinTable(name="sb_hotel_place_overview")
     **/
    private $places;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setDate(new \DateTime());
        $this->criterias = new ArrayCollection();
        $this->places = new ArrayCollection();
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
        return array(
            array(
                'name' => 'OfferPage',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Pages\OfferPage'
            ),
            array(
                'name' => 'PackageOverviewPage',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Pages\PackageOverviewPage'
            ),
            array(
                'name' => 'PackagePage',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Pages\PackagePage'
            ),);
    }

    /**
     * @return string[]
     */
    public function getPagePartAdminConfigurations()
    {
        return array(
            'SandboxWebsiteBundle:main',
            'SandboxWebsiteBundle:gallery',
            'SandboxWebsiteBundle:information',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPageTemplates()
    {
        return array('SandboxWebsiteBundle:hotelpage');
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

    /**
     * Get full entity name
     * @return string
     */
    public function getEntityName()
    {
        return 'Sandbox\WebsiteBundle\Entity\Pages\HotelPage';
    }

    /**
     * Add fromPlaces
     *
     * @param PlaceOverviewPage $fromPlaces
     */
    public function addFromPlace(PlaceOverviewPage $fromPlaces){}

    /**
     * Add place
     *
     * @param PlaceOverviewPage $places
     * @return $this
     */
    public function addPlace(PlaceOverviewPage $places)
    {
        $this->places[] = $places;

        return $this;
    }

    /**
     * Get places
     *
     * @return \Doctrine\Common\Collections\Collection|PlaceOverviewPage[]
     */
    public function getPlaces()
    {
        return $this->places;
    }

    /**
     * Get fromPlaces
     *
     * @return \Doctrine\Common\Collections\Collection|PlaceOverviewPage[]
     */
    public function getFromPlaces(){ return [];}

    /**
     * Remove all places
     */
    public function removeAllPlaces()
    {
        $this->places->clear();
    }

    /**
     * Remove all from places
     */
    public function removeAllFromPlaces(){}
}