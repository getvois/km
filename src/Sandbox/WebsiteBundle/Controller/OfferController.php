<?php

namespace Sandbox\WebsiteBundle\Controller;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class OfferController extends Controller
{
    /**
     * @Template()
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
        foreach ($data as &$offer) {
            if($offer->country){
                $field = 'name_'.$lang;
                $offer->country->name = $offer->country->$field;
                $countries[$offer->country->id] = $offer->country;
            }
        }

        $adapter = new ArrayAdapter($data);
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
}