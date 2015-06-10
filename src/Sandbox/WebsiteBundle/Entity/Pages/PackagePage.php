<?php

namespace Sandbox\WebsiteBundle\Entity\Pages;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\ArticleBundle\Entity\AbstractArticlePage;
use Kunstmaan\NodeBundle\Controller\SlugActionInterface;
use Kunstmaan\NodeBundle\Entity\AbstractPage;
use Kunstmaan\NodeBundle\Helper\RenderContext;
use Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface;
use Sandbox\WebsiteBundle\Entity\IPlaceFromTo;
use Sandbox\WebsiteBundle\Entity\MapCategory;
use Sandbox\WebsiteBundle\Entity\PackageCategory;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Sandbox\WebsiteBundle\Form\Pages\PackagePageAdminType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * PackagePage
 *
 * @ORM\Table(name="sb_package_pages")
 * @ORM\Entity(repositoryClass="Sandbox\WebsiteBundle\Repository\PackagePageRepository")
 */
class PackagePage extends AbstractArticlePage implements HasPageTemplateInterface, IPlaceFromTo, SlugActionInterface
{
    public function getControllerAction()
    {
        return "SandboxWebsiteBundle:BackwardCompatibility:service";
    }

    public function service(ContainerInterface $container, Request $request, RenderContext $context)
    {
        parent::service($container, $request, $context);

        $em = $container->get('doctrine.orm.entity_manager');

        $page = $context['page'];

        $node = $em->getRepository('KunstmaanNodeBundle:Node')
            ->getNodeFor($page);

        $translation = $node->getNodeTranslation($request->getLocale());
        if(!$translation || !$translation->isOnline()){
            throw new NotFoundHttpException("Requested page is offline");
        }

        $page->viewCount += 1;
        $em->persist($page);
        $em->flush();
        $context['page'] = $page;



        $hotelNode = $node->getParent();
        $hotelPage = null;
        if($hotelNode){
            $translation = $hotelNode->getNodeTranslation($request->getLocale());
            if($translation) {
                $hotelPage = $translation->getRef($em);
            }
        }

        $context['hotel'] = $hotelPage;

        $fromDate = $request->query->get('fromdate', date('Y-m-d'));

        $context['fromdate'] = $fromDate;

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
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage")
     */
    private $company;

    /**
     * @var PlaceOverviewPage
     *
     * @ORM\ManyToOne(targetEntity="Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage")
     */
    private $country;

    /**
     * @return PlaceOverviewPage
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param PlaceOverviewPage $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return int
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param int $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * @var integer
     *
     * @ORM\Column(name="order_number", type="integer", nullable=true)
     */
    private $orderNumber;

    /**
     * @return int
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @param int $orderNumber
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @var integer
     *
     * @ORM\Column(name="package_id", type="integer", nullable=true)
     */
    private $packageId;

    /**
     * @var integer
     *
     * @ORM\Column(name="number_adults", type="integer", nullable=true)
     */
    private $numberAdults;

    /**
     * @var integer
     *
     * @ORM\Column(name="number_children", type="integer", nullable=true)
     */
    private $numberChildren;

    /**
     * @var integer
     *
     * @ORM\Column(name="duration", type="integer", nullable=true)
     */
    private $duration;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="checkin", type="string", length=255, nullable=true)
     */
    private $checkin;

    /**
     * @var string
     *
     * @ORM\Column(name="checkout", type="string", length=255, nullable=true)
     */
    private $checkout;

    /**
     * @var string
     *
     * @ORM\Column(name="minprice", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $minprice;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @var boolean
     *
     * @ORM\Column(name="bank_payment", type="boolean", nullable=true)
     */
    private $bankPayment;

    /**
     * @var boolean
     *
     * @ORM\Column(name="creditcard_payment", type="boolean", nullable=true)
     */
    private $creditcardPayment;

    /**
     * @var boolean
     *
     * @ORM\Column(name="onthespot_payment", type="boolean", nullable=true)
     */
    private $onthespotPayment;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\PackageCategory")
     * @ORM\JoinTable(name="sb_package_page_package_category",
     *   joinColumns={
     *     @ORM\JoinColumn(name="package_page_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="package_category_id", referencedColumnName="id")
     *   }
     * )
     */
    private $categories;

    /**
     * @ORM\ManyToMany(targetEntity="Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage")
     * @ORM\JoinTable(name="sb_package_place_overview")
     **/
    private $places;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->places = new ArrayCollection();
        $this->setDate(new \DateTime());
    }

    /**
     * Set packageId
     *
     * @param integer $packageId
     * @return PackagePage
     */
    public function setPackageId($packageId)
    {
        $this->packageId = $packageId;

        return $this;
    }

    /**
     * Get packageId
     *
     * @return integer 
     */
    public function getPackageId()
    {
        return $this->packageId;
    }

