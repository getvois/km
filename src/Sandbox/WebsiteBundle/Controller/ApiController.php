<?php

namespace Sandbox\WebsiteBundle\Controller;

use Doctrine\ORM\EntityManager;
use Sandbox\WebsiteBundle\Entity\Pages\HotelPage;
use Sandbox\WebsiteBundle\Entity\Pages\PackagePage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends Controller
{

    /**
     * @Route("/test/")
     */
    public function testAction(Request $request, $_locale)
    {
        var_dump($_locale);
        return new Response("");
    }

    /**
     * @Route("/getPackage/{packageId}")
     * @param Request $request
     * @param $_locale
     * @param $packageId
     * @return JsonResponse
     */
    public function getPackage(Request $request, $_locale, $packageId)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $package = $em->getRepository('SandboxWebsiteBundle:Pages\PackagePage')
            ->getPackagePage($_locale, $packageId);

        if(!$package)return new JsonResponse([]);

        $data = $this->packageToArray($package, $request);

        return new JsonResponse($data);
    }

    /**
     * @Route("/getHotel/{hotelId}")
     * @param Request $request
     * @param $_locale
     * @param $hotelId
     * @return JsonResponse
     */
    public function getHotel(Request $request, $_locale, $hotelId)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $hotel = $em->getRepository('SandboxWebsiteBundle:Pages\HotelPage')
            ->getHotelPage($_locale, $hotelId);

        $data = $this->hotelToArray($hotel, $request);

        return new JsonResponse($data);
    }

    /**
     * @Route("/getPackageWithHotel/{packageId}")
     * @param Request $request
     * @param $_locale
     * @param $packageId
     * @return JsonResponse
     */
    public function getPackageWithHotel(Request $request, $_locale, $packageId)
    {
        $data = [];
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $package = $em->getRepository('SandboxWebsiteBundle:Pages\PackagePage')
            ->getPackagePage($_locale, $packageId);

        if(!$package)return new JsonResponse($data);

        $data['package'] = $this->packageToArray($package, $request);

        $packageNode = $em->getRepository('KunstmaanNodeBundle:Node')
            ->getNodeFor($package);

        $hotelNode = $packageNode->getParent();
        $hotelTranslation = $hotelNode->getNodeTranslation($_locale);
        if($hotelTranslation){
            $hotel = $hotelTranslation->getRef($em);
            $data['hotel'] = $this->hotelToArray($hotel, $request);
        }

        return new JsonResponse($data);
    }


    /**
     * @param $package PackagePage
     * @param Request $request
     * @return array
     */
    private function packageToArray($package, Request $request)
    {
        if(!$package) return [];

        $data = [];

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $translation = $em->getRepository('KunstmaanNodeBundle:NodeTranslation')
            ->getNodeTranslationFor($package);

        if(!$translation) return [];

        $locale = "";
        $host = $this->get('hosthelper')->getHost();
        if($host && $host->getMultiLanguage()){
            $locale = $request->getLocale() . "/";
        }

        /** @var $package PackagePage */
        $data['id'] = $package->getId();
        $data['packageId'] = $package->getPackageId();
        $data['title'] = $package->getTitleTranslated()?$package->getTitleTranslated():$package->getTitle();
        $data['duration'] = $package->getDuration();
        $data['adults'] = $package->getNumberAdults();
        $data['children'] = $package->getNumberChildren();
        $data['summary'] = $package->getSummary();
        $data['description'] = $package->getDescription();
        $data['image'] = $package->getImage();
        $data['url'] = $request->getSchemeAndHttpHost() . "/". $locale . $translation->getFullSlug();

        return $data;
    }

    /**
     * @param $hotel HotelPage
     * @param Request $request
     * @return array
     */
    private function hotelToArray($hotel, Request $request)
    {
        if(!$hotel) return [];

        $data = [];

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $translation = $em->getRepository('KunstmaanNodeBundle:NodeTranslation')
            ->getNodeTranslationFor($hotel);

        if(!$translation) return [];

        $locale = "";
        $host = $this->get('hosthelper')->getHost();
        if($host && $host->getMultiLanguage()){
            $locale = $request->getLocale() . "/";
        }

        /** @var $hotel HotelPage */
        $data['id'] = $hotel->getId();
        $data['hotelId'] = $hotel->getHotelId();
        $data['title'] = $hotel->getTitle();
        $data['street'] = $hotel->getStreet();
        $data['city'] = $hotel->getCity();
        $data['cityParish'] = $hotel->getCityParish();
        $data['country'] = $hotel->getCountry();
        $data['url'] = $request->getSchemeAndHttpHost() . "/". $locale . $translation->getFullSlug();

        return $data;
    }
}