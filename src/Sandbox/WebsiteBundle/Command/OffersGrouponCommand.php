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
use /** @noinspection PhpUndefinedClassInspection */
    Symfony\Component\Console\Input\ArrayInput;
use /** @noinspection PhpUndefinedClassInspection */
    Symfony\Component\Console\Input\InputInterface;
use /** @noinspection PhpUndefinedClassInspection */
    Symfony\Component\Console\Output\OutputInterface;

class OffersGrouponCommand extends ContainerAwareCommand
{
    protected $lang = 'fi';
    protected $currency = 'EUR';
    protected $rate = 1;


    protected function configure()
    {
        $this
            ->setName('travelbase:import:offersgroupon')
            ->setDescription('Import groupon offers')
        ;
    }

    protected $emailBody;

    /** @var  EntityManager */
    protected $em;
    /** @var  OutputInterface */
    protected $output;
    /** @var  CompanyOverviewPage */
    protected $company;

    protected function out($text)
    {
        $this->output->writeln($text);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->output = $output;

        $this->rate = $this->getCurrencyRate($this->currency);

        $rootNode = $this->em->getRepository('KunstmaanNodeBundle:Node')
            ->findOneBy([
                'internalName' => 'offer',
                'refEntityName' => 'Sandbox\WebsiteBundle\Entity\Pages\OffersOverviewPage',
                'deleted' => 0
            ]);

        if(!$rootNode){
            $this->out("OffersOverviewPage with internal name offers not found. Please create one.\n");
            return;
        }

        $this->setCompany();

        $offerIds = [];

        $offerPages = $this->getOfferPages();
        foreach ($offerPages as $page) {
            if(!is_numeric($page->getOfferId()))
                $offerIds[$page->getOfferId()] = $page->getOfferId();
        }

        $offers = $this->getOffers();

        for($i = 0; $i<count($offers); $i++){

            try {


                $offer = $offers[$i];

                $id = $offer->uuid;

                //remove offers that are available
                if(array_key_exists($id, $offerIds)){
                    unset($offerIds[$id]);
                }

                $offerPage = $this->offerExists($id);
                if ($offerPage) {
                    //update or something
                    $this->updateFields($offerPage, $offer);
                    $this->out('UPDATING: ' . $offerPage->getTitle());

                    $node = $this->em->getRepository('KunstmaanNodeBundle:Node')
                        ->getNodeFor($offerPage);
                    $this->addPlaces($node);
                    continue;
                }

                $offerPage = $this->setPageFields($offer);

                $this->out($offerPage->getTitle() . ' ' . ($i + 1) . '/' . count($offers));

                $translations = array();

                $langs = ['en', 'ee', 'fi', 'se', 'ru'];

                foreach ($langs as $lang) {
                    $translations[] = array('language' => $lang, 'callback' => function (OfferPage $page, NodeTranslation $translation, Seo $seo) {
                        $translation->setTitle($page->getTitle());
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
            catch(\Exception $e){
                var_dump($e->getMessage());
            }
        }




        //set offers to unpublished that are left in $offerIds
        foreach ($offerIds as $id) {
            foreach ($offerPages as $page) {
                if($page->getOfferId() == $id){
                    //unpublish page
                    $node = $this->em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($page);
                    if($node){
                        /** @var NodeTranslation[] $translations */
                        $translations = $node->getNodeTranslations();
                        if($translations){
                            foreach ($translations as $translation) {

                                /** @var OfferPage $page */
                                $page = $translation->getRef($this->em);
                                if($page){
                                    $page->setArchived(true);
                                    $this->em->persist($page);
                                }

//                                $translation->setOnline(false);
//                                $this->em->persist($translation);
                            }
                            $this->em->flush();
                        }
                    }
                    break;
                }
            }

        }


        if($this->emailBody)
        {
            $email = "Offers added/updated:<br/>" . $this->emailBody;
            EmailInfoSend::sendEmail($email, 'twp: Offers info');
        }

        $this->runTranslateCommand($input, $output);

    }

    protected function runTranslateCommand(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('travelbase:translate:offers:title');

        $arguments = array(
            'command' => 'travelbase:translate:offers:title',
            'originalLang'    => $this->lang,
        );

        $input = new ArrayInput($arguments);
        $command->run($input, $output);
    }

    protected function updateFields(OfferPage $offerPage, $offer)
    {

        $qb = $this->em->createQueryBuilder();
        $qb->update('SandboxWebsiteBundle:Pages\OfferPage', 'o');

        $update = false;

        $title = $offer->title;
        if($title != $offerPage->getTitle()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'title', $offerPage->getTitle(), $title );
            $qb->set('o.title', $qb->expr()->literal($title));
        }

//        $longTitle = $offer->filter('long_title')->text();
//        if($longTitle != $offerPage->getLongTitle()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'long_title', $offerPage->getLongTitle(), $longTitle );
//            $qb->set('o.longTitle', $qb->expr()->literal($longTitle));
//        }

        $image = $offer->largeImageUrl;
        if($image != $offerPage->getImage()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'image', $offerPage->getImage(), $image );
            $qb->set('o.image', $qb->expr()->literal($image));
        }

//        $wide_image = $offer->filter('wide_image')->text();
//        if($wide_image != $offerPage->getWideImage()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'wide_image', $offerPage->getWideImage(), $wide_image );
//            $qb->set('o.wideImage', $qb->expr()->literal($wide_image));
//        }

        $price = $offer->priceSummary->value->amount;
        $price = str_split($price, strlen($price) - 2)[0];
        if($price != $offerPage->getPrice()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'price', $offerPage->getPrice(), $price );
            $qb->set('o.price', $price);
        }

        $price_normal = $offer->priceSummary->price->amount;
        $price_normal = str_split($price_normal, strlen($price_normal) - 2)[0];
        if($price_normal != $offerPage->getPriceNormal()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'price_normal', $offerPage->getPriceNormal(), $price_normal );
            $qb->set('o.priceNormal', $price_normal);
        }

        if($this->currency != 'EUR'){
            if($offerPage->getPriceEur() != $price * $this->rate){
                $qb->set('o.priceEur', $price * $this->rate);
            }

            if($offerPage->getPriceNormalEur() != $price_normal * $this->rate){
                $qb->set('o.priceNormalEur', $price_normal * $this->rate);
            }
        }

//        $currency = $offer->filter('currency')->text();
//        if($currency != $offerPage->getCurrency()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'currency', $offerPage->getCurrency(), $currency );
//            $qb->set('o.currency', $qb->expr()->literal($currency));
//        }

//        $days = $offer->filter('days')->text();
//        if($days != $offerPage->getDays()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'days', $offerPage->getDays(), $days );
//            $qb->set('o.days', $days);
//        }

        $description = $offer->highlightsHtml;
        if($description != $offerPage->getDescription()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'description', $offerPage->getDescription(), $description );
            $qb->set('o.description', $qb->expr()->literal($description));
        }

