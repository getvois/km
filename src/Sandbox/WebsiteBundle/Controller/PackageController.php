<?php

namespace Sandbox\WebsiteBundle\Controller;

use Doctrine\ORM\EntityManager;
use Sandbox\WebsiteBundle\Entity\Pages\PackagePage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PackageController extends Controller
{
    /**
     * @Route("/package-filter/")
     * @param Request $request
     * @return JsonResponse
     */
    public function filterAction(Request $request){

        $toPlace = $request->query->get('place', '');
        $from = $request->query->get('from', '');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var PackagePage[] $packages */
        $packages = $em->getRepository('SandboxWebsiteBundle:Pages\PackagePage')
            ->getPackagePages($request->getLocale());

        if(!$packages) $packages = [];

        $filtered = [];

        $html = '';
        foreach ($packages as $package) {
            foreach ($package->getPlaces() as $place) {
                if($toPlace == -1 || $place->getCityId() == $toPlace){
                    $filtered[] = $package;

                    //get packages from date or current date
                    $packageDates = $this->getPackageDates($package, $from);

                    $html .= $this->get('templating')->render('@SandboxWebsite/Package/packageInline.html.twig', ['package' => $package, 'dates' => $packageDates, 'fromdate' => $from]);
                }
            }
        }

        return new JsonResponse(['html' => $html]);

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
                        $out[$date] = array(
                            'id' => $i,
                            'title' => $price. "eur",
                            'url' => '',
                            'start' => strtotime($date ) . '000',
                            //'end' => strtotime($date . "20:59") .'000',
                            'price' => $price,
                            'date' => $date,
                        );
                    }
                }else{
                    $out[$date] = array(
                        'id' => $i,
                        'title' => $price. "eur",
                        'url' => '',
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
}