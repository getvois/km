<?php
namespace Sandbox\WebsiteBundle\Command;


use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\SeoBundle\Entity\Seo;
use Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage;
use Sandbox\WebsiteBundle\Entity\PackageCategory;
use Sandbox\WebsiteBundle\Entity\Pages\OfferPage;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

class OffersUkCommand extends OffersCommand
{
    protected function configure()
    {
        $this
            ->setName('travelbase:import:offersuk')
            ->setDescription('Import travelbird uk offers')
        ;
    }

    protected function getId(Crawler $offer)
    {
        return $offer->filter('pid')->text();
    }

    protected function getMetaDesc(Crawler $offer)
    {
        return "";
    }

    /**
     * @return Crawler
     */
    protected function getOffers()
    {
        $context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
        $crawler = new Crawler(@file_get_contents('http://travelbird.co.uk/data/awinuk/all_active_extended.xml', false, $context));
        return $crawler->filter('product');
    }

    /**
     * @param $offer
     * @return OfferPage
     */
    protected function setPageFields(Crawler $offer)
    {
        $offerPage = new OfferPage();

        $id = $offer->filter('pid')->text();
        $offerPage->setOfferId($id);

        $title = $offer->filter('name')->text();
        $offerPage->setTitle($title);

        $longTitle = $offer->filter('additional long_description')->text();
        $offerPage->setLongTitle($longTitle);

        $image = $offer->filter('imgurl')->text();
        $offerPage->setImage($image);

//        $wide_image = $offer->filter('wide_image')->text();
//        $offerPage->setWideImage($wide_image);

        $price = $offer->filter('actualp')->text();
        $offerPage->setPrice($price);

        $price_normal = $offer->filter('storep')->text();
        $offerPage->setPriceNormal($price_normal);

        $currency = $offer->filter('currency')->text();
        $offerPage->setCurrency($currency);

//        $days = $offer->filter('days')->text();
//        $offerPage->setDays($days);

//        $description = $offer->filter('desc')->text();
//        $offerPage->setDescription($description);

        $long_description = $offer->filter('additional long_description')->text();
        $offerPage->getLongDescription($long_description);

        $short_description = $offer->filter('desc')->text();
        $offerPage->setShortDescription($short_description);

        $logo = $offer->filter('additional logo')->text();
        $offerPage->setLogo($logo);

        $absolute_url = $offer->filter('purl')->text();
        $offerPage->setAbsoluteUrl($absolute_url);

        $category = $offer->filter('category')->text();
        if($category){
            /** @var EntityManager $em */
            $em = $this->getContainer()->get('doctrine.orm.entity_manager');

            //exceptions
            if($category == 'wellness') $category = 'Spaa- ja lõõgastuspaketid';
            elseif($category == 'musical show or festival') $category = 'Teatri- ja kontserdipaketid';

            $cat = $em->getRepository('SandboxWebsiteBundle:PackageCategory')
                ->findOneBy(['name' => $category]);

            if(!$cat){
                $cat = new PackageCategory();
                $cat->setName($category);
                $em->persist($cat);
                $em->flush();
            }
            $offerPage->addCategory($cat);
        }

//        $category = $offer->filter('category')->text();
//        $offerPage->setCategory($category);

        $country = $offer->filter('country')->text();
        $offerPage->setCountry($country);

        $city = $offer->filter('city')->text();
        $offerPage->setCity($city);

        $region = $offer->filter('region')->text();
        $offerPage->setRegion($region);

//        $transportation = $offer->filter('transportation')->text();
//        $offerPage->setTransportation($transportation);

//        $target_group = $offer->filter('target_group')->text();
//        $offerPage->setTargetGroup($target_group);

        $accomodation = $offer->filter('brand')->text();
        $offerPage->setAccomodation($accomodation);

//        $accomodation_type = $offer->filter('accomodation_type')->text();
//        $offerPage->setAccomodationType($accomodation_type);

        $expiration_date = $offer->filter('validto')->text();
        $offerPage->setExpirationDate(new \DateTime($expiration_date));

//        $offer_sold = $offer->filter('offer_sold')->text();
//        $offerPage->setOfferSold($offer_sold);

//        $adress = $offer->filter('adress')->text();
//        $offerPage->setAdress($adress);

//        $included = $offer->filter('included')->text();
//        $offerPage->setIncluded($included);

        $latLong = $offer->filter('latlng')->text();
        if(preg_match('/,/', $latLong)) {
            list($latitude, $longitude) = explode(',', $latLong);
//        $latitude = $offer->filter('latitude')->text();
            $offerPage->setLatitude($latitude);

//        $longitude = $offer->filter('longitude')->text();
            $offerPage->setLongitude($longitude);
        }

        $nights = $offer->filter('additional long_description')->text();
        if(preg_match('/([0-9]+) Night/', $nights, $matches)){
            $nights = $matches[1];
            $offerPage->setNights($nights);
        }

//        $nights = $offer->filter('nights')->text();
//        $offerPage->setNights($nights);

//        $price_type = $offer->filter('price_type')->text();
//        $offerPage->setPriceType($price_type);

        $price_per = $offer->filter('additional type')->text();
        $offerPage->setPricePer($price_per);


        $discount = $offer->filter('promotext')->text();
        if(preg_match('/([0-9]+%)/', $discount, $matches)){
            $discount = $matches[1];
            $offerPage->setDiscount($discount);
        }

//        $discount = $offer->filter('discount')->text();
//        $offerPage->setDiscount($discount);

        $max_persons = $offer->filter('max_persons')->text();
        $offerPage->setMaxPersons($max_persons);

//        $min_persons = $offer->filter('min_persons')->text();
//        $offerPage->setMinPersons($min_persons);

//        $sold_out = $offer->filter('sold_out')->text();
//        $offerPage->setSoldOut($sold_out);

//        $booking_fee = $offer->filter('booking_fee')->text();
//        $offerPage->setBookingFee($booking_fee);

//        $extra = [];
//
//        $extra_breakfast = $offer->filter('extra_breakfast')->text();
//        $extra_dinner = $offer->filter('extra_dinner')->text();
//        $extra_wellness = $offer->filter('extra_wellness')->text();
//        $extra_halfboard = $offer->filter('extra_halfboard')->text();
//        $extra_fullboard = $offer->filter('extra_fullboard')->text();
//        $extra_allinclusive = $offer->filter('extra_allinclusive')->text();
//        $extra_moreinclusive = $offer->filter('extra_moreinclusive')->text();
//        $extra_skipass = $offer->filter('extra_skipass')->text();
//
//        if($extra_breakfast=='yes'){ $extra[]= '';}
//        if($extra_dinner=='yes'){ $extra[]= '';}
//        if($extra_wellness=='yes'){ $extra[]= '';}
//        if($extra_halfboard=='yes'){ $extra[]= '';}
//        if($extra_fullboard=='yes'){ $extra[]= '';}
//        if($extra_allinclusive=='yes'){ $extra[]= '';}
//        if($extra_moreinclusive=='yes'){ $extra[]= '';}
//        if($extra_skipass=='yes'){ $extra[]= '';}
//
//        $offerPage->setExtra(implode(', ', $extra));

        $offerPage->setCompany($this->company);

        $offerPage->setOriginalLanguage('en');

        return $offerPage;
    }
}