        $long_description = $offer->highlightsHtml;
        if($long_description != $offerPage->getLongDescription()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'long_description', $offerPage->getLongDescription(), $long_description );
            $qb->set('o.longDescription', $qb->expr()->literal($long_description));
        }

        $short_description = $offer->highlightsHtml;
        if($short_description != $offerPage->getShortDescription()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'short_description', $offerPage->getShortDescription(), $short_description );
            $qb->set('o.shortDescription', $qb->expr()->literal($short_description));
        }

//        $logo = $offer->filter('logo')->text();
//        if($logo != $offerPage->getLogo()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'logo', $offerPage->getLogo(), $logo );
//            $qb->set('o.logo', $qb->expr()->literal($logo));
//        }

        $absolute_url = $offer->dealUrl;
        if($absolute_url != $offerPage->getAbsoluteUrl()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'absolute_url', $offerPage->getAbsoluteUrl(), $absolute_url );
            $qb->set('o.absoluteUrl', $qb->expr()->literal($absolute_url));
        }

//        $category = $offer->filter('category')->text();
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

        if(property_exists($offer->locations[0], 'city')) {
            $city = $offer->locations[0]->city;
            if($city != $offerPage->getCity()){
                $update = true;
                $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'city', $offerPage->getCity(), $city );
                $qb->set('o.city', $qb->expr()->literal($city));
            }
        }

