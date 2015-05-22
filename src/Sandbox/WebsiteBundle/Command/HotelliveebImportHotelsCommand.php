<?php

namespace Sandbox\WebsiteBundle\Command;


use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Sandbox\WebsiteBundle\Entity\HotelCriteria;
use Sandbox\WebsiteBundle\Entity\HotelImage;
use Sandbox\WebsiteBundle\Entity\Pages\HotelPage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

class HotelliveebImportHotelsCommand extends ContainerAwareCommand{
    protected function configure()
    {
        $this
            ->setName('travelbase:import:hotelliveeb:hotels')
            ->setDescription('Import hotels from hotelliveeb')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $emailBody = '';
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        //parent node for hotels
        $node = $em->getRepository('KunstmaanNodeBundle:Node')
            ->findOneBy(['internalName' => 'hotels', 'deleted' => 0]);
        if(!$node){
            var_dump('Could not find HotelOverviewPage with internal name "hotels"');
            return;
        }

        $crawler = new Crawler(file_get_contents('http://www.hotelliveeb.ee/xml.php?type=hotel'));
        $hotels = $crawler->filter('hotel');

        $pagePartCreator = $this->getContainer()->get('kunstmaan_pageparts.pagepart_creator_service');
        //create pages
        for($i=0; $i<$hotels->count(); $i++){
            $hotel = $hotels->eq($i);

            //check if already exists by hotel id
            $hotelPage = $this->hotelExists($hotel->filter('id')->first()->text());
            if($hotelPage){
                continue;
            }

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

            $emailBody .= "node: ". $newNode->getId(). " title:". $hotelPage->getTitle() . "<br/>";

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
        }

        if($emailBody){
            $email = "New Hotels added:<br/>" . $emailBody;
            EmailInfoSend::sendEmail($email, 'twp: New hotels');
        }else{
            $email = "No new data :<br/>";
            EmailInfoSend::sendEmail($email, 'twp: Hotels no new data');
        }
    }


    /**
     * @param $hotelId
     * @return null|HotelPage
     */
    private function hotelExists($hotelId)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        return $em->createQueryBuilder()
            ->select('p')
            ->from('SandboxWebsiteBundle:Pages\HotelPage', 'p')
            ->innerJoin('KunstmaanNodeBundle:NodeVersion', 'nv', 'WITH', 'nv.refId = p.id')
            ->innerJoin('KunstmaanNodeBundle:NodeTranslation', 'nt', 'WITH', 'nt.publicNodeVersion = nv.id and nt.id = nv.nodeTranslation')
            ->innerJoin('KunstmaanNodeBundle:Node', 'n', 'WITH', 'n.id = nt.node')
            ->where('n.deleted = 0')
            ->andWhere('n.hiddenFromNav = 0')
            ->andWhere('p.hotelId = :hotel')
            ->setParameter(':hotel', $hotelId)
            ->orderBy('nt.online', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
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




}