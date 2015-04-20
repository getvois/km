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
        $to = $request->query->get('to', '');

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
                    $html .= $this->get('templating')->render('@SandboxWebsite/Package/package.html.twig', ['package' => $package]);
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
    public function packageEventSourceAction($id){

        $data = ['success' => 1, 'result' => []];
        $content = @file_get_contents('http://www.hotelliveeb.ee/xml.php?type=packageresource&package='.$id);

        if(!$content) return new JsonResponse($data);

        $crawler = new Crawler($content);

        $items = $crawler->filter('resource');

        $out = [];
        for($i=0; $i<$items->count(); $i++){
            $item = $items->eq($i);

            $date = $item->filter('date')->first()->text();
            $price = $item->filter('price')->first()->text();
            $available = $item->filter('available')->first()->text();

            $out[] = array(
                'id' => $i,
                'title' => $price. "eur",
                'url' => '',
                'start' => strtotime($date . " 00:00") . '000',
                'end' => strtotime($date . "23:59") .'000'
            );
        }

        $data['result'] = $out;

        return new JsonResponse($data);
    }
}