//        $region = $offer->filter('region')->text();
//        if($region != $offerPage->getRegion()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'region', $offerPage->getRegion(), $region );
//            $qb->set('o.region', $qb->expr()->literal($region));
//        }

//        $transportation = $offer->filter('transportation')->text();
//        if($transportation != $offerPage->getTransportation()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'transportation', $offerPage->getTransportation(), $transportation );
//            $qb->set('o.transportation', $qb->expr()->literal($transportation));
//        }

//        $target_group = $offer->filter('target_group')->text();
//        if($target_group != $offerPage->getTargetGroup()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'target_group', $offerPage->getTargetGroup(), $target_group );
//            $qb->set('o.targetGroup', $qb->expr()->literal($target_group));
//        }

        $accomodation = $offer->locations[0]->name;
        if($accomodation != $offerPage->getAccomodation()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'accomodation', $offerPage->getAccomodation(), $accomodation );
            $qb->set('o.accomodation', $qb->expr()->literal($accomodation));
        }

//        $accomodation_type = $offer->filter('accomodation_type')->text();
//        if($accomodation_type != $offerPage->getAccomodationType()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'accomodation_type', $offerPage->getAccomodationType(), $accomodation_type );
//            $qb->set('o.accomodationType', $qb->expr()->literal($accomodation_type));
//        }

        $expiration_date = $offer->endAt;
        $expiration_date = new \DateTime($expiration_date);
        if($expiration_date->getTimestamp() != $offerPage->getExpirationDate()->getTimestamp()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'expiration_date', $offerPage->getExpirationDate()->format('d-m-Y'), $expiration_date->format("d-m-Y") );
            $qb->set('o.expirationDate', ':date');
            $qb->setParameter(':date', $expiration_date);
        }

        $offer_sold = $offer->soldQuantity;
        if($offer_sold != $offerPage->getOfferSold()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'offer_sold', $offerPage->getOfferSold(), $offer_sold );
            $qb->set('o.offerSold', $offer_sold);
        }

        $adress = $offer->locations[0]->streetAddress1;
        if($adress != $offerPage->getAdress()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'adress', $offerPage->getAdress(), $adress );
            $qb->set('o.adress', $qb->expr()->literal($adress));
        }

//        $included = $offer->filter('included')->text();
//        if($included != $offerPage->getIncluded()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'included', $offerPage->getIncluded(), $included );
//            $qb->set('o.included', $qb->expr()->literal($included));
//        }

        $latitude = $offer->locations[0]->lat;
        if($latitude != $offerPage->getLatitude()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'latitude', $offerPage->getLatitude(), $latitude );
            $qb->set('o.latitude', $qb->expr()->literal($latitude));
        }

        $longitude = $offer->locations[0]->lng;
        if($longitude != $offerPage->getLongitude()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'longitude', $offerPage->getLongitude(), $longitude );
            $qb->set('o.longitude', $qb->expr()->literal($longitude));
        }


        if($longitude && $latitude){
            $content = @file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?latlng=$latitude,$longitude&key=AIzaSyAfEHbffAkg6FoOqpCEUdgn9EGrONKiZeM");
            if($content){
                $data = json_decode($content);

                if(property_exists($data, 'results')){
                    $components = $data->results[0]->address_components;
                    foreach ($components as $component) {
                        foreach ($component->types as $type) {
                            if($type == 'country'){
                                $country = $component->long_name;
                                if($country != $offerPage->getCountry()) {
                                    $update = true;
                                    $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'country', $offerPage->getCountry(), $country);
                                    $qb->set('o.country', $qb->expr()->literal($country));
                                }
                            }
                        }
                    }
                }
            }
        }

