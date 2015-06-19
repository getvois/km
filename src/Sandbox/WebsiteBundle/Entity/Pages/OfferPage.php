<?php

namespace Sandbox\WebsiteBundle\Entity\Pages;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\NodeBundle\Controller\SlugActionInterface;
use Kunstmaan\NodeBundle\Entity\AbstractPage;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\NodeBundle\Helper\RenderContext;
use Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface;
use Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage;
use Sandbox\WebsiteBundle\Entity\IPlaceFromTo;
use Sandbox\WebsiteBundle\Entity\MapCategory;
use Sandbox\WebsiteBundle\Entity\PackageCategory;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Sandbox\WebsiteBundle\Form\Pages\OfferPageAdminType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * OfferPage
 *
 * @ORM\Table(name="sb_offer_pages")
 * @ORM\Entity(repositoryClass="Sandbox\WebsiteBundle\Repository\OffersPageRepository")
 */
class OfferPage extends AbstractPage implements HasPageTemplateInterface, IPlaceFromTo, SlugActionInterface
{
    public function getControllerAction()
    {
        return "SandboxWebsiteBundle:BackwardCompatibility:service";
    }

    public function service(ContainerInterface $container, Request $request, RenderContext $context)
    {
        parent::service($container, $request, $context);

        /** @var EntityManager $em */
        $em = $container->get('doctrine.orm.entity_manager');
        /** @var OfferPage $page */
        $page = $context['page'];

        $page->viewCount += 1;

        $em->persist($page);
        $em->flush();

        /** @var NodeTranslation $translation */
        $translation = $em->getRepository('KunstmaanNodeBundle:NodeTranslation')
            ->getNodeTranslationFor($page);

        $prevPage = $em->getRepository('SandboxWebsiteBundle:Pages\OfferPage')
            ->getPrevPage($translation->getCreated(), $request->getLocale());
        $nextPage = $em->getRepository('SandboxWebsiteBundle:Pages\OfferPage')
            ->getNextPage($translation->getCreated(), $request->getLocale());

        $context['prevPage'] = $prevPage;
        $context['nextPage'] = $nextPage;
        $context['page'] = $page;
    }

    /**
     * @var boolean
     *
     * @ORM\Column(name="favorite", type="boolean", nullable=true)
     */
    private $favorite;

    /**
     * @return boolean
     */
    public function isFavorite()
    {
        return $this->favorite;
    }

    /**
     * @param boolean $favorite
     */
    public function setFavorite($favorite)
    {
        $this->favorite = $favorite;
    }

    /**
     * @var boolean
     *
     * @ORM\Column(name="translated", type="boolean", nullable=true)
     */
    private $translated;

    /**
     * @return boolean
     */
    public function isTranslated()
    {
        return $this->translated;
    }

    /**
     * @param boolean $translated
     */
    public function setTranslated($translated)
    {
        $this->translated = $translated;
    }

    /**
     * @var boolean
     *
     * @ORM\Column(name="archived", type="boolean", nullable=true)
     */
    private $archived;

    /**
     * @return boolean
     */
    public function isArchived()
    {
        return $this->archived;
    }

    /**
     * @param boolean $archived
     */
    public function setArchived($archived)
    {
        $this->archived = $archived;
    }

    /**
     * @var MapCategory
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\WebsiteBundle\Entity\MapCategory")
     */
    private $mapCategory;

    /**
     * @return MapCategory
     */
    public function getMapCategory()
    {
        return $this->mapCategory;
    }

    /**
     * @param MapCategory $mapCategory
     */
    public function setMapCategory($mapCategory)
    {
        $this->mapCategory = $mapCategory;
    }

    /**
     * @var int
     *
     * @ORM\Column(name="view_count", type="integer")
     */
    private $viewCount;

    /**
     * @return int
     */
    public function getViewCount()
    {
        return $this->viewCount;
    }

