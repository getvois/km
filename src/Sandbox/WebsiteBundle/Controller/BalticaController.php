<?php

namespace Sandbox\WebsiteBundle\Controller;


use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\Node;
use Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage;
use Sandbox\WebsiteBundle\Entity\Pages\HotelPage;
use Sandbox\WebsiteBundle\Entity\Pages\OfferPage;
use Sandbox\WebsiteBundle\Entity\Pages\PackagePage;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BalticaController extends Controller{


    /**
     * @Route("/baltica-citylist/{nodeId}")
     * @param Request $request
     * @param $nodeId
     * @return string
     */
    public function cityListAction(Request $request, $nodeId)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var PackagePage[] $packages */
        $packages = $em->getRepository('SandboxWebsiteBundle:Pages\PackagePage')
            ->getPackagePages($request->getLocale());

        if(!$packages) $packages = [];

        $places = [];

        $host = $this->get('hosthelper')->getHost();

        foreach ($packages as $package) {
//            if($package->getCountry() && $package->getCountry()->getCityId() == $cityId){
//                foreach ($package->getPlaces() as $place) {
//                    $places[$place->getId()] = $place;
//                }
//            }
            if($package->getCountry()){
                $translation = $em->getRepository('KunstmaanNodeBundle:NodeTranslation')
                    ->getNodeTranslationFor($package->getCountry());

                if($translation && $translation->isOnline()){
                    $node = $em->getRepository('KunstmaanNodeBundle:Node')
                        ->getNodeFor($package->getCountry());
                    if($host){
                        //if country in host and equal to node id
                        if($package->getCountry()->getHosts()->contains($host) && $node->getId() == $nodeId){
                            foreach ($package->getPlaces() as $place) {
                                if($place->getHosts()->contains($host)){
                                    $placeNode = $em->getRepository('KunstmaanNodeBundle:Node')
                                        ->getNodeFor($place);
                                    $places[$placeNode->getId()] = $place;
                                }
                            }
                        }
                    }else{
                        if($node->getId() == $nodeId){
                            foreach ($package->getPlaces() as $place) {
                                $placeNode = $em->getRepository('KunstmaanNodeBundle:Node')
                                    ->getNodeFor($place);
                                $places[$placeNode->getId()] = $place;
                            }
                        }
                    }
                }
            }
        }

        /** @var OfferPage[] $offers */
        $offers = $em->getRepository('SandboxWebsiteBundle:Pages\OfferPage')
            ->getOfferPages($request->getLocale());

        if(!$offers) $offers = [];

        foreach ($offers as $offer) {
            if($offer->getCountryPlace()){
                $node = $em->getRepository('KunstmaanNodeBundle:Node')
                    ->getNodeFor($offer->getCountryPlace());

                if($host){
                    //if country in host and equal to node id
                    if($offer->getCountryPlace()->getHosts()->contains($host) && $node->getId() == $nodeId){
                        foreach ($offer->getPlaces() as $place) {
                            if($place->getHosts()->contains($host)){
                                $placeNode = $em->getRepository('KunstmaanNodeBundle:Node')
                                    ->getNodeFor($place);
                                $places[$placeNode->getId()] = $place;
                            }
                        }
                    }
                }else{
                    if($node->getId() == $nodeId){
                        foreach ($offer->getPlaces() as $place) {
                            $placeNode = $em->getRepository('KunstmaanNodeBundle:Node')
                                ->getNodeFor($place);
                            $places[$placeNode->getId()] = $place;
                        }
                    }
                }
            }

//            if($offer->getCountryPlace() && $offer->getCountryPlace()->getCityId() == $cityId){
//                foreach ($offer->getPlaces() as $place) {
//                    $placeNode = $em->getRepository('KunstmaanNodeBundle:Node')
//                        ->getNodeFor($place);
//                    $places[$place->getId()] = $place;
//                }
//            }
        }

        uasort($places, function(PlaceOverviewPage $a,PlaceOverviewPage $b)
        {
            return strcmp($a->getTitle(), $b->getTitle());
        });

        $any = $this->get('translator')->trans('any', [], 'frontend');
        $html = "<option value='-1'>$any</option>";

        /** @var PlaceOverviewPage $place */
        foreach ($places as $key => $place) {
            $html .= "<option value='$key'>{$place->getTitle()}</option>";
        }

        return new Response($html);
    }


    /**
     * @Route("/baltica-package-category/{nodeId}")
     * @param Request $request
     * @param $nodeId
     * @return string
     */
    public function hotelListAction(Request $request, $nodeId)
    {
        //node id = place node id.
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();


        $categories = $em->getRepository('SandboxWebsiteBundle:PackageCategory')
            ->findAll();

        if(!$categories) $categories = [];



        $any = $this->get('translator')->trans('any', [], 'frontend');
        $html = "<option value='-1'>$any</option>";

        foreach ($categories as $category) {
            $html .= "<option value='{$category->getId()}'>{$category->getName()}</option>";
        }

        return new Response($html);


        /** @var HotelPage[] $hotels */
        $hotels = $em->getRepository('SandboxWebsiteBundle:Pages\HotelPage')
            ->getHotelPages($request->getLocale());

        if(!$hotels) $hotels = [];

        $filtered = [];

        $host = $this->get('hosthelper')->getHost();

        foreach ($hotels as $hotel) {
            foreach ($hotel->getPlaces() as $place) {
//                if($place->getCityId() == $placeId){
//                    //check if hotel has packages eg node has children
//                    $node = $em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($hotel);
//                    if($node && $node->getChildren()->count() > 0){
//                        $filtered[$hotel->getId()] = $hotel;
//                        break;
//                    }
//                }
                $node = $em->getRepository('KunstmaanNodeBundle:Node')
                    ->getNodeFor($place);

                if($host){
                    if($place->getHosts()->contains($host) && $node->getId() == $nodeId){
                        $node2 = $em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($hotel);
                        if($node2 && $node2->getChildren()->count() > 0){
                            $filtered[$hotel->getId()] = $hotel;
                            break;
                        }
                    }
                }else{
                    if($node->getId() == $nodeId){
                        $node2 = $em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($hotel);
                        if($node2 && $node2->getChildren()->count() > 0){
                            $filtered[$hotel->getId()] = $hotel;
                            break;
                        }
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
     * @Route("/baltica-filter/")
     * @param Request $request
     * @return JsonResponse
     */
    public function filterAction(Request $request){
        $pageLength = 5;
        $html = '';
        $toPlace = $request->query->get('place', '');
        $from = $request->query->get('from', '');
        $mapCategory = $request->query->get('hotel', '');//map category id
        $offset = $request->query->get('offset', 0);
        $country = $request->query->get('country', '');
        $total = 0;

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        if($mapCategory && $mapCategory != -1){
            $hotels = $em->getRepository('SandboxWebsiteBundle:Pages\HotelPage')
                ->getHotelPagesByMapCategory($request->getLocale(), $mapCategory);

            $offers = $em->getRepository('SandboxWebsiteBundle:Pages\OfferPage')
                ->getOfferPagesByMapCategory($request->getLocale(), $mapCategory);

            $companies = $em->getRepository('SandboxWebsiteBundle:Company\CompanyOverviewPage')
                ->getCompanyPagesByMapCategory($request->getLocale(), $mapCategory);
        }else{
            /** @var HotelPage[] $packages */
            $hotels = $em->getRepository('SandboxWebsiteBundle:Pages\HotelPage')
                ->getHotelPages($request->getLocale());

            $offers = $em->getRepository('SandboxWebsiteBundle:Pages\OfferPage')
                ->getOfferPages($request->getLocale());

            $companies = $em->getRepository('SandboxWebsiteBundle:Company\CompanyOverviewPage')
                ->getCompaniesWithMapCategory($request->getLocale());
        }

        //filter by place
        if($toPlace && $toPlace != -1){
            $filteredHotels = [];
            foreach ($hotels as $hotel) {
                foreach ($hotel->getPlaces() as $place) {
                    $placeNode = $em->getRepository('KunstmaanNodeBundle:Node')
                        ->getNodeFor($place);
                    if($placeNode->getId() == $toPlace){
                        $filteredHotels[] = $hotel;
                    }
                }
            }
            $hotels = $filteredHotels;

            $filteredOffers = [];
            foreach ($offers as $offer) {
                foreach ($offer->getPlaces() as $place) {
                    $placeNode = $em->getRepository('KunstmaanNodeBundle:Node')
                        ->getNodeFor($place);
                    if($placeNode->getId() == $toPlace){
                        $filteredOffers[] = $offer;
                    }
                }

            }
            $offers = $filteredOffers;

            $filteredCompanies = [];
            foreach ($companies as $company) {
                foreach ($company->getPlaces() as $place) {
                    $placeNode = $em->getRepository('KunstmaanNodeBundle:Node')
                        ->getNodeFor($place);
                    if($placeNode->getId() == $toPlace){
                        $filteredCompanies[] = $company;
                    }
                }
            }
            $companies = $filteredCompanies;


            //filter by country
        }elseif($country && $country != -1){
            $filteredHotels = [];
            foreach ($hotels as $hotel) {
                if($hotel->getCountryPlace()){
                    $placeNode = $em->getRepository('KunstmaanNodeBundle:Node')
                        ->getNodeFor($hotel->getCountryPlace());
                    if($placeNode->getId() == $country){
                        $filteredHotels[] = $hotel;
                    }
                }
            }
            $hotels = $filteredHotels;

            $filteredOffers = [];
            foreach ($offers as $offer) {
                if ($offer->getCountryPlace()) {
                    $placeNode = $em->getRepository('KunstmaanNodeBundle:Node')
                        ->getNodeFor($offer->getCountryPlace());
                    if($placeNode->getId() == $country){
                        $filteredOffers[] = $offer;
                    }
                }

            }
            $offers = $filteredOffers;


            $filteredCompanies = [];
            foreach ($companies as $company) {
                foreach ($company->getPlaces() as $place) {
                    $placeNode = $em->getRepository('KunstmaanNodeBundle:Node')
                        ->getNodeFor($place);
                    if($placeNode->getId() == $country){
                        $filteredCompanies[] = $company;
                    }
                }
            }
            $companies = $filteredCompanies;

        }

        $packages = [];

        foreach ($hotels as $hotel) {
            $hotelNode = $em->getRepository('KunstmaanNodeBundle:Node')
                ->getNodeFor($hotel);

            /** @var Node $childNode */
            foreach ($hotelNode->getChildren() as $childNode) {
                $translation = $childNode->getNodeTranslation($request->getLocale());
                if($translation){
                    $packagePage = $translation->getRef($em);
                    if($packagePage){
                        $packages[] = $packagePage;
                    }
                }
            }


        }


        $items = array_merge($packages, $offers, $companies);

        usort($items, function($a, $b){
            return strcmp($a->getTitle(), $b->getTitle());
        });

        $pages = array_slice($items, $offset, $pageLength * 3);

        foreach ($pages as $page) {

            if($page instanceof PackagePage){
                $packageDates = $this->getPackageDates($page, $from);
                $html .= $this->get('templating')->render('@SandboxWebsite/Package/packageInline.html.twig', ['package' => $page, 'dates' => $packageDates, 'fromdate' => $from]);
            }elseif($page instanceof OfferPage){
                $html .= $this->get('templating')->render('SandboxWebsiteBundle:Offer:offerInline.html.twig', ['offer' => $page]);
            }elseif($page instanceof CompanyOverviewPage){
                $html .= $this->get('templating')->render('@SandboxWebsite/Company/companyInline.html.twig', ['company' => $page]);
            }

        }
        $total += count($items);


//        $pages = array_slice($packages, $offset, $pageLength);
//        foreach ($pages as $page) {
//            $packageDates = $this->getPackageDates($page, $from);
//            $html .= $this->get('templating')->render('@SandboxWebsite/Package/packageInline.html.twig', ['package' => $page, 'dates' => $packageDates, 'fromdate' => $from]);
//        }
//        $total += count($packages);
//
//
//        $pages = array_slice($offers, $offset, $pageLength);
//        foreach ($pages as $page) {
//            $html .= $this->get('templating')->render('SandboxWebsiteBundle:Offer:offerInline.html.twig', ['offer' => $page]);
//        }
//        $total += count($offers);
//
//
//        $pages = array_slice($companies, $offset, $pageLength);
//        foreach ($pages as $page) {
//            $html .= $this->get('templating')->render('@SandboxWebsite/Company/companyInline.html.twig', ['company' => $page]);
//        }
//        $total += count($companies);


        return new JsonResponse(['total' => $total, 'html' => $html]);
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
}