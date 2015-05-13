<?php

namespace Sandbox\WebsiteBundle\Controller;

use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\Node;
use Sandbox\WebsiteBundle\Entity\Pages\HotelPage;
use Sandbox\WebsiteBundle\Entity\Pages\PackagePage;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PackageController extends Controller
{
    /**
     * @Route("/package-filter/")
     * @param Request $request
     * @return JsonResponse
     */
    public function filterAction(Request $request){
        $pageLength = 10;
        $html = '';
        $toPlace = $request->query->get('place', '');
        $from = $request->query->get('from', '');
        $hotel = $request->query->get('hotel', '');
        $offset = $request->query->get('offset', 0);

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $filtered = [];

        if($hotel && $hotel != -1){

            $hotelPage = $em->getRepository('SandboxWebsiteBundle:Pages\HotelPage')
                ->findOneBy(['hotelId' => $hotel]);

            if(!$hotelPage) return new JsonResponse(['html' => $html]);

            $node = $em->getRepository('KunstmaanNodeBundle:Node')
                ->getNodeFor($hotelPage);

            if($node->getChildren()){
                /** @var Node $packageNode */
                foreach ($node->getChildren() as $packageNode) {
                    $translation = $packageNode->getNodeTranslation($request->getLocale());
                    if($translation){
                        $packagePage = $translation->getRef($em);
                        if($packagePage){
                            $filtered[] = $packagePage;
                            //get packages from date or current date
                            //$packageDates = $this->getPackageDates($packagePage, $from);

                            //$html .= $this->get('templating')->render('@SandboxWebsite/Package/packageInline.html.twig', ['package' => $packagePage, 'dates' => $packageDates, 'fromdate' => $from]);
                        }
                    }
                }
            }

        }else{
            /** @var PackagePage[] $packages */
            $packages = $em->getRepository('SandboxWebsiteBundle:Pages\PackagePage')
                ->getPackagePages($request->getLocale());

            if(!$packages) $packages = [];

            foreach ($packages as $package) {
                foreach ($package->getPlaces() as $place) {
                    if($toPlace == -1 || $place->getCityId() == $toPlace){
                        $filtered[] = $package;

                        //get packages from date or current date
                        //$packageDates = $this->getPackageDates($package, $from);

                        //$html .= $this->get('templating')->render('@SandboxWebsite/Package/packageInline.html.twig', ['package' => $package, 'dates' => $packageDates, 'fromdate' => $from]);
                    }
                }
            }
        }

        $pages = array_slice($filtered, $offset, $pageLength);

        foreach ($pages as $page) {
            $packageDates = $this->getPackageDates($page, $from);
            $html .= $this->get('templating')->render('@SandboxWebsite/Package/packageInline.html.twig', ['package' => $page, 'dates' => $packageDates, 'fromdate' => $from]);
        }

        return new JsonResponse(['total' => count($filtered), 'html' => $html]);

    }

    /**
     * @Route("/package-event-source/{id}")
     * @param $id
     * @return JsonResponse
     */
    public function packageEventSourceAction(Request $request, $id){

        $data = ['success' => 1, 'result' => []];
        $content = @file_get_contents('http://www.hotelliveeb.ee/xml.php?type=packageresource&package='.$id);

        $from = $request->query->get('from') / 1000;
        $to = $request->query->get('to') / 1000;

        if(!$content) return new JsonResponse($data);

        $crawler = new Crawler($content);

        $items = $crawler->filter('resource');

        $out = [];
        for($i=0; $i<$items->count(); $i++){
            $item = $items->eq($i);

            $date = $item->filter('date')->first()->text();
            $price = $item->filter('price')->first()->text();
            $available = $item->filter('available')->first()->text();

            if(strtotime($date) >= $from && strtotime($date) <= $to){
                if(array_key_exists($date, $out)){
                    if($price > $out[$date]['price']){
                        $url = "https://pay.travelwebpartner.com/payment/hv/" . $request->getLocale()."/$id/$date?host=" . $request->getHost();
                        $out[$date] = array(
                            'id' => $i,
                            'title' => "<a href='$url'>$price eur</a>",
                            'url' => '',
                            'start' => strtotime($date ) . '000',
                            //'end' => strtotime($date . "20:59") .'000',
                            'price' => $price,
                            'date' => $date,
                        );
                    }
                }else{
                    $url = "https://pay.travelwebpartner.com/payment/hv/" . $request->getLocale()."/$id/$date?host=" . $request->getHost();
                    $out[$date] = array(
                        'id' => $i,
                        'title' => "<a href='$url'>$price eur</a>",
                        'url' => "",
                        'start' => strtotime($date ) . '000',
                        //'end' => strtotime($date . "20:59") .'000',
                        'price' => $price,
                        'date' => $date,
                    );
                }
            }

        }

        $out = array_values($out);

        $data['result'] = $out;

        return new JsonResponse($data);
    }

    private function getPackageDates(PackagePage $package, $from)
    {
        //set date to yesterday
        if(!$from) $from = date("Y-m-d", strtotime('-1 day'));
        else $from = date("Y-m-d", strtotime($from) - 60 * 60 * 24);

        $context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
        $content = @file_get_contents('http://www.hotelliveeb.ee/xml.php?type=packageresource&package='.$package->getPackageId(), false, $context);

        if(!$content) return [];

        $crawler = new Crawler($content);

        $items = $crawler->filter('resource');

        $out = [];
        for($i=0; $i<$items->count(); $i++){
            $item = $items->eq($i);

            $date = $item->filter('date')->first()->text();
            $price = $item->filter('price')->first()->text();
            //$available = $item->filter('available')->first()->text();

            //check if date >= from date
            if(strtotime($date) >= strtotime($from)){
                //check if in array and set bigger price
                if(array_key_exists($date, $out)){
                    if($out[$date] < $price)
                        $out[$date] = round($price);
                }else{
                    $out[$date] = round($price);
                }
            }

            //only add 7 days
            if(count($out) == 7) break;
        }

        return $out;
    }

    /**
     * @Route("/package-citylist/{cityId}")
     * @param Request $request
     * @param $cityId
     * @return string
     */
    public function cityListAction(Request $request, $cityId)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var PackagePage[] $packages */
        $packages = $em->getRepository('SandboxWebsiteBundle:Pages\PackagePage')
            ->getPackagePages($request->getLocale());

        if(!$packages) $packages = [];

        $places = [];


        foreach ($packages as $package) {
            if($package->getCountry() && $package->getCountry()->getCityId() == $cityId){
                foreach ($package->getPlaces() as $place) {
                    $places[$place->getId()] = $place;
                }
            }
        }

        $any = $this->get('translator')->trans('any', [], 'frontend');
        $html = "<option value='-1'>$any</option>";

        /** @var PlaceOverviewPage $place */
        foreach ($places as $place) {
            $html .= "<option value='{$place->getCityId()}'>{$place->getTitle()}</option>";
        }

        return new Response($html);
    }


    /**
     * @Route("/package-hotellist/{placeId}")
     * @param Request $request
     * @param $placeId
     * @return string
     */
    public function hotelListAction(Request $request, $placeId)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var HotelPage[] $hotels */
        $hotels = $em->getRepository('SandboxWebsiteBundle:Pages\HotelPage')
            ->getHotelPages($request->getLocale());

        if(!$hotels) $hotels = [];

        $filtered = [];

        foreach ($hotels as $hotel) {
            foreach ($hotel->getPlaces() as $place) {
                if($place->getCityId() == $placeId){
                    //check if hotel has packages eg node has children
                    $node = $em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($hotel);
                    if($node && $node->getChildren()->count() > 0){
                        $filtered[$hotel->getId()] = $hotel;
                        break;
                    }
                }
            }

        }

        $any = $this->get('translator')->trans('any', [], 'frontend');
        $html = "<option value='-1'>$any</option>";

        /** @var HotelPage $hotel */
        foreach ($filtered as $hotel) {
            $html .= "<option value='{$hotel->getHotelId()}'>{$hotel->getTitle()}</option>";
        }

        return new Response($html);
    }


    /**
     * @Template()
     * @param Request $request
     * @return array
     */
    public function getPackagesAction(Request $request){

        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        /** @var PackagePage[] $packages */
        $packages = $em->getRepository('SandboxWebsiteBundle:Pages\PackagePage')
            ->getPackagePages($request->getLocale());

        if(!$packages) $packages = [];

        $packages = array_slice($packages, 0, 9);

        return ['packages' => $packages];
    }
}