<?php
namespace Sandbox\WebsiteBundle\Command;


use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\SeoBundle\Entity\Seo;
use Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage;
use Sandbox\WebsiteBundle\Entity\Pages\OfferPage;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

class OffersCommand extends ContainerAwareCommand
{
    protected $emailBody;

    protected function configure()
    {
        $this
            ->setName('travelbase:import:offers')
            ->setDescription('Import travelbird offers')
        ;
    }

    protected $company;

    protected function getId(Crawler $offer)
    {
        return $offer->filter('id')->text();
    }

    protected function getMetaDesc(Crawler $offer)
    {
        return $offer->filter('meta_description')->text();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $rootNode = $em->getRepository('KunstmaanNodeBundle:Node')
            ->findOneBy([
                'internalName' => 'offer',
                'refEntityName' => 'Sandbox\WebsiteBundle\Entity\Pages\OffersOverviewPage',
                'deleted' => 0
            ]);

        if(!$rootNode){
            echo("OffersOverviewPage with internal name offers not found. Please create one.\n");
            return;
        }

        $this->setCompany();

        $offers = $this->getOffers();

        for($i = 0; $i<$offers->count(); $i++){
            $offer = $offers->eq($i);

            $id = $this->getId($offer);

            $offerPage = $this->offerExists($id);
            if($offerPage){
                //update or something

                echo($offerPage->getOfferId() . "\n");

                continue;
            }

            $offerPage = $this->setPageFields($offer);

            echo($offerPage->getTitle() . ' ' . ($i + 1) . '/' . $offers->count() . "\n");

            $meta_description = $this->getMetaDesc($offer);

            $translations = array();

            $langs = ['en', 'ee', 'fi', 'se', 'ru'];

            foreach ($langs as $lang) {
                $translations[] = array('language' => $lang, 'callback' => function(OfferPage $page,NodeTranslation $translation, Seo $seo) use ($meta_description) {
                    $translation->setTitle($page->getTitle());
                    $seo->setMetaDescription($meta_description);
                });
            }

            $options = array(
                'parent' => $rootNode,
                'set_online' => true,
                'hidden_from_nav' => false,
                'creator' => 'Admin'
            );
            $newNode = $this->getContainer()->get('kunstmaan_node.page_creator_service')->createPage($offerPage, $translations, $options);

            $this->addPlaces($newNode);

            $this->emailBody .= "New Offer node: ({$newNode->getId()}) title: " . $offerPage->getTitle();

        }


        if($this->emailBody)
        {
            $email = "Offers added/updated:<br/>" . $this->emailBody;
            EmailInfoSend::sendEmail($email, 'twp: Offers info');
        }

    }

    /**
     * @return Crawler
     */
    protected function getOffers()
    {
        $context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
        $crawler = new Crawler(@file_get_contents('http://travelbird.fi/data/travelwebpartner/all_active_extended.xml', false, $context));
        return $crawler->filter('offer');
    }

    /**
     * @param $offer
     * @return OfferPage
     */
    protected function setPageFields(Crawler $offer)
    {
        $offerPage = new OfferPage();

        $id = $offer->filter('id')->text();
        $offerPage->setOfferId($id);

        $title = $offer->filter('title')->text();
        $offerPage->setTitle($title);

        $longTitle = $offer->filter('long_title')->text();
        $offerPage->setLongTitle($longTitle);

        $image = $offer->filter('image')->text();
        $offerPage->setImage($image);

        $wide_image = $offer->filter('wide_image')->text();
        $offerPage->setWideImage($wide_image);

        $price = $offer->filter('price')->text();
        $offerPage->setPrice($price);

        $price_normal = $offer->filter('price_normal')->text();
        $offerPage->setPriceNormal($price_normal);

        $currency = $offer->filter('currency')->text();
        $offerPage->setCurrency($currency);

        $days = $offer->filter('days')->text();
        $offerPage->setDays($days);

        $description = $offer->filter('description')->text();
        $offerPage->setDescription($description);

        $long_description = $offer->filter('long_description')->text();
        $offerPage->getLongDescription($long_description);

        $short_description = $offer->filter('short_description')->text();
        $offerPage->setShortDescription($short_description);

        $logo = $offer->filter('logo')->text();
        $offerPage->setLogo($logo);

        $absolute_url = $offer->filter('absolute_url')->text();
        $offerPage->setAbsoluteUrl($absolute_url);

        $category = $offer->filter('category')->text();
        $offerPage->setCategory($category);

        $country = $offer->filter('country')->text();
        $offerPage->setCountry($country);

        $city = $offer->filter('city')->text();
        $offerPage->setCity($city);

        $region = $offer->filter('region')->text();
        $offerPage->setRegion($region);

        $transportation = $offer->filter('transportation')->text();
        $offerPage->setTransportation($transportation);

        $target_group = $offer->filter('target_group')->text();
        $offerPage->setTargetGroup($target_group);

        $accomodation = $offer->filter('accomodation')->text();
        $offerPage->setAccomodation($accomodation);

        $accomodation_type = $offer->filter('accomodation_type')->text();
        $offerPage->setAccomodationType($accomodation_type);

        $expiration_date = $offer->filter('expiration_date')->text();
        $offerPage->setExpirationDate(new \DateTime($expiration_date));

        $offer_sold = $offer->filter('offer_sold')->text();
        $offerPage->setOfferSold($offer_sold);

        $adress = $offer->filter('adress')->text();
        $offerPage->setAdress($adress);

        $included = $offer->filter('included')->text();
        $offerPage->setIncluded($included);

        $latitude = $offer->filter('latitude')->text();
        $offerPage->setLatitude($latitude);

        $longitude = $offer->filter('longitude')->text();
        $offerPage->setLongitude($longitude);

        $nights = $offer->filter('nights')->text();
        $offerPage->setNights($nights);

        $price_type = $offer->filter('price_type')->text();
        $offerPage->setPriceType($price_type);

        $price_per = $offer->filter('price_per')->text();
        $offerPage->setPricePer($price_per);

        $discount = $offer->filter('discount')->text();
        $offerPage->setDiscount($discount);

        $max_persons = $offer->filter('max_persons')->text();
        $offerPage->setMaxPersons($max_persons);

        $min_persons = $offer->filter('min_persons')->text();
        $offerPage->setMinPersons($min_persons);

        $sold_out = $offer->filter('sold_out')->text();
        $offerPage->setSoldOut($sold_out);

        $booking_fee = $offer->filter('booking_fee')->text();
        $offerPage->setBookingFee($booking_fee);

        $extra = [];

        $extra_breakfast = $offer->filter('extra_breakfast')->text();
        $extra_dinner = $offer->filter('extra_dinner')->text();
        $extra_wellness = $offer->filter('extra_wellness')->text();
        $extra_halfboard = $offer->filter('extra_halfboard')->text();
        $extra_fullboard = $offer->filter('extra_fullboard')->text();
        $extra_allinclusive = $offer->filter('extra_allinclusive')->text();
        $extra_moreinclusive = $offer->filter('extra_moreinclusive')->text();
        $extra_skipass = $offer->filter('extra_skipass')->text();

        if($extra_breakfast=='yes'){ $extra[]= '';}
        if($extra_dinner=='yes'){ $extra[]= '';}
        if($extra_wellness=='yes'){ $extra[]= '';}
        if($extra_halfboard=='yes'){ $extra[]= '';}
        if($extra_fullboard=='yes'){ $extra[]= '';}
        if($extra_allinclusive=='yes'){ $extra[]= '';}
        if($extra_moreinclusive=='yes'){ $extra[]= '';}
        if($extra_skipass=='yes'){ $extra[]= '';}

        $offerPage->setExtra(implode(', ', $extra));

        $offerPage->setCompany($this->company);

        $offerPage->setOriginalLanguage('fi');

        return $offerPage;
    }

