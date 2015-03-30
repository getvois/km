<?php

namespace Sandbox\WebsiteBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Kunstmaan\NodeBundle\Entity\Node;
use Sandbox\WebsiteBundle\Entity\Host;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DropdownController extends Controller
{

    /**
     * @param Request $request
     * @return array
     *
     * @Template()
     */
    public function departureAction(Request $request){
        $lang = $request->getLocale();
        /** @var ObjectManager $em */
        $em = $this->getDoctrine()->getManager();
        $host = $this->get('hosthelper')->getHost();

        $placeNodes = $this->getCountries($lang, $host, true, true);
        return ['nodes' => $placeNodes, 'lang' => $lang, 'em' => $em];
    }

    /**
     * @param Request $request
     * @return array
     *
     * @Template()
     */
    public function destinationAction(Request $request)
    {
        $lang = $request->getLocale();
        /** @var ObjectManager $em */
        $em = $this->getDoctrine()->getManager();
        $host = $this->get('hosthelper')->getHost();

        $root = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
            ->getRoot($lang, $host);

        $topPlaces = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
            ->getByRoot($root['id'], $lang, $host);

        //get list of places ids
        $topPlacesIds = [];
        foreach ($topPlaces as $place) {
            $topPlacesIds[] = $place['id'];
        }

        $topChildren = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
            ->getByParentIds($topPlacesIds, $lang, $host);

        //get sub children ids
        $topChildrenChildrenIds = [];
        foreach ($topChildren as $topChildrenChild) {
            $topChildrenChildrenIds[] = $topChildrenChild['id'];
        }

        $topChildrenChildren = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
            ->getByParentIds($topChildrenChildrenIds, $lang, $host);


        //get sub children ids
        $topChildrenChildrenChildrenIds = [];
        foreach ($topChildrenChildren as $topChildrenChildrenChild) {
            $topChildrenChildrenChildrenIds[] = $topChildrenChildrenChild['id'];
        }

        $topChildrenChildrenChildren = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
            ->getByParentIds($topChildrenChildrenIds, $lang, $host);


        //bind children to places
        $placesNodes = [];
        foreach ($topPlaces as $place) {
            $children = [];
            foreach ($topChildren as $child) {
                if($child['parent'] == $place['id']){
                    //$children[] = $child;
                    $childrenChildren = [];
                    foreach ($topChildrenChildren as $childrenChild) {
                        if($child['id'] == $childrenChild['parent']){
                            //$childrenChildren[] = $childrenChild;
                            $childrenChildrenChildren = [];
                            foreach ($topChildrenChildrenChildren as $childrenChildrenChild) {
                                if($childrenChild['id'] == $childrenChildrenChild['parent']){
                                    $childrenChildrenChildren[] = $childrenChildrenChild;
                                }
                            }
                            $childrenChildren[] = ['parent' =>$childrenChild, 'children' => $childrenChildrenChildren];
                        }
                    }
                    $children[] = ['parent' =>$child, 'children' => $childrenChildren];
                }
            }
            $placesNodes[] = ['parent' =>$place, 'children' => $children];
        }

        $topPlaces = null;
        $topPlacesIds = null;
        $topChildren = null;
        $topChildrenChildrenIds = null;
        $topChildrenChildren = null;
        $topChildrenChildrenChildrenIds = null;
        $topChildrenChildrenChildren = null;

        //$placeNodes = [];//$this->getCountries($lang, $host);
        return ['root' => $root, 'nodes' => $placesNodes];
    }

    /**
     * @return array
     *
     * @Template()
     */
    public function companyAction(Request $request)
    {
        $lang = $request->getLocale();
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $root = $em->getRepository('SandboxWebsiteBundle:Company\CompanyPage')
            ->getRoot('companies', $lang);

        //company places
        $places = $em->getRepository('SandboxWebsiteBundle:Company\CompanyPage')
            ->getByRoot($root['id'], $lang);

        //get all places ids
        $placesIds = [];
        foreach ($places as $place) {
            $placesIds[] = $place['id'];
        }

        //get companies based on parent node id from places ids and order by parent id
        $companies = $em->getRepository('SandboxWebsiteBundle:Company\CompanyPage')
            ->getByParentIds($placesIds, $lang);

        //bind places to companies
        $companyNodes = [];
        foreach ($places as $place) {
            $children = [];
            foreach ($companies as $company) {
                if($company['parent'] == $place['id']){
                    $children[] = $company;
                }
            }
            $companyNodes[] = ['parent' =>$place, 'children' => $children];
        }

        return ['root' => $root, 'companies' => $companyNodes];
    }

    /**
     * @param Request $request
     * @return array
     *
     * @Template()
     */
    public function companySelectAction(Request $request)
    {
        $lang = $request->getLocale();
        /** @var ObjectManager $em */
        $em = $this->getDoctrine()->getManager();

        $companies = $em->getRepository('SandboxWebsiteBundle:Company\CompanyOverviewPage')
            ->getCompanies($lang);

        return ['companies' => $companies];
    }


    private function getCountries($lang, $host = null, $withPreferred = false, $full = false)
    {
        /** @var ObjectManager $em */
        $em = $this->getDoctrine()->getManager();
        $countryRootNode = $em->getRepository('KunstmaanNodeBundle:Node')
            ->findOneBy(
                [
                    'parent' => 1,
                    'refEntityName' => 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage',
                    'deleted' => 0
                ]);

        /** @var PlaceOverviewPage[] $placeOverviewPages */
        $placeOverviewPages = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
            ->getActiveOverviewPages($lang, $host, $countryRootNode->getId());

        /** @var PlaceOverviewPage[] $preferred */
        $preferred = [];
        /** @var Host $host */

        //if with preferred get preferred countries by locale
        if($withPreferred && $host && $host->getPreferredCountries()->count() > 0) {
            foreach ($host->getPreferredCountries() as $place) {
                $node = $em->getRepository('KunstmaanNodeBundle:Node')
                    ->getNodeFor($place);
                $trans = $node->getNodeTranslation($lang);
                if ($trans) {
                    $placeLocale = $trans->getRef($em);
                    $preferred[$node->getId()] = $placeLocale;
                }
            }
        }

        $preferredResult = [];

        foreach ($placeOverviewPages as $placeOverviewPage) {
            /** @var PlaceOverviewPage $placeOverviewPage */
            $nodeVersion = $em->getRepository('KunstmaanNodeBundle:NodeVersion')
                ->findOneBy(['refId' => $placeOverviewPage->getId(),
                        'refEntityName' => 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage']
                );

            //if full list
            if($full){
                //checked preferred countries
                if($preferred){
                    $added = false;
                    foreach ($preferred as $country) {
                        if($placeOverviewPage->getId() == $country->getId()) {

                            //add to preferred (to add later to front of array)
                            $preferredResult[] = $nodeVersion->getNodeTranslation()->getNode();
                            $added = true;
                            break;
                        }

                    }

                    //if was not in preferred add it
                    if(!$added){
                        $placeNodes[] = $nodeVersion->getNodeTranslation()->getNode();
                    }
                }else{
                    $placeNodes[] = $nodeVersion->getNodeTranslation()->getNode();
                }
            }else{

                if($host){
                    //if place in host
                    if ($placeOverviewPage->getHosts()->contains($host)) {

                        //check preferred country
                        if($preferred){
                            $added = false;
                            foreach ($preferred as $country) {
                                if($placeOverviewPage->getId() == $country->getId()) {

                                    //add to preferred (to add later to front of array)
                                    $preferredResult[] = $nodeVersion->getNodeTranslation()->getNode();
                                    $added = true;
                                    break;
                                }

                            }

                            //if was not in preferred add it
                            if(!$added){
                                $placeNodes[] = $nodeVersion->getNodeTranslation()->getNode();
                            }

                        }else{
                            $placeNodes[] = $nodeVersion->getNodeTranslation()->getNode();
                        }
                    }
                }else{
                    $placeNodes[] = $nodeVersion->getNodeTranslation()->getNode();
                }
            }
        }

        $preferredResult[] = null;//add null for breaker of classes in css
        $placeNodes = array_merge($preferredResult, $placeNodes);

        return $placeNodes;
    }

}
