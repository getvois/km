<?php

namespace Sandbox\WebsiteBundle\Controller;

use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Sandbox\WebsiteBundle\Entity\Pages\OfferPage;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OfferController extends Controller
{
    /**
     * @Template()
     * @param Request $request
     * @return array
     */
    public function indexAction(Request $request)
    {
        $lang = $request->getLocale();
        $offerTypes = [];
        $content = @file_get_contents('http://api.travelwebpartner.com/api/offerType.getAll');
        if($content){
            $offerTypes = json_decode($content);
            foreach ($offerTypes as &$type) {
                $field = 'name_'.$lang;
                $type->name = $type->$field;
            }
        }

        $content = @file_get_contents('http://api.travelwebpartner.com/api/offer.getCountries');
        if(!$content)
            return [];

        $data = json_decode($content);

        $countries = [];
        foreach ($data as &$offer) {
            $field = 'name_'.$lang;
            $offer->name = $offer->$field;
            $countries[$offer->id] = $offer;
        }

        return [
            'offerTypes' => $offerTypes,
            'countries' => $countries,
        ];
    }


    /**
     * @Route("/offers.get/")
     * @param Request $request
     * @return JsonResponse
     */
    public function getOffersAction(Request $request)
    {
        $query = $this->getQuery($request);

        $content = @file_get_contents('http://api.travelwebpartner.com/app_dev.php/api/offer.filter/?'.$query);
        if(!$content)
            return new JsonResponse(['html' => '', 'total' => 0]);

        $data = json_decode($content);

        $html = $this->get('templating')->render('@SandboxWebsite/Offer/fromJson.html.twig', ['data' => $data]);

        return new JsonResponse(['html' => $html, 'total' => $data->total]);
    }

    private function getQuery(Request $request)
    {
        $query = [];
        //type //int or name;
        if($request->query->has('type')){
            $query[] = 'type='.$request->query->get('type');
        }

        //city //int or name, coma separated list
        if($request->query->has('city')){
            $query[] = 'city='.$request->query->get('city');
        }

        //company //int or name
        if($request->query->has('company')){
            $query[] = 'company='.$request->query->get('company');
        }

        //withFlights//1 or 0
        if($request->query->has('withFlights')){
            $query[] = 'withFlights='.$request->query->get('withFlights');
        }

        //price//priceFrom & priceTo
        if($request->query->has('priceFrom')){
            $query[] = 'priceFrom='.$request->query->get('priceFrom');
        }
        if($request->query->has('priceTo')){
            $query[] = 'priceTo='.$request->query->get('priceTo');
        }

        //limit//int
        if($request->query->has('limit')){
            $query[] = 'limit='.$request->query->get('limit');
        }
        //offset//int
        if($request->query->has('offset')){
            $query[] = 'offset='.$request->query->get('offset');
        }

        $query = implode("&", $query);
        return $query;
    }

    /**
     * @Template()
     * @param Request $request
     * @return array
     */
    public function getOfferPagesAction(Request $request){

        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $offers = $em->getRepository('SandboxWebsiteBundle:Pages\OfferPage')
            ->getOfferPages($request->getLocale());

        if(!$offers) $offers = [];

        return ['offers' => $offers];
    }


    /**
     * @Route("/offer-citylist/{cityId}")
     * @param Request $request
     * @param $cityId
     * @return string
     */
    public function cityListAction(Request $request, $cityId)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var OfferPage[] $offers */
        $offers = $em->getRepository('SandboxWebsiteBundle:Pages\OfferPage')
            ->getOfferPages($request->getLocale());

        if(!$offers) $offers = [];

        $places = [];


        foreach ($offers as $offer) {
            if($offer->getCountryPlace() && $offer->getCountryPlace()->getCityId() == $cityId){
                foreach ($offer->getPlaces() as $place) {
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
     * @Route("/offer-filter/")
     * @param Request $request
     * @return JsonResponse
     */
    public function filterAction(Request $request)
    {
        $html = '';
        $toPlace = $request->query->get('place', '');
        $country = $request->query->get('country', '');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var OfferPage[] $offers */
        $offers = $em->getRepository('SandboxWebsiteBundle:Pages\OfferPage')
            ->getOfferPages($request->getLocale());

        if (!$offers) $offers = [];

        if ($toPlace && $toPlace != -1) {
            $filtered = [];

            foreach ($offers as $offer) {
                foreach ($offer->getPlaces() as $place) {
                    if ($place->getCityId() == $toPlace) {
                        $filtered[] = $offer;
                        $html .= $this->get('templating')->render('SandboxWebsiteBundle:Offer:offerInline.html.twig', ['offer' => $offer]);
                    }
                }
            }
            //only country set
        } else if ($country) {
            foreach ($offers as $offer) {
                if ($country == -1 || $offer->getCountryPlace() && $offer->getCountryPlace()->getCityId() == $country) {
                    $html .= $this->get('templating')->render('SandboxWebsiteBundle:Offer:offerInline.html.twig', ['offer' => $offer]);
                }
            }
        }

        return new JsonResponse(['html' => $html]);

    }

}