<?php

namespace Sandbox\WebsiteBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
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

        $placeNodes = $this->getCountries($lang, $host);
        return ['nodes' => $placeNodes, 'lang' => $lang, 'em' => $em];
    }

    /**
     * @return array
     *
     * @Template()
     */
    public function companyAction(Request $request)
    {
        $lang = $request->getLocale();
        /** @var ObjectManager $em */
        $em = $this->getDoctrine()->getManager();

        $nodes = $em->getRepository('KunstmaanNodeBundle:Node')->
            getNodesByInternalName('companies', $lang);

        $node = $nodes?$nodes[0]:null;

        $companyNodes = [];
        //now node is company root.
        if($node) {

            $companyClass = 'Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage';

            $class = 'Sandbox\WebsiteBundle\Entity\Pages\ContentPage';

            $fullNodes = $this->get('nodehelper')
                ->getFullNodesWithParam('n.parent = :id', [':id' => $node->getId()], $class, $lang);

            foreach ($fullNodes as $node) {
                $children = $this->get('nodehelper')
                    ->getFullNodesWithParam('n.parent = :id', [":id" => $node->getNode()->getId()], $companyClass, $lang);
                $companyNodes[] = ['parent' =>$node, 'children' => $children];
            }

        }

        return [ 'companies' => $companyNodes, 'lang' => $lang, 'em' => $em];
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