    /**
     * @param $id
     * @return null|OfferPage
     */
    protected function offerExists($id)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        /** @var OfferPage[] $pages */
        $pages = $em->getRepository('SandboxWebsiteBundle:Pages\OfferPage')
            ->findBy(['offerId' => $id]);
        if($pages){
            foreach ($pages as $page) {
                /** @var Node $node */
                $node = $em->getRepository('KunstmaanNodeBundle:Node')
                    ->getNodeFor($page);

                //if node exists and not deleted skip
                if($node && !$node->isDeleted()) {
                    return $page;
                }
            }
        }

        return null;
    }

    /**
     * @return CompanyOverviewPage
     */
    protected function setCompany()
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var CompanyOverviewPage $company */
        $company = $em->getRepository('SandboxWebsiteBundle:Company\CompanyOverviewPage')
            ->findOneBy(['title' => 'Travelbird']);

        $this->company = $company;

        if(!$company) echo("WARNING: company Travelbird not found\n");

        return $company;
    }

    protected function addPlaces(Node $node)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        /** @var NodeTranslation[] $translations */
        $translations = $node->getNodeTranslations(true);

        foreach ($translations as $translation) {
            $lang = $translation->getLang();
            /** @var OfferPage $page */
            $page = $translation->getRef($em);

            $page->removeAllPlaces();

            //set place to hotel based on city
            if($page->getCity()){
                //find place page
                $place = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
                    ->findOneBy(['title' => $page->getCity()]);
                if(!$place) {
                    $msg = 'place not found in db '. $page->getCity(). "\n";
                    echo($msg);
                    $this->emailBody .= $msg;
                    break;
                }

                //get place page node
                $node2 = $em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($place);
                if(!$node2) {
                    echo('Node node found for city'. $page->getCity() . "\n");
                    continue;
                }

                $translation = $node2->getNodeTranslation($lang, true);
                if($translation){
                    /** @var PlaceOverviewPage $placePage */
                    $placePage = $translation->getRef($em);
                    if($placePage){
                        $page->addPlace($placePage);
                        $em->persist($page);
                    }
                }
            }

            ///set country place
            if($page->getCountry()){
                //find place page
                $place = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
                    ->findOneBy(['title' => $page->getCountry()]);
                if(!$place) {
                    $msg = 'place not found in db ' . $page->getCountry() . "\n";
                    echo($msg);
                    $this->emailBody .= $msg;
                    break;
                }

                //get place page node
                $node2 = $em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($place);
                if(!$node2) {
                    echo('Node node found for city'. $page->getCountry() . "\n");
                    continue;
                }

                $translation = $node2->getNodeTranslation($lang, true);
                if($translation){
                    /** @var PlaceOverviewPage $placePage */
                    $placePage = $translation->getRef($em);
                    if($placePage){
                        $page->setCountryPlace($placePage);
                        $em->persist($page);
                    }
                }
            }
        }
        $em->flush();

    }


}