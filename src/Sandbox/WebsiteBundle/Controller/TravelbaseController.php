<?php

namespace Sandbox\WebsiteBundle\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\AbstractPage;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeVersion;
use Sandbox\WebsiteBundle\Entity\Article\ArticlePage;
use Sandbox\WebsiteBundle\Entity\Host;
use Sandbox\WebsiteBundle\Entity\IHostable;
use Sandbox\WebsiteBundle\Entity\News\NewsPage;
use Sandbox\WebsiteBundle\Entity\Pages\HotelPage;
use Sandbox\WebsiteBundle\Entity\Pages\OfferPage;
use Sandbox\WebsiteBundle\Entity\Pages\PackagePage;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Sandbox\WebsiteBundle\Entity\TopImage;
use Sandbox\WebsiteBundle\Helper\FullNode;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TravelbaseController extends Controller
{
    private static $randomImage;

    /**
     * @param Request $request
     * @return array
     * @Template()
     */
    public function menuAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $root = $em->getRepository('KunstmaanNodeBundle:Node')->findOneBy(
          ['parent' => null, 'deleted' => 0, 'hiddenFromNav' => 0]
        );

        if(!$root)
            return[];

        $exclude = ['countries', 'companies']; //node internal name


        $host = $this->get('hosthelper')->getHost();

        if($host && $host->getTabs()){
            //keys from tab field in Sandbox/WebsiteBundle/Form/HostAdminType.php
            $tabs = ['club', 'offer', 'package'];
            $excludeItems = array_diff($tabs, $host->getTabs());
            $exclude = array_merge($exclude, $excludeItems);
        }

        $pages = [];
        /** @var Node $node */
        foreach ($root->getChildren() as $node) {
            if(!$node->isDeleted() && !$node->isHiddenFromNav() && !in_array($node->getInternalName(), $exclude)){
                $translation = $node->getNodeTranslation($request->getLocale());
                if($translation){
                    $page = $translation->getRef($em);
                    if($page){
                        $pages[] = $page;
                    }
                }
            }
        }

        return ['pages' => $pages];
    }

    /**
     * @param Request $request
     *
     * @return array
     * @Template()
     */
    public function topTenAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $host = $this->get('hosthelper')->getHost();

        $newsRoot = $em->getRepository('SandboxWebsiteBundle:News\NewsPage')
            ->getRoot($request->getLocale());

        $articleRoot = $em->getRepository('SandboxWebsiteBundle:Article\ArticlePage')
            ->getRoot($request->getLocale());

        $news = $em->getRepository('SandboxWebsiteBundle:News\NewsPage')
            ->getNewsPagesWithImage($request->getLocale(), $host, 5, true);

        $articles = $em->getRepository('SandboxWebsiteBundle:Article\ArticlePage')
            ->getArticlePagesWithImage($request->getLocale(), $host, 5, true);

        //fill slug
        foreach ($news as &$n) {
            $n['slug'] = $newsRoot['slug'] . '/' . $n['slug'];
        }
        foreach ($articles as &$a) {
            $a['slug'] = $articleRoot['slug'] . '/' . $a['slug'];
        }

        $fullNodes = array_merge($news, $articles);

        usort($fullNodes, function ($a, $b)
        {
            /** @noinspection PhpUndefinedMethodInspection */
            if ($a['viewCount'] == $b['viewCount']) {
                return 0;
            }
            /** @noinspection PhpUndefinedMethodInspection */
            return ($a['viewCount'] < $b['viewCount']) ? 1 : -1;
        });

        return ['fullNodes' => $fullNodes];
    }

    /**
     * Get random top image
     *
     * @return TopImage
     */
    public function getRandomTopImage()
    {
        if(self::$randomImage) return self::$randomImage;

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $host = $this->get('hosthelper')->getHost();

        $qb = $em->createQueryBuilder()
            ->select('i')
            ->from('SandboxWebsiteBundle:TopImage', 'i')
            ->join('i.hosts', 'h')
            //->join('i.picture', 'p')
            //->join('i.places', 'pl')
            ->where('i.visible = 1');

//        if($host){
//            $qb->andWhere('h.name = :host')
//                ->setParameter(':host', $host->getName());
//        }

        $topImages = $qb->getQuery()->getResult();

        //$topImages = $em->getRepository('SandboxWebsiteBundle:TopImage')->findBy(['visible' => 1]);
        if(!$topImages) return null;
        $id = rand(0, count($topImages)-1);

        self::$randomImage = $topImages[$id];
        return self::$randomImage;
    }

    /**
     * Get picture url
     *
     * @return Response
     */
    public function getRandomImageUrlAction()
    {
        $image = $this->getRandomTopImage();
        if(!$image){
            return new Response("");
        }

        return new Response($image->getPicture()->getUrl());
    }

    /**
     * Get picture title
     *
     * @param Request $request
     * @param null $image
     * @return Response
     */
    public function getRandomImageTitleAction(Request $request, $image = null)
    {
        $lang = $request->getLocale();
        if(!$image)
            $image = $this->getRandomTopImage();

        if(!$image)
            return new Response("");

        $place = $this->getRandomImagePlace($lang, $image);

        if($place){
            return new Response("<a href='/$place'>" . $image->getTitle() . "</a>");

        }

        if($image->getExternal()){
            return new Response("<a href='".$image->getExternal()."'>" . $image->getTitle() . "</a>");
        }

        if(is_numeric($image->getTitle()))
            return new Response("");

        return new Response($image->getTitle());
    }

    /**
     * Get picture place
     *
     * @param string $lang
     * @param null $image
     * @return Response
     */
    public function getRandomImagePlace($lang = 'en', $image = null)
    {
        if(!$image)
            $image = $this->getRandomTopImage();

        if(!$image) return "";

        $place = $image->getPlaces();
        $place = $place->first();
        if(!$place){
            return null;
        }


        $em = $this->getDoctrine()->getManager();
        $version = $em->getRepository('KunstmaanNodeBundle:NodeVersion')
            ->findOneBy(['refId' => $place->getId(),
                'refEntityName' => 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage',
                'type' => 'public'
            ]);

        if(!$version){
            //check external url
            if($image->getExternal())
                return $image->getExternal();

            return null;
        }

        $translation = $version->getNodeTranslation()->getNode()->getNodeTranslation($lang);

        if(!$translation) return null;

        return $lang . '/' . $translation->getFullSlug();
    }


    /**
     * @param AbstractPage $place
     * @param Host $host
     *
     * @return array
     */
    public function placeInHostAction(Request $request, AbstractPage $place, Host $host)
    {
        /** @var $place IHostable */
        if($place->getHosts()->contains($host)){
            //url
            return $this->render('@SandboxWebsite/Layout/toplacelink.html.twig', ['place' => $place]);
        }else{
            if($host->getFromPlaces()->contains($place)){
                //url
                return $this->render('@SandboxWebsite/Layout/toplacelink.html.twig', ['place' => $place]);
            }else{
                //check host place children
                $em = $this->getDoctrine()->getManager();

                foreach ($host->getFromPlaces() as $fromPlace) {
                    $node = $em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($fromPlace);
                    if(!$node){//should not happen
                        //span
                        return $this->render('@SandboxWebsite/Layout/toplacespan.html.twig', ['place' => $place]);
                    }
                    $url = $this->checkChildren($place, $node, $request->getLocale(), $em);

                    if($url)
                        //url
                        return $this->render('@SandboxWebsite/Layout/toplacelink.html.twig', ['place' => $place]);
                }

                return $this->render('@SandboxWebsite/Layout/toplacespan.html.twig', ['place' => $place]);
            }
        }
    }


    private function checkChildren(AbstractPage $aPlace, Node $node, $lang, $em)
    {
        foreach ($node->getChildren() as $child) {
            /** @var $child Node */
            if($child->isDeleted()) continue;

            if($child->getRefEntityName() == 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage'){
                //check in host
                $translation = $child->getNodeTranslation($lang);
                if(!$translation){
                    continue;
                }
                if(!$translation->isOnline()) continue;
                $place = $translation->getRef($em);
                if($place->getId() == $aPlace->getId()){
                    return $place;
                }
                $this->checkChildren($aPlace, $child, $lang, $em);
            }
        }
        return null;
    }


    /**
     * @param Request $request
     *
     * @Template()
     * @return array
     */
    public function placesFooterAction(Request $request)
    {
        $root = $this->get('placeshelper')->getRoot();
        $placesNodes = $this->get('placeshelper')->getPlaces();
        return ['root' => $root, 'places' => $placesNodes];

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $host = $this->get('hosthelper')->getHost();

        $root = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
            ->getRoot($request->getLocale(), $host);

        if(!$root) return [];

        $places = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
            ->getByRoot($root['id'], $request->getLocale(), $host);

        if(!$places) return [];

        return ['root' => $root, 'places' => $places];
    }

    /**
     * @param Request $request
     * @return array
     *
     * @Template()
     */
    public function articleFooterAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $host = $this->get('hosthelper')->getHost();

        $root = $em->getRepository('SandboxWebsiteBundle:Article\ArticlePage')
            ->getRoot($request->getLocale(), $host);

        if(!$root) return [];

        $articles = $em->getRepository('SandboxWebsiteBundle:Article\ArticlePage')
            ->getArticlePages($request->getLocale(), $host, 10);

        if(!$articles) return [];

        return ['root' => $root, 'articles' => $articles];
    }

    /**
     * @param Request $request
     * @return array
     *
     * @Template()
     */
    public function companyFooterAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $root = $em->getRepository('SandboxWebsiteBundle:Company\CompanyPage')
            ->getRoot('companies', $request->getLocale());

        if(!$root) return [];

        $companies = $em->getRepository('SandboxWebsiteBundle:Company\CompanyPage')
            ->getByRoot($root['id'], $request->getLocale());

        if(!$companies) return [];

        return ['root' => $root, 'companies' => $companies];
    }


    /**
     * tab forms for header
     * @return string
     */
    public function formAction(Request $request)
    {
        $html = '';
        $twigEngine = $this->get('templating');
        $host = $this->get('hosthelper')->getHost();

        $flightAdded = false;
        if($host && $host->getTabs()){
            foreach ($host->getTabs() as $tab) {
                if($tab == 'flight.hotel' || $tab == 'flight.only'){
                    if(!$flightAdded) {
                        $html .= $twigEngine->render('@SandboxWebsite/Travelbase/form/flight.html.twig');
                        $flightAdded = true;
                    }
                }elseif($tab == 'package'){
                    $params = $this->packageFormParams($request);
                    $html .= $twigEngine->render('@SandboxWebsite/Travelbase/form/package.html.twig', $params);
                }elseif($tab == 'baltica'){
                    $params = $this->balticaFormParams($request);
                    $html .= $twigEngine->render('@SandboxWebsite/Travelbase/form/baltica.html.twig', $params);
                }elseif($tab == 'offer'){
                    $params = $this->offerFormParams($request);
                    $html .= $twigEngine->render('@SandboxWebsite/Travelbase/form/offer.html.twig', $params);
                }elseif($tab == 'club'){
                    $params = $this->offerFormParams($request);
                    $html .= $twigEngine->render('@SandboxWebsite/Travelbase/form/club.html.twig', $params);
                }elseif($tab == 'hotel'){
                    $params = $this->hotelsFormParams($request);
                    $html .= $twigEngine->render('@SandboxWebsite/Travelbase/form/hotel.html.twig', $params);
                }else{
                    $html .= $twigEngine->render('@SandboxWebsite/Travelbase/form/'.$tab.'.html.twig');
                }
            }
        }else{
            $html .= $twigEngine->render('@SandboxWebsite/Travelbase/form/flight.html.twig');

            $params = $this->offerFormParams($request);
            $html .= $twigEngine->render('@SandboxWebsite/Travelbase/form/club.html.twig', $params);
            $html .= $twigEngine->render('@SandboxWebsite/Travelbase/form/cruise.html.twig');
            $html .= $twigEngine->render('@SandboxWebsite/Travelbase/form/ferry.html.twig');

            $params = $this->hotelsFormParams($request);
            $html .= $twigEngine->render('@SandboxWebsite/Travelbase/form/hotel.html.twig', $params);

            $params = $this->offerFormParams($request);
            $html .= $twigEngine->render('@SandboxWebsite/Travelbase/form/offer.html.twig', $params);

            $params = $this->packageFormParams($request);
            $html .= $twigEngine->render('@SandboxWebsite/Travelbase/form/package.html.twig', $params);

            $params = $this->balticaFormParams($request);
            $html .= $twigEngine->render('@SandboxWebsite/Travelbase/form/baltica.html.twig', $params);
            $html .= $twigEngine->render('@SandboxWebsite/Travelbase/form/user.html.twig');
        }

        return new Response($html);
    }


    private function balticaFormParams(Request $request)
    {
        $context = [];
        /** @var PlaceOverviewPage[] $countries */
        $countries = $this->packageFormParams($request)['countries'];

        /** @var PlaceOverviewPage[] $offerCountries */
        $offerCountries = $this->offerFormParams($request)['countries'];

        // merge arrays
        foreach ($offerCountries as $country) {
            $found = false;
            foreach ($countries as $c) {
                if($country->getId() == $c->getId()){
                    $found = true;
                    break;
                }
            }

            if(!$found){
                $countries[] = $country;
            }
        }

        uasort($countries, function(PlaceOverviewPage $a,PlaceOverviewPage $b)
        {
            return strcmp($a->getTitle(), $b->getTitle());
        });

        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository('SandboxWebsiteBundle:MapCategory')
            ->findAll();

        if(!$categories) $categories = [];

        $context['countries'] = $countries;
        $context['categories'] = $categories;

        return $context;
    }

    private function packageFormParams(Request $request)
    {
        $context = [];
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $host = $this->get('hosthelper')->getHost();

        /** @var HotelPage[] $hotels */
        $hotels = $em->getRepository('SandboxWebsiteBundle:Pages\HotelPage')
            ->getHotelPages($request->getLocale());

        if(!$hotels) $hotels = [];

        $countries = [];
        foreach ($hotels as $hotel) {

            if($host){
                if($hotel->getCountryPlace() && $hotel->getCountryPlace()->getHosts()->contains($host)){
                    $node = $em->getRepository('KunstmaanNodeBundle:Node')
                        ->getNodeFor($hotel->getCountryPlace());
                    $countries[$node->getId()] = $hotel->getCountryPlace();
                }
            }else{
                if($hotel->getCountryPlace()){
                    $node = $em->getRepository('KunstmaanNodeBundle:Node')
                        ->getNodeFor($hotel->getCountryPlace());
                    $countries[$node->getId()] = $hotel->getCountryPlace();
                }
            }

        }

        uasort($countries, function(PlaceOverviewPage $a,PlaceOverviewPage $b)
        {
            return strcmp($a->getTitle(), $b->getTitle());
        });

        $context['countries'] = $countries;

        return $context;
    }

    private function offerFormParams(Request $request)
    {
        $context = [];
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        /** @var OfferPage[] $packages */
        $packages = $em->getRepository('SandboxWebsiteBundle:Pages\OfferPage')
            ->getOfferPages($request->getLocale());

        if(!$packages) $packages = [];

        $countries = [];

        foreach ($packages as $package) {
            if($package->getCountryPlace()){
                $node = $em->getRepository('KunstmaanNodeBundle:Node')
                    ->getNodeFor($package->getCountryPlace());
                $countries[$node->getId()] = $package->getCountryPlace();
            }
        }

        uasort($countries, function(PlaceOverviewPage $a,PlaceOverviewPage $b)
        {
            return strcmp($a->getTitle(), $b->getTitle());
        });

        $context['countries'] = $countries;

        return $context;
    }

    private function hotelsFormParams(Request $request)
    {
        $context = [];
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $node = $em->getRepository('KunstmaanNodeBundle:Node')
            ->findOneBy(['deleted' => 0, 'refEntityName' => 'Sandbox\WebsiteBundle\Entity\Pages\BookingcomPage']);

        $url = 'http://www.booking.com/searchresults.html';
        if($node && $node->getNodeTranslation($request->getLocale())){
            $url = $this->generateUrl('_slug', ['url' => $node->getNodeTranslation($request->getLocale())->getFullSlug()]);
        }
        $context['formUrl'] = $url;

        $year = date('Y');
        $month = date('n');

        $dates = [];

        for($i=0; $i < 12; $i++){
            $dates[$year . '-' . $month] = $month . '-' . $year;

            $month++;
            if($month == 13){
                $month = 1;
                $year++;
            }
        }

        $context['dates'] = $dates;

        return $context;
    }

}
