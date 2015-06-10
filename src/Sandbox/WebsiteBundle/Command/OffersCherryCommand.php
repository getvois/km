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

class OffersCherryCommand extends OffersCommand
{
    protected $lang = 'ee';

    protected function configure()
    {
        $this
            ->setName('travelbase:import:offerscherry')
            ->setDescription('Import cherry offers')
        ;
    }

    /**
     * @return CompanyOverviewPage
     */
    protected function setCompany()
    {
        /** @var CompanyOverviewPage $company */
        $company = $this->em->getRepository('SandboxWebsiteBundle:Company\CompanyOverviewPage')
            ->findOneBy(['title' => 'Cherry']);

        $this->company = $company;

        if(!$company) echo("WARNING: company Cherry not found\n");

        return $company;
    }

    protected function getId(Crawler $offer)
    {
        return $offer->filter('id')->text();
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
        $content = @file_get_contents('https://cherry.ee/export/travel/twp.xml', false, $context);
        if(!$content) die("cant load url https://cherry.ee/export/travel/twp.xml\n");
        $crawler = new Crawler($content);
        return $crawler->filter('deal');
    }

    /**
     * @param $offer
     * @return OfferPage
     */
    protected function setPageFields(Crawler $offer)
    {
        $offerPage = new OfferPage();

        $offerPage->setViewCount(0);

        $id = $offer->filter('id')->text();
        $offerPage->setOfferId($id);

        $title = $offer->filter('title')->text();
        $offerPage->setTitle($title);

//        $longTitle = $offer->filter('long_title')->text();
//        $offerPage->setLongTitle($longTitle);

        $image = $offer->filter('image')->text();
        $offerPage->setImage($image);

        $wide_image = $offer->filter('wide_image')->text();
        $offerPage->setWideImage($wide_image);

        $currency = $offer->filter('currency')->text();
        $offerPage->setCurrency($currency);

        $price = $offer->filter('price')->text();
        $offerPage->setPrice($price);

        $price_normal = $offer->filter('price_normal')->text();
        $offerPage->setPriceNormal($price_normal);

        if($currency != 'EUR'){
            $offerPage->setPriceEur($price * $this->rate);
            $offerPage->setPriceNormalEur($price_normal * $this->rate);
        }

//        $days = $offer->filter('days')->text();
//        $offerPage->setDays($days);

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

//        $category = $offer->filter('category')->text();
//        if($category){
//
//            $cat = $this->em->getRepository('SandboxWebsiteBundle:PackageCategory')
//                ->findOneBy(['name' => $category]);
//
//            if(!$cat){
//                $cat = new PackageCategory();
//                $cat->setName($category);
//                $this->em->persist($cat);
//                $this->em->flush();
//            }
//            $offerPage->addCategory($cat);
//        }
//        $offerPage->setCategory($category);
//        $country = $offer->filter('country')->text();
//        $offerPage->setCountry($country);

//        $city = $offer->filter('city')->text();
//        $offerPage->setCity($city);

//        $region = $offer->filter('region')->text();
//        $offerPage->setRegion($region);

//        $transportation = $offer->filter('transportation')->text();
//        $offerPage->setTransportation($transportation);

//        $target_group = $offer->filter('target_group')->text();
//        $offerPage->setTargetGroup($target_group);

//        $accomodation = $offer->filter('accomodation')->text();
//        $offerPage->setAccomodation($accomodation);

//        $accomodation_type = $offer->filter('accomodation_type')->text();
//        $offerPage->setAccomodationType($accomodation_type);

        $expiration_date = $offer->filter('expiration_date')->text();
        $offerPage->setExpirationDate(new \DateTime($expiration_date));

//        $offer_sold = $offer->filter('offer_sold')->text();
//        $offerPage->setOfferSold($offer_sold);

//        $adress = $offer->filter('adress')->text();
//        $offerPage->setAdress($adress);

//        $included = $offer->filter('included')->text();
//        $offerPage->setIncluded($included);

//        $latitude = $offer->filter('latitude')->text();
//        $offerPage->setLatitude($latitude);

//        $longitude = $offer->filter('longitude')->text();
//        $offerPage->setLongitude($longitude);

//        $nights = $offer->filter('nights')->text();
//        $offerPage->setNights($nights);

//        $price_type = $offer->filter('price_type')->text();
//        $offerPage->setPriceType($price_type);

//        $price_per = $offer->filter('price_per')->text();
//        $offerPage->setPricePer($price_per);

//        $discount = $offer->filter('discount')->text();
//        $offerPage->setDiscount($discount);

//        $max_persons = $offer->filter('max_persons')->text();
//        $offerPage->setMaxPersons($max_persons);

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

        $offerPage->setOriginalLanguage('ee');

        return $offerPage;
    }

    protected function updateFields(OfferPage $offerPage, Crawler $offer)
    {

        $qb = $this->em->createQueryBuilder();
        $qb->update('SandboxWebsiteBundle:Pages\OfferPage', 'o');

        $update = false;

        $title = $offer->filter('title')->text();
        if($title != $offerPage->getTitle()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'title', $offerPage->getTitle(), $title );
            $qb->set('o.title', $qb->expr()->literal($title));
        }