    /**
     * @param int $viewCount
     */
    public function setViewCount($viewCount)
    {
        $this->viewCount = $viewCount;
    }

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, name="original_language")
     */
    protected $originalLanguage;

    /**
     * @return string
     */
    public function getOriginalLanguage()
    {
        return $this->originalLanguage;
    }

    /**
     * @param string $originalLanguage
     */
    public function setOriginalLanguage($originalLanguage)
    {
        $this->originalLanguage = $originalLanguage;
    }

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, name="title_translated")
     */
    protected $titleTranslated;

    /**
     * @var string
     *
     * @ORM\Column(name="summary", type="text", nullable=true)
     */
    protected $summary;

    /**
     * @return string
     */
    public function getTitleTranslated()
    {
        return $this->titleTranslated;
    }

    /**
     * @param string $titleTranslated
     */
    public function setTitleTranslated($titleTranslated)
    {
        $this->titleTranslated = $titleTranslated;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @param string $summary
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="offer_id", type="string", length=255, nullable=true)
     */
    private $offerId;

    /**
     * @var string
     *
     * @ORM\Column(name="long_title", type="string", length=255, nullable=true)
     */
    private $longTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @var string
     *
     * @ORM\Column(name="wide_image", type="string", length=255, nullable=true)
     */
    private $wideImage;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="price_normal", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $priceNormal;

    /**
     * @var string
     *
     * @ORM\Column(name="price_eur", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $priceEur;

    /**
     * @var string
     *
     * @ORM\Column(name="price_normal_eur", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $priceNormalEur;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=255, nullable=true)
     */
    private $currency;

    /**
     * @var string
     *
     * @ORM\Column(name="days", type="string", length=255, nullable=true)
     */
    private $days;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="long_description", type="text", nullable=true)
     */
    private $longDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="short_description", type="text", nullable=true)
     */
    private $shortDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="short_description_translated", type="text", nullable=true)
     */
    private $shortDescriptionTranslated;

    /**
     * @return string
     */
    public function getShortDescriptionTranslated()
    {
        return $this->shortDescriptionTranslated;
    }

    /**
     * @param string $shortDescriptionTranslated
     */
    public function setShortDescriptionTranslated($shortDescriptionTranslated)
    {
        $this->shortDescriptionTranslated = $shortDescriptionTranslated;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="logo", type="string", length=255, nullable=true)
     */
    private $logo;

    /**
     * @var string
     *
     * @ORM\Column(name="absolute_url", type="string", length=255, nullable=true)
     */
    private $absoluteUrl;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\PackageCategory")
     * @ORM\JoinTable(name="sb_offer_page_category",
     *   joinColumns={
     *     @ORM\JoinColumn(name="offer_page_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="package_category_id", referencedColumnName="id")
     *   }
     * )
     */
    private $categories;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="region", type="string", length=255, nullable=true)
     */
    private $region;

    /**
     * @var string
     *
     * @ORM\Column(name="transportation", type="string", length=255, nullable=true)
     */
    private $transportation;

    /**
     * @var string
     *
     * @ORM\Column(name="target_group", type="string", length=255, nullable=true)
     */
    private $targetGroup;

    /**
     * @var string
     *
     * @ORM\Column(name="accomodation", type="string", length=255, nullable=true)
     */
    private $accomodation;

    /**
     * @var string
     *
     * @ORM\Column(name="accomodation_type", type="string", length=255, nullable=true)
     */
    private $accomodationType;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiration_date", type="datetime", nullable=true)
     */
    private $expirationDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="offer_sold", type="integer", nullable=true)
     */
    private $offerSold;

    /**
     * @var string
     *
     * @ORM\Column(name="adress", type="string", length=255, nullable=true)
     */
    private $adress;

    /**
     * @var string
     *
     * @ORM\Column(name="included", type="text", nullable=true)
     */
    private $included;

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
     * @ORM\Column(name="nights", type="string", length=255, nullable=true)
     */
    private $nights;

    /**
     * @var string
     *
     * @ORM\Column(name="price_type", type="string", length=255, nullable=true)
     */
    private $priceType;

    /**
     * @var string
     *
     * @ORM\Column(name="price_per", type="string", length=255, nullable=true)
     */
    private $pricePer;

    /**
     * @var string
     *
     * @ORM\Column(name="discount", type="string", length=255, nullable=true)
     */
    private $discount;

    /**
     * @var integer
     *
     * @ORM\Column(name="max_persons", type="integer", nullable=true)
     */
    private $maxPersons;

    /**
     * @var integer
     *
     * @ORM\Column(name="min_persons", type="integer", nullable=true)
     */
    private $minPersons;

    /**
     * @var boolean
     *
     * @ORM\Column(name="sold_out", type="boolean", nullable=true)
     */
    private $soldOut;

    /**
     * @var string
     *
     * @ORM\Column(name="booking_fee", type="string", length=255, nullable=true)
     */
    private $bookingFee;

    /**
     * @var string
     *
     * @ORM\Column(name="extra", type="string", length=255, nullable=true)
     */
    private $extra;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage")
     * @ORM\JoinTable(name="sb_offer_place_overview")
     **/
    private $places;


    /**
     * @var CompanyOverviewPage
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage")
     */
    private $company;

    /**
     * @var PlaceOverviewPage
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage")
     */
    private $countryPlace;


    function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->places = new ArrayCollection();
    }

    public function inCategory($name)
    {
        /** @var PackageCategory $category */
        foreach ($this->categories as $category) {
            if($category->getName() == $name)
                return true;
        }

        return false;
    }

    public function removeAllCategories()
    {
        $this->categories->clear();
    }
    /**
     * @return CompanyOverviewPage
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param CompanyOverviewPage $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

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
     * Set offerId
     *
     * @param string $offerId
     * @return OfferPage
     */
    public function setOfferId($offerId)
    {
        $this->offerId = $offerId;

        return $this;
    }

    /**
     * Get offerId
     *
     * @return string
     */
    public function getOfferId()
    {
        return $this->offerId;
    }

    /**
     * Set longTitle
     *
     * @param string $longTitle
     * @return OfferPage
     */
    public function setLongTitle($longTitle)
    {
        $this->longTitle = $longTitle;

        return $this;
    }

    /**
     * Get longTitle
     *
     * @return string 
     */
    public function getLongTitle()
    {
        return $this->longTitle;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return OfferPage
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
     * Set wideImage
     *
     * @param string $wideImage
     * @return OfferPage
     */
    public function setWideImage($wideImage)
    {
        $this->wideImage = $wideImage;

        return $this;
    }

    /**
     * Get wideImage
     *
     * @return string 
     */
    public function getWideImage()
    {
        return $this->wideImage;
    }

    /**
     * Set price
     *
     * @param string $price
     * @return OfferPage
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getPriceEur()
    {
        return $this->priceEur;
    }

    /**
     * @param string $priceEur
     */
    public function setPriceEur($priceEur)
    {
        $this->priceEur = $priceEur;
    }

    /**
     * @return string
     */
    public function getPriceNormalEur()
    {
        return $this->priceNormalEur;
    }

    /**
     * @param string $priceNormalEur
     */
    public function setPriceNormalEur($priceNormalEur)
    {
        $this->priceNormalEur = $priceNormalEur;
    }

    /**
     * Set priceNormal
     *
     * @param string $priceNormal
     * @return OfferPage
     */
    public function setPriceNormal($priceNormal)
    {
        $this->priceNormal = $priceNormal;

        return $this;
    }

    /**
     * Get priceNormal
     *
     * @return string 
     */
    public function getPriceNormal()
    {
        return $this->priceNormal;
    }

    /**
     * Set currency
     *
     * @param string $currency
     * @return OfferPage
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency
     *
     * @return string 
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set days
     *
     * @param string $days
     * @return OfferPage
     */
    public function setDays($days)
    {
        $this->days = $days;

        return $this;
    }

    /**
     * Get days
     *
     * @return string 
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return OfferPage
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
     * Set longDescription
     *
     * @param string $longDescription
     * @return OfferPage
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
     * Set shortDescription
     *
     * @param string $shortDescription
     * @return OfferPage
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
     * Set logo
     *
     * @param string $logo
     * @return OfferPage
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo
     *
     * @return string 
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set absoluteUrl
     *
     * @param string $absoluteUrl
     * @return OfferPage
     */
    public function setAbsoluteUrl($absoluteUrl)
    {
        $this->absoluteUrl = $absoluteUrl;

        return $this;
    }

    /**
     * Get absoluteUrl
     *
     * @return string 
     */
    public function getAbsoluteUrl()
    {
        return $this->absoluteUrl;
    }


    /**
     * Add categories
     *
     * @param PackageCategory $categories
     * @return PackagePage
     */
    public function addCategory(PackageCategory $categories)
    {
        $this->categories[] = $categories;

        return $this;
    }

    /**
     * Remove categories
     *
     * @param PackageCategory $categories
     */
    public function removeCategory(PackageCategory $categories)
    {
        $this->categories->removeElement($categories);
    }

    /**
     * Get categories
     *
     * @return \Doctrine\Common\Collections\Collection|PackageCategory[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return OfferPage
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
     * Set city
     *
     * @param string $city
     * @return OfferPage
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
     * Set region
     *
     * @param string $region
     * @return OfferPage
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return string 
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set transportation
     *
     * @param string $transportation
     * @return OfferPage
     */
    public function setTransportation($transportation)
    {
        $this->transportation = $transportation;

        return $this;
    }

    /**
     * Get transportation
     *
     * @return string 
     */
    public function getTransportation()
    {
        return $this->transportation;
    }

    /**
     * Set targetGroup
     *
     * @param string $targetGroup
     * @return OfferPage
     */
    public function setTargetGroup($targetGroup)
    {
        $this->targetGroup = $targetGroup;

        return $this;
    }

    /**
     * Get targetGroup
     *
     * @return string 
     */
    public function getTargetGroup()
    {
        return $this->targetGroup;
    }

    /**
     * Set accomodation
     *
     * @param string $accomodation
     * @return OfferPage
     */
    public function setAccomodation($accomodation)
    {
        $this->accomodation = $accomodation;

        return $this;
    }

    /**
     * Get accomodation
     *
     * @return string 
     */
    public function getAccomodation()
    {
        return $this->accomodation;
    }

    /**
     * Set accomodationType
     *
     * @param string $accomodationType
     * @return OfferPage
     */
    public function setAccomodationType($accomodationType)
    {
        $this->accomodationType = $accomodationType;

        return $this;
    }

    /**
     * Get accomodationType
     *
     * @return string 
     */
    public function getAccomodationType()
    {
        return $this->accomodationType;
    }

    /**
     * Set expirationDate
     *
     * @param \DateTime $expirationDate
     * @return OfferPage
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    /**
     * Get expirationDate
     *
     * @return \DateTime 
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * Set offerSold
     *
     * @param integer $offerSold
     * @return OfferPage
     */
    public function setOfferSold($offerSold)
    {
        $this->offerSold = $offerSold;

        return $this;
    }

    /**
     * Get offerSold
     *
     * @return integer 
     */
    public function getOfferSold()
    {
        return $this->offerSold;
    }

    /**
     * Set adress
     *
     * @param string $adress
     * @return OfferPage
     */
    public function setAdress($adress)
    {
        $this->adress = $adress;

        return $this;
    }

    /**
     * Get adress
     *
     * @return string 
     */
    public function getAdress()
    {
        return $this->adress;
    }

    /**
     * Set included
     *
     * @param string $included
     * @return OfferPage
     */
    public function setIncluded($included)
    {
        $this->included = $included;

        return $this;
    }

    /**
     * Get included
     *
     * @return string 
     */
    public function getIncluded()
    {
        return $this->included;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     * @return OfferPage
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
     * @return OfferPage
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
     * Set nights
     *
     * @param string $nights
     * @return OfferPage
     */
    public function setNights($nights)
    {
        $this->nights = $nights;

        return $this;
    }

    /**
     * Get nights
     *
     * @return string 
     */
    public function getNights()
    {
        return $this->nights;
    }

    /**
     * Set priceType
     *
     * @param string $priceType
     * @return OfferPage
     */
    public function setPriceType($priceType)
    {
        $this->priceType = $priceType;

        return $this;
    }

    /**
     * Get priceType
     *
     * @return string 
     */
    public function getPriceType()
    {
        return $this->priceType;
    }

    /**
     * Set pricePer
     *
     * @param string $pricePer
     * @return OfferPage
     */
    public function setPricePer($pricePer)
    {
        $this->pricePer = $pricePer;

        return $this;
    }

    /**
     * Get pricePer
     *
     * @return string 
     */
    public function getPricePer()
    {
        return $this->pricePer;
    }

    /**
     * Set discount
     *
     * @param string $discount
     * @return OfferPage
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Get discount
     *
     * @return string 
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set maxPersons
     *
     * @param integer $maxPersons
     * @return OfferPage
     */
    public function setMaxPersons($maxPersons)
    {
        $this->maxPersons = $maxPersons;

        return $this;
    }

    /**
     * Get maxPersons
     *
     * @return integer 
     */
    public function getMaxPersons()
    {
        return $this->maxPersons;
    }

    /**
     * Set minPersons
     *
     * @param integer $minPersons
     * @return OfferPage
     */
    public function setMinPersons($minPersons)
    {
        $this->minPersons = $minPersons;

        return $this;
    }

    /**
     * Get minPersons
     *
     * @return integer 
     */
    public function getMinPersons()
    {
        return $this->minPersons;
    }

    /**
     * Set soldOut
     *
     * @param boolean $soldOut
     * @return OfferPage
     */
    public function setSoldOut($soldOut)
    {
        $this->soldOut = $soldOut;

        return $this;
    }

    /**
     * Get soldOut
     *
     * @return boolean 
     */
    public function getSoldOut()
    {
        return $this->soldOut;
    }

    /**
     * Set bookingFee
     *
     * @param string $bookingFee
     * @return OfferPage
     */
    public function setBookingFee($bookingFee)
    {
        $this->bookingFee = $bookingFee;

        return $this;
    }

    /**
     * Get bookingFee
     *
     * @return string 
     */
    public function getBookingFee()
    {
        return $this->bookingFee;
    }

    /**
     * Set extra
     *
     * @param string $extra
     * @return OfferPage
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;

        return $this;
    }

    /**
     * Get extra
     *
     * @return string 
     */
    public function getExtra()
    {
        return $this->extra;
    }
    /**
     * Returns the default backend form type for this page
     *
     * @return OfferPageAdminType
     */
    public function getDefaultAdminType()
    {
        return new OfferPageAdminType();
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
        return 'SandboxWebsiteBundle:Offer:view.html.twig';
    }


    /**
     * Get full entity name
     * @return string
     */
    public function getEntityName()
    {
        return 'Sandbox\WebsiteBundle\Entity\Pages\OfferPage';
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
    public function getFromPlaces(){return [];}

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

    /**
     * @return bool
     */
    public function hasCoordinates()
    {
        if($this->getLatitude() && $this->getLongitude())
            return true;
        return false;
    }
}