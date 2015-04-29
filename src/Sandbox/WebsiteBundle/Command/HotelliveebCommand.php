<?php
namespace Sandbox\WebsiteBundle\Command;


use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage;
use Sandbox\WebsiteBundle\Entity\HotelCriteria;
use Sandbox\WebsiteBundle\Entity\HotelImage;
use Sandbox\WebsiteBundle\Entity\PackageCategory;
use Sandbox\WebsiteBundle\Entity\Pages\HotelPage;
use Sandbox\WebsiteBundle\Entity\Pages\PackagePage;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

class HotelliveebCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('travelbase:import:hotelliveeb')
            ->setDescription('Import hotels and packages from hotelliveeb')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $crawler = new Crawler(file_get_contents('http://www.hotelliveeb.ee/xml.php?type=hotel'));
        $hotels = $crawler->filter('hotel');
        $this->checkHotelCriterias($hotels);

        $pagePartCreator = $this->getContainer()->get('kunstmaan_pageparts.pagepart_creator_service');
        //create pages
        for($i=0; $i<$hotels->count(); $i++){
            $hotel = $hotels->eq($i);

            //check if already exists by hotel id
            $hotelPage = $this->hotelExists($hotel->filter('id')->first()->text());
            if($hotelPage){
                //update fields on existing page
                //$hotelPage = $this->setPageFields($hotel, $hotelPage);
                //$em->persist($hotelPage);
                //$em->flush();

                $node = $em->getRepository('KunstmaanNodeBundle:Node')
                    ->getNodeFor($hotelPage);

                //$this->addPlaceToHotel($node);

                //$this->addPackages($hotelPage, $hotel);
                continue;
            }

            //find placeoverviewnode based on city or city_parish
//            $city = $this->getCityFromHotel($hotel);
//            if(!$city) {
//                var_dump('city not found in xml');
//                continue;
//            }

            $node = $em->getRepository('KunstmaanNodeBundle:Node')
                ->findOneBy(['internalName' => 'hotels', 'deleted' => 0]);
            if(!$node){
                var_dump('Could not find HotelOverviewPage with internal name "hotels"');
                break;
            }

//            //find place page
//            $place = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
//                ->findOneBy(['title' => $city]);
//            if(!$place) {
//                var_dump('place not found in db '. $city);
//                continue;
//            }
//
//            //get place page node
//            $node = $em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($place);
//            if(!$node) {
//                var_dump('Node node found for city'. $city);
//                continue;
//            }

            //create page as admin
            $hotelPage = new HotelPage();
            $hotelPage->setTitle($hotel->filter('name')->first()->text());

            var_dump($hotelPage->getTitle());

            //set fields
            $hotelPage = $this->setPageFields($hotel, $hotelPage);

            $translations = array();

            $langs = ['ee', 'en', 'ru', 'fi', 'se'];

            foreach ($langs as $lang) {
                $translations[] = array('language' => $lang, 'callback' => function($page, $translation, $seo) {
                    /** @var $page HotelPage */
                    /** @var $translation NodeTranslation */
                    $translation->setTitle($page->getTitle());
                    //$translation->setSlug('satellite');
                    $translation->setWeight(20);
                });
            }

            $options = array(
                'parent' => $node,
                'set_online' => true,
                'hidden_from_nav' => false,
                'creator' => 'Admin'
            );

            //add criterias
            $criterias = $hotel->filter('criteria');

            if($criterias->count() > 0){
                for($j=0; $j<$criterias->count(); $j++){
                    $criteria = $criterias->eq($j)->text();

                    $c = $em->getRepository('SandboxWebsiteBundle:HotelCriteria')
                        ->findOneBy(['name' => $criteria]);

                    if(!$c){
                        //add new
                        $c = new HotelCriteria();
                        $c->setName($criteria);
                        $em->persist($c);
                        $em->flush();
                    }

                    $hotelPage->addCriteria($c);
                }
            }

            $newNode = $this->getContainer()->get('kunstmaan_node.page_creator_service')->createPage($hotelPage, $translations, $options);

            //$this->addPlaceToHotel($newNode);

            //add page parts to all languages
            foreach ($langs as $lang) {

                //add images and information
                // Add pageparts

                $pageparts = array();
                //add gallery

                $images = $hotel->filterXPath("hotel/images/image");

                if($images->count() > 0){

                    $imagesArr = [];

                    for($k=0;$k<$images->count();$k++){
                        $url = $images->eq($k)->text();

                        $image = new HotelImage();
                        $image->setImageUrl($url);
                        $imagesArr[] = $image;
                        $em->persist($image);
                    }

                    $em->flush();

                    $pageparts['gallery'][] = $pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Sandbox\WebsiteBundle\Entity\PageParts\HotelGalleryPagePart',
                        array(
                            'setImages' => $imagesArr,
                        )
                    );
                }

                //add info
                $info = $hotel->filter('information item');

                if($info->count() > 0){

                    for($k=0;$k<$info->count();$k++){
                        $name = $info->eq($k)->filter('name');
                        $description = $info->eq($k)->filter('description');
                        $images = $info->eq($k)->filter('images image');

                        $realName = '';
                        if($name->count() > 0){$realName = $name->first()->text();}
                        $realDescription = '';
                        if($description->count() > 0){$realDescription = $description->first()->text();}

                        $imagesArr = [];
                        if($images->count() > 0){
                            for($l=0;$l<$images->count();$l++){
                                $url = $images->eq($l)->text();

                                $image = new HotelImage();
                                $image->setImageUrl($url);
                                $imagesArr[] = $image;
                                $em->persist($image);
                            }
                        }
                        $em->flush();

                        $pageparts['information'][] = $pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Sandbox\WebsiteBundle\Entity\PageParts\HotelInformationPagePart',
                            array(
                                'setImages' => $imagesArr,
                                'setName' => $realName,
                                'setDescription' => $realDescription,
                            )
                        );
                    }

                }


                $pagePartCreator->addPagePartsToPage($newNode, $pageparts, $lang);
            }

            //$this->addPackages($hotelPage, $hotel);

            //if($i > 2) break;//todo kosmos remove after check
        }

        $this->copyCountryFromHotelToPackage();

    }

    private function checkHotelCriterias(Crawler $hotels)
    {
        $criteriaArr = [];

        for($i=0; $i<$hotels->count(); $i++){
            $hotel = $hotels->eq($i);
            $criterias = $hotel->filter('criteria');

            if($criterias->count() > 0){
                for($j=0; $j<$criterias->count(); $j++){
                    $criteria = $criterias->eq($j)->text();

                    $criteriaArr[$criteria] = $criteria;
                    //var_dump($criteria);
                }
            }
        }

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        foreach ($criteriaArr as $criteria) {

            //check if exist
            $c = $em->getRepository('SandboxWebsiteBundle:HotelCriteria')
                ->findOneBy(['name' => $criteria]);
            if($c) continue;

            //add new
            $c = new HotelCriteria();
            $c->setName($criteria);
            $em->persist($c);
            $em->flush();
        }
    }

    /**
     * @param $hotelId
     * @return null|HotelPage
     */
    private function hotelExists($hotelId)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var HotelPage[] $pages */
        $pages = $em->getRepository('SandboxWebsiteBundle:Pages\HotelPage')
            ->findBy(['hotelId' => $hotelId]);
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
     * @param $hotel
     * @param $hotelPage
     * @return mixed
     */
    private function setPageFields(Crawler $hotel,HotelPage $hotelPage)
    {
        $hotelId = $hotel->filter('id');
        if ($hotelId->count() > 0) {
            $hotelPage->setHotelId($hotelId->first()->text());
        }
        $street = $hotel->filter('street');
        if ($street->count() > 0) {
            $hotelPage->setStreet($street->first()->text());
        }
        $city = $hotel->filter('city');
        if ($city->count() > 0) {
            $hotelPage->setCity($city->first()->text());
        }
        $city_parish = $hotel->filter('city_parish');
        if ($city_parish->count() > 0) {
            $hotelPage->setCityParish($city_parish->first()->text());
        }
        $country = $hotel->filter('country');
        if ($country->count() > 0) {
            $hotelPage->setCountry($country->first()->text());
        }
        $latitude = $hotel->filter('latitude');
        if ($latitude->count() > 0 && is_numeric($latitude->first()->text())) {
            $hotelPage->setLatitude($latitude->first()->text());
        }
        $longitude = $hotel->filter('longitude');
        if ($longitude->count() > 0 && is_numeric($longitude->first()->text())) {
            $hotelPage->setLongitude($longitude->first()->text());
        }
        $short_description = $hotel->filter('short_description');
        if ($short_description->count() > 0) {
            $hotelPage->setShortDescription($short_description->first()->text());
        }
        $long_description = $hotel->filter('long_description');
        if ($long_description->count() > 0) {
            $hotelPage->setLongDescription($long_description->first()->text());
        }
        return $hotelPage;
    }


    private function addPackages(HotelPage $hotelPage, Crawler $hotel)
    {
        $hotelId = $hotelPage->getHotelId();

        $url = 'http://www.hotelliveeb.ee/xml.php?type=package&hotel=' . $hotelId;
        $content = @file_get_contents($url);
        if(!$content){
            var_dump('couldnot load: '. $url);
            return;
        }

        $crawler = new Crawler($content);
        $packages = $crawler->filter('package');

        if($packages->count() == 0) return;

        //get avaliable packages
        $packagePages = $this->getHotelPackages($hotelPage);

        $packageIds = [];
        foreach ($packagePages as $page) {
            $packageIds[$page->getPackageId()] = $page->getPackageId();
        }


        for($i=0;$i<$packages->count();$i++){
            $package = $packages->eq($i);
            $packageId = $package->filter('id')->first()->text();

            //remove packages that are available
            if(array_key_exists($packageId, $packageIds)){
                unset($packageIds[$packageId]);
            }

            $this->createPageFromPackage($package, $hotelPage, $hotel);
        }

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $hotelNode = $em->getRepository('KunstmaanNodeBundle:Node')
            ->getNodeFor($hotelPage);

        //set packages to unpublished that are left in $packageIds
        foreach ($packageIds as $id) {
            foreach ($packagePages as $page) {
                if($page->getId() == $id){
                    //unpublish page
                    $node = $em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($page);
                    if($node){
                        /** @var NodeTranslation[] $translations */
                        $translations = $node->getNodeTranslations();
                        if($translations){
                            foreach ($translations as $translation) {
                                $translation->setOnline(false);
                                $em->persist($translation);
                            }
                            $em->flush();
                        }
                    }
                    break;
                }
            }

        }


    }



    /**
     * @param Crawler $hotel
     * @return null|string
     */
    private function getCityFromHotel(Crawler $hotel)
    {
        $city = $hotel->filter('city');
        if($city->count() > 0){
            $city = $city->first()->text();
        }else{
            $city = $hotel->filter('city_parish');
            if($city->count() > 0){
                $city = $city->first()->text();
            }else{
                $city = null;
            }
        }

        return $city;
    }


    /**
     * @param $hotelPage
     * @return array|\Sandbox\WebsiteBundle\Entity\Pages\PackagePage[]
     */
    private function getHotelPackages($hotelPage)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $hotelNode = $em->getRepository('KunstmaanNodeBundle:Node')
            ->getNodeFor($hotelPage);

        if(!$hotelNode) return [];

        $dql = "SELECT p