        $longTitle = $offer->filter('long_title')->text();
        if($longTitle != $offerPage->getLongTitle()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'long_title', $offerPage->getLongTitle(), $longTitle );
            $qb->set('o.longTitle', $qb->expr()->literal($longTitle));
        }

        $image = $offer->filter('image')->text();
        if($image != $offerPage->getImage()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'image', $offerPage->getImage(), $image );
            $qb->set('o.image', $qb->expr()->literal($image));
        }

        $wide_image = $offer->filter('wide_image')->text();
        if($wide_image != $offerPage->getWideImage()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'wide_image', $offerPage->getWideImage(), $wide_image );
            $qb->set('o.wideImage', $qb->expr()->literal($wide_image));
        }

        $price = $offer->filter('price')->text();
        if($price != $offerPage->getPrice()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'price', $offerPage->getPrice(), $price );
            $qb->set('o.price', $price);
        }

        $price_normal = $offer->filter('price_normal')->text();
        if($price_normal != $offerPage->getPriceNormal()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'price_normal', $offerPage->getPriceNormal(), $price_normal );
            $qb->set('o.priceNormal', $price_normal);
        }

        $currency = $offer->filter('currency')->text();
        if($currency != $offerPage->getCurrency()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'currency', $offerPage->getCurrency(), $currency );
            $qb->set('o.currency', $qb->expr()->literal($currency));
        }


        if($currency != 'EUR'){
            if($offerPage->getPriceEur() != $price * $this->rate){
                $qb->set('o.priceEur', $price * $this->rate);
            }

            if($offerPage->getPriceNormalEur() != $price_normal * $this->rate){
                $qb->set('o.priceNormalEur', $price_normal * $this->rate);
            }
        }


//        $days = $offer->filter('days')->text();
//        if($days != $offerPage->getDays()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'days', $offerPage->getDays(), $days );
//            $qb->set('o.days', $days);
//        }

        $description = $offer->filter('description')->text();
        if($description != $offerPage->getDescription()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'description', $offerPage->getDescription(), $description );
            $qb->set('o.description', $qb->expr()->literal($description));
        }

        $long_description = $offer->filter('long_description')->text();
        if($long_description != $offerPage->getLongDescription()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'long_description', $offerPage->getLongDescription(), $long_description );
            $qb->set('o.longDescription', $qb->expr()->literal($long_description));
        }

        $short_description = $offer->filter('short_description')->text();
        if($short_description != $offerPage->getShortDescription()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'short_description', $offerPage->getShortDescription(), $short_description );
            $qb->set('o.shortDescription', $qb->expr()->literal($short_description));
        }

        $logo = $offer->filter('logo')->text();
        if($logo != $offerPage->getLogo()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'logo', $offerPage->getLogo(), $logo );
            $qb->set('o.logo', $qb->expr()->literal($logo));
        }

        $absolute_url = $offer->filter('absolute_url')->text();
        if($absolute_url != $offerPage->getAbsoluteUrl()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'absolute_url', $offerPage->getAbsoluteUrl(), $absolute_url );
            $qb->set('o.absoluteUrl', $qb->expr()->literal($absolute_url));
        }
//        $category = $offer->filter('category')->text();
//
//        if(!$offerPage->inCategory($category)){
//            $offerPage->removeAllCategories();
//            if($category){
//
//                $cat = $this->em->getRepository('SandboxWebsiteBundle:PackageCategory')
//                    ->findOneBy(['name' => $category]);
//
//                if(!$cat){
//                    $cat = new PackageCategory();
//                    $cat->setName($category);
//                    $this->em->persist($cat);
//                    $this->em->flush();
//                }
//                $offerPage->addCategory($cat);
//            }
//        }


        //$offerPage->setCategory($category);

//        $country = $offer->filter('country')->text();
//        if($country != $offerPage->getCountry()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'country', $offerPage->getCountry(), $country );
//            $qb->set('o.country', $qb->expr()->literal($country));
//        }
//
//        $city = $offer->filter('city')->text();
//        if($city != $offerPage->getCity()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'city', $offerPage->getCity(), $city );
//            $qb->set('o.city', $qb->expr()->literal($city));
//        }
//
//        $region = $offer->filter('region')->text();
//        if($region != $offerPage->getRegion()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'region', $offerPage->getRegion(), $region );
//            $qb->set('o.region', $qb->expr()->literal($region));
//        }
//
//        $transportation = $offer->filter('transportation')->text();
//        if($transportation != $offerPage->getTransportation()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'transportation', $offerPage->getTransportation(), $transportation );
//            $qb->set('o.transportation', $qb->expr()->literal($transportation));
//        }
//
//        $target_group = $offer->filter('target_group')->text();
//        if($target_group != $offerPage->getTargetGroup()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'target_group', $offerPage->getTargetGroup(), $target_group );
//            $qb->set('o.targetGroup', $qb->expr()->literal($target_group));
//        }
//
//        $accomodation = $offer->filter('accomodation')->text();
//        if($accomodation != $offerPage->getAccomodation()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'accomodation', $offerPage->getAccomodation(), $accomodation );
//            $qb->set('o.accomodation', $qb->expr()->literal($accomodation));
//        }
//
//        $accomodation_type = $offer->filter('accomodation_type')->text();
//        if($accomodation_type != $offerPage->getAccomodationType()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'accomodation_type', $offerPage->getAccomodationType(), $accomodation_type );
//            $qb->set('o.accomodationType', $qb->expr()->literal($accomodation_type));
//        }

        $expiration_date = $offer->filter('expiration_date')->text();
        $expiration_date = new \DateTime($expiration_date);
        if($expiration_date->getTimestamp() != $offerPage->getExpirationDate()->getTimestamp()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'expiration_date', $offerPage->getExpirationDate()->format('d-m-Y'), $expiration_date->format("d-m-Y") );
            $qb->set('o.expirationDate', ':date');
            $qb->setParameter(':date', $expiration_date);
        }

