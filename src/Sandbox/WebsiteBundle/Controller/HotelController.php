<?php

namespace Sandbox\WebsiteBundle\Controller;

use Doctrine\ORM\EntityManager;
use Sandbox\WebsiteBundle\Entity\Pages\HotelPage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class HotelController extends Controller
{

    /**
     * @Route("/hotel-filter/")
     * @param Request $request
     * @return JsonResponse
     */
    public function filterAction(Request $request)
    {
        $html = '';

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $cityId = $request->query->get('place', '-1');


        /** @var HotelPage[] $hotels */
        $hotels = $em->getRepository('SandboxWebsiteBundle:Pages\HotelPage')
            ->getHotelPages($request->getLocale());
        if(!$hotels) $hotels = [];

        if($cityId != '-1'){
            foreach ($hotels as $hotel) {
                foreach ($hotel->getPlaces() as $place) {
                    if($place->getCityId() == $cityId){
                        $html .= $this->get('templating')->render('@SandboxWebsite/Hotel/hotel.html.twig', ['hotel' => $hotel]);
                    }
                }
            }
        }else{
            foreach ($hotels as $hotel) {
                $html .= $this->get('templating')->render('@SandboxWebsite/Hotel/hotel.html.twig', ['hotel' => $hotel]);
            }
        }

        return new JsonResponse(['html' => $html]);
    }
}