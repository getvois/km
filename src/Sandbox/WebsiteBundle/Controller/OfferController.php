<?php

namespace Sandbox\WebsiteBundle\Controller;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
        $content = @file_get_contents('http://api.travelwebpartner.com/app_dev.php/api/offerType.getAll');
        if($content){
            $offerTypes = json_decode($content);
            foreach ($offerTypes as &$type) {
                $field = 'name_'.$lang;
                $type->name = $type->$field;
            }
        }

        $content = @file_get_contents('http://api.travelwebpartner.com/app_dev.php/api/offer.filter/');
        if(!$content)
            return [];

        $data = json_decode($content);

        $countries = [];
        foreach ($data->offers as &$offer) {
            if($offer->country){
                $field = 'name_'.$lang;
                $offer->country->name = $offer->country->$field;
                $countries[$offer->country->id] = $offer->country;
            }
        }

        $adapter = new ArrayAdapter([]);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(9);

        $pagenumber = $request->get('page');
        if (!$pagenumber || $pagenumber < 1) {
            $pagenumber = 1;
        }
        $pagerfanta->setCurrentPage($pagenumber);

        return [
            'pagerfanta' => $pagerfanta,
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
}