//        $nights = $offer->filter('nights')->text();
//        if($nights != $offerPage->getNights()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'nights', $offerPage->getNights(), $nights );
//            $qb->set('o.nights', $qb->expr()->literal($nights));
//        }

//        $price_type = $offer->filter('price_type')->text();
//        if($price_type != $offerPage->getPriceType()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'price_type', $offerPage->getPriceType(), $price_type );
//            $qb->set('o.priceType', $qb->expr()->literal($price_type));
//        }

//        $price_per = $offer->filter('price_per')->text();
//        if($price_per != $offerPage->getPricePer()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'price_per', $offerPage->getPricePer(), $price_per );
//            $qb->set('o.pricePer', $qb->expr()->literal($price_per));
//        }

        $discount = $offer->priceSummary->discountPercent;
        if($discount != $offerPage->getDiscount()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'discount', $offerPage->getDiscount(), $discount );
            $qb->set('o.discount', $qb->expr()->literal($discount));
        }

//        $max_persons = $offer->filter('max_persons')->text();
//        if($max_persons != $offerPage->getMaxPersons()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'max_persons', $offerPage->getMaxPersons(), $max_persons );
//            $qb->set('o.maxPersons', $qb->expr()->literal($max_persons));
//        }

//        $min_persons = $offer->filter('min_persons')->text();
//        if($min_persons != $offerPage->getMinPersons()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'min_persons', $offerPage->getMinPersons(), $min_persons );
//            $qb->set('o.minPersons', $qb->expr()->literal($min_persons));
//        }

        $sold_out = $offer->isSoldOut;
        if($sold_out != $offerPage->getSoldOut()){
            $update = true;
            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'sold_out', $offerPage->getSoldOut(), $sold_out );
            $qb->set('o.soldOut', $qb->expr()->literal($sold_out));
        }

