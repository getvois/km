<?php

namespace Sandbox\WebsiteBundle\Controller;


use Doctrine\ORM\EntityManager;
use Sandbox\WebsiteBundle\Entity\Pages\OfferPage;
use Sandbox\WebsiteBundle\Entity\Pages\PackagePage;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

}