//        $offer_sold = $offer->filter('offer_sold')->text();
//        if($offer_sold != $offerPage->getOfferSold()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'offer_sold', $offerPage->getOfferSold(), $offer_sold );
//            $qb->set('o.offerSold', $offer_sold);
//        }
//
//        $adress = $offer->filter('adress')->text();
//        if($adress != $offerPage->getAdress()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'adress', $offerPage->getAdress(), $adress );
//            $qb->set('o.adress', $qb->expr()->literal($adress));
//        }
//
//        $included = $offer->filter('included')->text();
//        if($included != $offerPage->getIncluded()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'included', $offerPage->getIncluded(), $included );
//            $qb->set('o.included', $qb->expr()->literal($included));
//        }
//
//        $latitude = $offer->filter('latitude')->text();
//        if($latitude != $offerPage->getLatitude()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'latitude', $offerPage->getLatitude(), $latitude );
//            $qb->set('o.latitude', $qb->expr()->literal($latitude));
//        }
//
//        $longitude = $offer->filter('longitude')->text();
//        if($longitude != $offerPage->getLongitude()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'longitude', $offerPage->getLongitude(), $longitude );
//            $qb->set('o.longitude', $qb->expr()->literal($longitude));
//        }
//
//        $nights = $offer->filter('nights')->text();
//        if($nights != $offerPage->getNights()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'nights', $offerPage->getNights(), $nights );
//            $qb->set('o.nights', $qb->expr()->literal($nights));
//        }
//
//        $price_type = $offer->filter('price_type')->text();
//        if($price_type != $offerPage->getPriceType()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'price_type', $offerPage->getPriceType(), $price_type );
//            $qb->set('o.priceType', $qb->expr()->literal($price_type));
//        }
//
//        $price_per = $offer->filter('price_per')->text();
//        if($price_per != $offerPage->getPricePer()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'price_per', $offerPage->getPricePer(), $price_per );
//            $qb->set('o.pricePer', $qb->expr()->literal($price_per));
//        }
//
//        $discount = $offer->filter('discount')->text();
//        if($discount != $offerPage->getDiscount()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'discount', $offerPage->getDiscount(), $discount );
//            $qb->set('o.discount', $qb->expr()->literal($discount));
//        }
//
//        $max_persons = $offer->filter('max_persons')->text();
//        if($max_persons != $offerPage->getMaxPersons()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'max_persons', $offerPage->getMaxPersons(), $max_persons );
//            $qb->set('o.maxPersons', $qb->expr()->literal($max_persons));
//        }
//
//        $min_persons = $offer->filter('min_persons')->text();
//        if($min_persons != $offerPage->getMinPersons()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'min_persons', $offerPage->getMinPersons(), $min_persons );
//            $qb->set('o.minPersons', $qb->expr()->literal($min_persons));
//        }
//
//        $sold_out = $offer->filter('sold_out')->text();
//        if($sold_out == 'False') $sold_out = false;
//        else $sold_out = true;
//
//        if($sold_out != $offerPage->getSoldOut()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'sold_out', $offerPage->getSoldOut(), $sold_out );
//            $qb->set('o.soldOut', $qb->expr()->literal($sold_out));
//        }
//
//        $booking_fee = $offer->filter('booking_fee')->text();
//        if($booking_fee != $offerPage->getBookingFee()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'booking_fee', $offerPage->getBookingFee(), $booking_fee );
//            $qb->set('o.bookingFee', $qb->expr()->literal($booking_fee));
//        }
//
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
//        $extras = implode(', ', $extra);
//        if($extras != $offerPage->getExtra()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'extra', $offerPage->getExtra(), $extras );
//            $qb->set('o.extra', $qb->expr()->literal($extras));
//        }

        //$offerPage->setCompany($this->company);

        //$offerPage->setOriginalLanguage('fi');

        //return $offerPage;

        if($update){

            $query = $qb->where('o.offerId = ' . $offerPage->getOfferId())
                ->getQuery();

            $query->execute();

            $node = $this->em->getRepository('KunstmaanNodeBundle:Node')
                ->getNodeFor($offerPage);

            $this->emailBody .= "On node:" . $node->getId() . '<br/>';
        }

    }


}