    /**
     * Set numberAdults
     *
     * @param integer $numberAdults
     * @return PackagePage
     */
    public function setNumberAdults($numberAdults)
    {
        $this->numberAdults = $numberAdults;

        return $this;
    }

    /**
     * Get numberAdults
     *
     * @return integer 
     */
    public function getNumberAdults()
    {
        return $this->numberAdults;
    }

    /**
     * Set numberChildren
     *
     * @param integer $numberChildren
     * @return PackagePage
     */
    public function setNumberChildren($numberChildren)
    {
        $this->numberChildren = $numberChildren;

        return $this;
    }

    /**
     * Get numberChildren
     *
     * @return integer 
     */
    public function getNumberChildren()
    {
        return $this->numberChildren;
    }

    /**
     * Set duration
     *
     * @param integer $duration
     * @return PackagePage
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration
     *
     * @return integer 
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return PackagePage
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
     * Set checkin
     *
     * @param string $checkin
     * @return PackagePage
     */
    public function setCheckin($checkin)
    {
        $this->checkin = $checkin;

        return $this;
    }

    /**
     * Get checkin
     *
     * @return string 
     */
    public function getCheckin()
    {
        return $this->checkin;
    }

    /**
     * Set checkout
     *
     * @param string $checkout
     * @return PackagePage
     */
    public function setCheckout($checkout)
    {
        $this->checkout = $checkout;

        return $this;
    }

    /**
     * Get checkout
     *
     * @return string 
     */
    public function getCheckout()
    {
        return $this->checkout;
    }

    /**
     * Set minprice
     *
     * @param string $minprice
     * @return PackagePage
     */
    public function setMinprice($minprice)
    {
        $this->minprice = $minprice;

        return $this;
    }

    /**
     * Get minprice
     *
     * @return string 
     */
    public function getMinprice()
    {
        return $this->minprice;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return PackagePage
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
     * Set bankPayment
     *
     * @param boolean $bankPayment
     * @return PackagePage
     */
    public function setBankPayment($bankPayment)
    {
        $this->bankPayment = $bankPayment;

        return $this;
    }

    /**
     * Get bankPayment
     *
     * @return boolean 
     */
    public function getBankPayment()
    {
        return $this->bankPayment;
    }

    /**
     * Set creditcardPayment
     *
     * @param boolean $creditcardPayment
     * @return PackagePage
     */
    public function setCreditcardPayment($creditcardPayment)
    {
        $this->creditcardPayment = $creditcardPayment;

        return $this;
    }

    /**
     * Get creditcardPayment
     *
     * @return boolean 
     */
    public function getCreditcardPayment()
    {
        return $this->creditcardPayment;
    }

    /**
     * Set onthespotPayment
     *
     * @param boolean $onthespotPayment
     * @return PackagePage
     */
    public function setOnthespotPayment($onthespotPayment)
    {
        $this->onthespotPayment = $onthespotPayment;

        return $this;
    }

    /**
     * Get onthespotPayment
     *
     * @return boolean 
     */
    public function getOnthespotPayment()
    {
        return $this->onthespotPayment;
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
     * Returns the default backend form type for this page
     *
     * @return PackagePageAdminType
     */
    public function getDefaultAdminType()
    {
        return new PackagePageAdminType();
    }

    /**
     * @return array
     */
    public function getPossibleChildTypes()
    {
        return array(
            array(
                'name' => 'PackageOverviewPage',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Pages\PackageOverviewPage'
            ),);
    }

    /**
     * @return string[]
     */
    public function getPagePartAdminConfigurations()
    {
        return array(
            'SandboxWebsiteBundle:main',
            'SandboxWebsiteBundle:rooms',
            'SandboxWebsiteBundle:information',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPageTemplates()
    {
        return array('SandboxWebsiteBundle:packagepage');
    }

    /**
     * Get the twig view.
     *
     * @return string
     */
    public function getDefaultView()
    {
        return 'SandboxWebsiteBundle:Package:view.html.twig';
    }

    /**
     * Get full entity name
     * @return string
     */
    public function getEntityName()
    {
        return 'Sandbox\WebsiteBundle\Entity\Pages\PackagePage';
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
     * @param PlaceOverviewPage $children
     * @return $this
     */
    public function addPlace(PlaceOverviewPage $children)
    {
        $this->places[] = $children;

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
    public function getFromPlaces()
    {
        return [];
    }

    /**
     * Remove all places
     */
    public function removeAllPlaces()
    {
        $this->places->clear();
    }

    /**
     * Remove place
     *
     * @param PlaceOverviewPage $children
     */
    public function removePlace(PlaceOverviewPage $children)
    {
        $this->places->removeElement($children);
    }

    /**
     * Remove all from places
     */
    public function removeAllFromPlaces(){}
}