//        $booking_fee = $offer->filter('booking_fee')->text();
//        if($booking_fee != $offerPage->getBookingFee()){
//            $update = true;
//            $this->emailBody .= sprintf("%s updated from %s to %s<br>", 'booking_fee', $offerPage->getBookingFee(), $booking_fee );
//            $qb->set('o.bookingFee', $qb->expr()->literal($booking_fee));
//        }

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

            if($this->company) {
                $qb->set('o.company', $this->company->getId());
            }

            $query = $qb->where("o.offerId = '" . $offerPage->getOfferId() . "'")
                ->getQuery();

            $query->execute();

            $node = $this->em->getRepository('KunstmaanNodeBundle:Node')
                ->getNodeFor($offerPage);

            $this->emailBody .= "On node:" . $node->getId() . '<br/>';
        }

    }

    protected function addPlaces(Node $node)
    {

        /** @var NodeTranslation[] $translations */
        $translations = $node->getNodeTranslations(true);

        foreach ($translations as $translation) {
            $lang = $translation->getLang();
            /** @var OfferPage $page */
            $page = $translation->getRef($this->em);

            $page->removeAllPlaces();

            //set place to hotel based on city
            if($page->getCity()){
                //find place page
                $place = $this->em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
                    ->findOneBy(['title' => $page->getCity()]);
                if(!$place) {
                    $msg = 'place not found in db '. $page->getCity(). "<br>";
                    $this->out($msg);
                    $this->emailBody .= $msg;
                    break;
                }

                //get place page node
                $node2 = $this->em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($place);
                if(!$node2) {
                    $this->out('Node node found for city'. $page->getCity() . "<br>");
                    continue;
                }

                $translation = $node2->getNodeTranslation($lang, true);
                if($translation){
                    /** @var PlaceOverviewPage $placePage */
                    $placePage = $translation->getRef($this->em);
                    if($placePage){
                        $page->addPlace($placePage);
                        $this->em->persist($page);
                    }
                }
            }

            ///set country place
            if($page->getCountry()){
                //find place page
                $place = $this->em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
                    ->findOneBy(['title' => $page->getCountry()]);
                if(!$place) {
                    $msg = 'place not found in db ' . $page->getCountry() . "<br>";
                    $this->out($msg);
                    $this->emailBody .= $msg;
                    break;
                }

                //get place page node
                $node2 = $this->em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($place);
                if(!$node2) {
                    $this->out('Node node found for city'. $page->getCountry() . "\n");
                    continue;
                }

                $translation = $node2->getNodeTranslation($lang, true);
                if($translation){
                    /** @var PlaceOverviewPage $placePage */
                    $placePage = $translation->getRef($this->em);
                    if($placePage){
                        $page->setCountryPlace($placePage);
                        $this->em->persist($page);
                    }
                }
            }
        }
        $this->em->flush();

    }

    /**
     * @param $offer
     * @return OfferPage
     */
    protected function setPageFields($offer)
    {
        $offerPage = new OfferPage();

        $offerPage->setViewCount(0);

        $id = $offer->uuid;
        $offerPage->setOfferId($id);

        $title = $offer->title;
        $offerPage->setTitle($title);

//        $longTitle = $offer->filter('long_title')->text();
//        $offerPage->setLongTitle($longTitle);

        $image = $offer->largeImageUrl;
        $offerPage->setImage($image);

//        $wide_image = $offer->filter('wide_image')->text();
//        $offerPage->setWideImage($wide_image);

        $currency = $this->currency;
        $offerPage->setCurrency($currency);

        $price = $offer->priceSummary->value->amount;
        $price = str_split($price, strlen($price) - 2)[0];
        $offerPage->setPrice($price);

        $price_normal = $offer->priceSummary->price->amount;
        $price_normal = str_split($price_normal, strlen($price_normal) - 2)[0];
        $offerPage->setPriceNormal($price_normal);

        if($currency != 'EUR'){
            $offerPage->setPriceEur($price * $this->rate);
            $offerPage->setPriceNormalEur($price_normal * $this->rate);
        }

//        $days = $offer->filter('days')->text();
//        $offerPage->setDays($days);

        $description = $offer->highlightsHtml;
        $offerPage->setDescription($description);

        $long_description = $offer->highlightsHtml;
        $offerPage->getLongDescription($long_description);

        $short_description = $offer->highlightsHtml;
        $offerPage->setShortDescription($short_description);

//        $logo = $offer->filter('logo')->text();
//        $offerPage->setLogo($logo);

        $absolute_url = $offer->dealUrl;
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

//        $country = $offer->filter('country')->text();
//        $offerPage->setCountry($country);

        if(property_exists($offer->locations[0], 'city')) {
            $city = $offer->locations[0]->city;
            $offerPage->setCity($city);
        }

//        $region = $offer->filter('region')->text();
//        $offerPage->setRegion($region);

//        $transportation = $offer->filter('transportation')->text();
//        $offerPage->setTransportation($transportation);

//        $target_group = $offer->filter('target_group')->text();
//        $offerPage->setTargetGroup($target_group);

        $accomodation = $offer->locations[0]->name;
        $offerPage->setAccomodation($accomodation);

//        $accomodation_type = $offer->filter('accomodation_type')->text();
//        $offerPage->setAccomodationType($accomodation_type);

        $expiration_date = $offer->endAt;
        $offerPage->setExpirationDate(new \DateTime($expiration_date));

        $offer_sold = $offer->soldQuantity;
        $offerPage->setOfferSold($offer_sold);

        $adress = $offer->locations[0]->streetAddress1;
        $offerPage->setAdress($adress);

//        $included = $offer->filter('included')->text();
//        $offerPage->setIncluded($included);

        $latitude = $offer->locations[0]->lat;
        $offerPage->setLatitude($latitude);

        $longitude = $offer->locations[0]->lng;
        $offerPage->setLongitude($longitude);

        if($longitude && $latitude){
            $content = @file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?latlng=$latitude,$longitude&key=AIzaSyAfEHbffAkg6FoOqpCEUdgn9EGrONKiZeM");
            if($content){
                $data = json_decode($content);

                if(property_exists($data, 'results')){
                    $components = $data->results[0]->address_components;
                    foreach ($components as $component) {
                        foreach ($component->types as $type) {
                            if($type == 'country'){
                                $offerPage->setCountry($component->long_name);
                            }
                        }
                    }
                }
            }
        }

//        $nights = $offer->filter('nights')->text();
//        $offerPage->setNights($nights);

//        $price_type = $offer->filter('price_type')->text();
//        $offerPage->setPriceType($price_type);

//        $price_per = $offer->filter('price_per')->text();
//        $offerPage->setPricePer($price_per);

        $discount = $offer->priceSummary->discountPercent;
        $offerPage->setDiscount($discount);

//        $max_persons = $offer->filter('max_persons')->text();
//        $offerPage->setMaxPersons($max_persons);

//        $min_persons = $offer->filter('min_persons')->text();
//        $offerPage->setMinPersons($min_persons);

        $sold_out = $offer->isSoldOut;
        $offerPage->setSoldOut($sold_out);

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

        $offerPage->setOriginalLanguage($this->lang);

        return $offerPage;
    }

    /**
     * @param $id
     * @return null|OfferPage
     */
    protected function offerExists($id)
    {

        /** @var OfferPage[] $pages */
        $pages = $this->em->getRepository('SandboxWebsiteBundle:Pages\OfferPage')
            ->findBy(['offerId' => $id]);
        if($pages){
            foreach ($pages as $page) {
                /** @var Node $node */
                $node = $this->em->getRepository('KunstmaanNodeBundle:Node')
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
     * @return array
     */
    protected function getOffers()
    {
        $context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
        $content = @file_get_contents('https://partner-int-api.groupon.com/deals.json?tsToken=IE_AFF_0_201236_212556_0&country_code=' . strtoupper($this->lang) . '&filters=topcategory:travel', false, $context);

        if(!$content) return [];

        $data = json_decode($content);

        return $data->deals;
    }

    /**
     * @return array|OfferPage[]
     */
    private function getOfferPages()
    {
        /** @var EntityManager em */
        return $this->em->getRepository('SandboxWebsiteBundle:Pages\OfferPage')
            ->getOfferPages($this->lang, $this->lang);
    }

    /**
     * @return CompanyOverviewPage
     */
    protected function setCompany()
    {
        /** @var CompanyOverviewPage $company */
        $company = $this->em->getRepository('SandboxWebsiteBundle:Company\CompanyOverviewPage')
            ->findOneBy(['title' => 'Groupon']);

        $this->company = $company;

        if(!$company) $this->out("WARNING: company Groupon not found\n");

        return $company;
    }


    /**
     * @param $currency string 3 char code of currency that needs to convert to EUR
     * @return float
     */
    private function getCurrencyRate($currency)
    {
        if($currency == "EUR")
            return 1;

        do{
            $rate = null;
            $content = @file_get_contents('http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.xchange%20where%20pair%20in%20%28%22'.$currency.'EUR%22%29&format=json&env=store://datatables.org/alltableswithkeys&callback=');
            if($content){
                $res = json_decode($content);
                if(property_exists($res, 'query') &&
                    property_exists($res->query, 'results') &&
                    property_exists($res->query->results, 'rate') &&
                    property_exists($res->query->results->rate, 'Rate'))
                    $rate = $res->query->results->rate->Rate;
            }else{
                $content = @file_get_contents('http://rate-exchange.appspot.com/currency?from='.$currency.'&to=EUR');
                if($content) {
                    $res = json_decode($content);
                    $rate = $res->rate;
                }
            }

        }while($content == false || $rate == null);

        return $rate;
    }
}