FROM Sandbox\WebsiteBundle\Entity\Pages\PackagePage p
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeVersion nv WITH nv.refId = p.id
INNER JOIN Kunstmaan\NodeBundle\Entity\NodeTranslation nt WITH nt.publicNodeVersion = nv.id and nt.id = nv.nodeTranslation
INNER JOIN Kunstmaan\NodeBundle\Entity\Node n WITH n.id = nt.node";
        $dql .= ' WHERE n.deleted = 0
        AND n.hiddenFromNav = 0
AND n.refEntityName = \'Sandbox\WebsiteBundle\Entity\Pages\PackagePage\'
AND nt.online = 1 AND n.parent = ' . $hotelNode->getId();


        /** @var PackagePage[] $pages */
        $pages = $em->createQuery($dql)
            ->getResult();

        if(!$pages) return [];

        return $pages;
    }


    private function createPageFromPackage(Crawler $package, HotelPage $hotelPage, Crawler $hotel)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $packageId = $package->filter('id')->first()->text();
        $packagePage = $this->packageExists($packageId);
        if($packagePage){
            $this->setPackagePageFields($package, $packagePage);
            $em->persist($packagePage);
            $em->flush();

            $node = $em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($packagePage);
            $this->setPackageCompany($node);
            return;
        }

        $packagePage = new PackagePage();
        $packagePage->setTitle($package->filter('title')->first()->text());

        $node = $em->getRepository('KunstmaanNodeBundle:Node')
            ->getNodeFor($hotelPage);

        if(!$node){
            var_dump('Node for page not found');
            return;
        }

        $this->setPackagePageFields($package, $packagePage);
        $newNode = $this->createPackageTranslations($node , $packagePage, $package, $hotel);

        $this->setPackageCompany($newNode);
        $this->createPackagePageParts($package, $newNode);

    }

    private function setPackageCompany(Node $node)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        /** @var NodeTranslation[] $translations */
        $translations = $node->getNodeTranslations(true);

        foreach ($translations as $translation) {
            $lang = $translation->getLang();

            //set company to hotelliveeb
            /** @var CompanyOverviewPage $companyPage */
            $companyPage = $em->getRepository('SandboxWebsiteBundle:Company\CompanyOverviewPage')
                ->findOneBy(['title' => 'hotelliveeb']);

            if($companyPage) {
                $node = $em->getRepository('KunstmaanNodeBundle:Node')
                    ->getNodeFor($companyPage);

                if($node){
                    $tr = $node->getNodeTranslation($lang, true);
                    if($tr){
                        $packagePage = $translation->getRef($em);
                        if($packagePage) {
                            $packagePage->setCompany($companyPage);
                            $em->persist($packagePage);
                        }
                    }
                }
            }
        }
        $em->flush();

    }

    /**
     * @param $packageId
     * @return null|PackagePage
     */
    private function packageExists($packageId)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var PackagePage[] $pages */
        $pages = $em->getRepository('SandboxWebsiteBundle:Pages\PackagePage')
            ->findBy(['packageId' => $packageId]);
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


    private function setPackagePageFields(Crawler $package, PackagePage $packagePage)
    {
        $packageId = $package->filter('id');
        if ($packageId->count() > 0) {
            $packagePage->setPackageId($packageId->first()->text());
        }
        $number_adults = $package->filter('number_adults');
        if ($number_adults->count() > 0) {
            $packagePage->setNumberAdults($number_adults->first()->text());
        }
        $number_children = $package->filter('number_children');
        if ($number_children->count() > 0) {
            $packagePage->setNumberChildren($number_children->first()->text());
        }
        $duration = $package->filter('duration');
        if ($duration->count() > 0) {
            $packagePage->setDuration($duration->first()->text());
        }
        $description = $package->filter('description');
        if ($description->count() > 0) {
            $packagePage->setDescription($description->first()->text());
        }
        $checkin = $package->filter('checkin');
        if ($checkin->count() > 0) {
            $packagePage->setCheckin($checkin->first()->text());
        }
        $checkout = $package->filter('checkout');
        if ($checkout->count() > 0) {
            $packagePage->setCheckout($checkout->first()->text());
        }
        $minprice = $package->filter('minprice');
        if ($minprice->count() > 0) {
            $packagePage->setMinprice($minprice->first()->text());
        }
        $image = $package->filter('image');
        if ($image->count() > 0) {
            $packagePage->setImage($image->first()->text());
        }

        $packagePage->setOriginalLanguage('ee');

        //set payment methods
        $payment = $package->filter('paymentmethod');
        if ($payment->count() > 0) {
            for($i=0;$i<$payment->count();$i++){
                if($payment->eq($i)->text() == 'bank'){
                    $packagePage->setBankPayment(true);
                }
                if($payment->eq($i)->text() == 'creditcard'){
                    $packagePage->setCreditcardPayment(true);
                }
                if($payment->eq($i)->text() == 'onthespot'){
                    $packagePage->setOnthespotPayment(true);
                }
            }
        }
    }


    /**
     * @param $node
     * @param PackagePage $packagePage
     * @param Crawler $package
     * @param Crawler $hotel
     * @return Node
     */
    private function createPackageTranslations($node, PackagePage $packagePage, Crawler $package, Crawler $hotel)
    {
        $langs = ['ee', 'en', 'ru', 'fi', 'se'];
        $translations = [];
        foreach ($langs as $lang) {
            $translations[] = array('language' => $lang, 'callback' => function($page, $translation, $seo) {
                /** @var $page PackagePage */
                /** @var $translation NodeTranslation */
                $translation->setTitle($page->getTitle());
                $translation->setWeight(20);
            });
        }

        //add categories
        $categories = $package->filter('category');

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        foreach ($packagePage->getCategories() as $category) {
            $packagePage->removeCategory($category);
            $em->remove($category);
        }

        $em->flush();


        $added = [];
        if($categories->count() > 0){
            for($j=0; $j<$categories->count(); $j++){
                $category = $categories->eq($j)->text();

                var_dump($category);
                $c = $em->getRepository('SandboxWebsiteBundle:PackageCategory')
                    ->findOneBy(['name' => $category]);

                if(!$c){
                    //add new
                    $c = new PackageCategory();
                    $c->setName($category);
                    $em->persist($c);
                    $em->flush();
                }

                if(!array_key_exists($c->getId(), $added)){
                    $added[$c->getId()] = $c->getId();
                    $packagePage->addCategory($c);
                }

            }
        }

        $options = array(
            'parent' => $node,
            'set_online' => true,
            'hidden_from_nav' => false,
            'creator' => 'Admin'
        );

        $node = $this->getContainer()->get('kunstmaan_node.page_creator_service')->createPage($packagePage, $translations, $options);

        //bind package to place from hotel
        foreach ($langs as $lang) {
            $translation = $node->getNodeTranslation($lang, true);
            if(!$translation) continue;

            /** @var PackagePage $page */
            $page = $translation->getRef($em);
            if(!$page) continue;

            //find placeoverviewnode based on city or city_parish
            $city = $this->getCityFromHotel($hotel);

            if($city){
                //find place page
                $place = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
                    ->findOneBy(['title' => $city]);
                if($place){
                    //get place page node
                    $node2 = $em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($place);
                    if($node2) {
                        //get fresh place page
                        $placeTranslation = $node2->getNodeTranslation($lang, true);
                        if(!$placeTranslation) continue;
                        /** @var PlaceOverviewPage $placePage */
                        $placePage = $placeTranslation->getRef($em);
                        if($placePage){
                            $page->addPlace($placePage);
                            $em->persist($page);
                            $em->flush();
                        }
                    }
                }
            }
        }

        return $node;

    }


    private function createPackagePageParts(Crawler $package, Node $node)
    {
        $pagePartCreator = $this->getContainer()->get('kunstmaan_pageparts.pagepart_creator_service');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $langs = ['ee', 'en', 'ru', 'fi', 'se'];
        //add page parts to all languages
        foreach ($langs as $lang) {

            //add rooms and information
            // Add pageparts

            $pageparts = array();
            //add gallery

            $rooms = $package->filter("room");
            if($rooms->count() > 0){
                for($k=0;$k<$rooms->count();$k++){
                    $room = $rooms->eq($k);

                    $params = [];

                    $roomID = $room->filter('id');
                    if($roomID->count() > 0){
                        $params['setRoomId'] = $roomID->first()->text();
                    }
                    $roomName = $room->filter('name');
                    if($roomName->count() > 0){
                        $params['setName'] = $roomName->first()->text();
                    }
                    $roomImage = $room->filter('image');
                    if($roomImage->count() > 0){
                        $params['setImage'] = $roomImage->first()->text();
                    }
                    $roomContent = $room->filter('content');
                    if($roomContent->count() > 0){
                        $params['setContent'] = $roomContent->first()->text();
                    }

                    $pageparts['rooms'][] = $pagePartCreator
                        ->getCreatorArgumentsForPagePartAndProperties('Sandbox\WebsiteBundle\Entity\PageParts\RoomPagePart',
                            $params
                        );
                }
            }

            //add info
            $info = $package->filter('information item');

            if($info->count() > 0){

                for($k=0;$k<$info->count();$k++){
                    $name = $info->eq($k)->filter('name');
                    $description = $info->eq($k)->filter('description');
                    $image = $info->eq($k)->filter('image');

                    $realName = '';
                    if($name->count() > 0){$realName = $name->first()->text();}
                    $realDescription = '';
                    if($description->count() > 0){$realDescription = $description->first()->text();}

                    $imgArr = [];
                    if($image->count() > 0){
                        $url = $image->first()->text();
                        if($url){
                            $image = new HotelImage();
                            $image->setImageUrl($url);
                            $imgArr[] = $image;
                            $em->persist($image);
                            $em->flush();
                        }
                    }

                    $pageparts['information'][] = $pagePartCreator->getCreatorArgumentsForPagePartAndProperties('Sandbox\WebsiteBundle\Entity\PageParts\HotelInformationPagePart',
                        array(
                            'setImages' => $imgArr,
                            'setName' => $realName,
                            'setDescription' => $realDescription,
                        )
                    );
                }
            }

            $pagePartCreator->addPagePartsToPage($node, $pageparts, $lang);
        }
    }


    private function addPlaceToHotel(Node $node)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        /** @var NodeTranslation[] $translations */
        $translations = $node->getNodeTranslations(true);

        foreach ($translations as $translation) {
            $lang = $translation->getLang();
            /** @var HotelPage $page */
            $page = $translation->getRef($em);

            $page->removeAllPlaces();

            $city = '';
            if($page->getCity())$city = $page->getCity();
            elseif($page->getCityParish()) $city = $page->getCityParish();

            //set place to hotel based on city
            if($city){
                //find place page
                $place = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
                    ->findOneBy(['title' => $city]);
                if(!$place) {
                    var_dump('place not found in db '. $city);
                    break;
                }

                //get place page node
                $node2 = $em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($place);
                if(!$node2) {
                    var_dump('Node node found for city'. $city);
                    continue;
                }

                $translation = $node2->getNodeTranslation($lang, true);
                if($translation){
                    $placePage = $translation->getRef($em);
                    if($placePage){
                        $page->addPlace($placePage);
                        $em->persist($page);
                    }
                }
            }

        }
        $em->flush();

    }

    /**
     * Set Package country as its Hotel parent
     */
    private function copyCountryFromHotelToPackage()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $hotelNodes = $em->getRepository('KunstmaanNodeBundle:Node')
            ->findBy(['deleted' => 0, 'refEntityName' => 'Sandbox\WebsiteBundle\Entity\Pages\HotelPage']);

        if(!$hotelNodes) $hotelNodes = [];

        foreach ($hotelNodes as $hotelNode) {
            /** @var Node $node */
            foreach ($hotelNode->getChildren() as $node) {
                if($node->getRefEntityName() == 'Sandbox\WebsiteBundle\Entity\Pages\PackagePage'){
                    foreach ($node->getNodeTranslations(true) as $translation) {
                        //copy country to package from hotel
                        $hotelTranslation = $hotelNode->getNodeTranslation($translation->getLang());
                        if($hotelTranslation){
                            /** @var HotelPage $hotelP */
                            $hotelP = $hotelTranslation->getRef($em);
                            if($hotelP){
                                /** @var PackagePage $packagePage */
                                $packagePage = $translation->getRef($em);
                                if($packagePage){
                                    $packagePage->setCountry($hotelP->getCountryPlace());
                                    $em->persist($packagePage);
                                }
                            }
                        }
                    }
                }
            }
            $em->flush();
        }
    }
}