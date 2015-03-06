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
        $host = $em->getRepository('SandboxWebsiteBundle:Host')
            ->findOneBy(['name' => $request->getHost()]);

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
        $host = $em->getRepository('SandboxWebsiteBundle:Host')
            ->findOneBy(['name' => $request->getHost()]);

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
//        findBy([
//            'refEntityName' => 'Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage',
//            'deleted' => 0]);

//        $node = null;
//        foreach ($nodes as $node) {
//            $willDo = true;
//            while($node->getParent()->getId() != 1){//1 = home page
//                if($node->getParent()->getRefEntityName() == 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage'){
//                    $willDo = false;
//                    break;
//                }
//                $node = $node->getParent();
//            }
//
//            if($willDo) break;
//        }



        $rightNodes = [];
        $companyNodes = [];
        //now node is company root.
        if($node) {
            $acl = $this->container->get('kunstmaan_admin.acl.helper');
            $rightNodes = $em->getRepository('KunstmaanNodeBundle:Node')->getChildNodes($node->getId(), $lang, "VIEW",  $acl);

            foreach ($rightNodes as $node) {
                if(!$node->getNodeTranslation($lang) || !$node->getNodeTranslation($lang)->isOnline()) continue;
                $nodes = $em->getRepository('KunstmaanNodeBundle:Node')->getChildNodes($node->getId(), $lang, "VIEW",  $acl);

                /** @var Node $node */
                    $companyNodes[] = ['parent' =>$node, 'children' => $nodes];
            }
        }

        return [ 'nodes' => $rightNodes, 'companies' => $companyNodes, 'lang' => $lang, 'em' => $em];
    }

    /**
     * @param Request $request
     * @return array
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

//        $nodes = $em->getRepository('KunstmaanNodeBundle:Node')->
//        findBy(['parent' => $countryRootNode,
//                'refEntityName' => 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage',
//                'deleted' => 0]);
//
//        /** @var Node[] $placeNodes */
//        $placeNodes = [];
//
//        $nodeIds = [];
//        foreach ($nodes as $node) {
//            $translation = $node->getNodeTranslation($lang);
//            if($translation)
//                $nodeIds[] = $translation->getRef($em)->getId();
//        }

        /** @var PlaceOverviewPage[] $placeOverviewPages */
        $placeOverviewPages = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')
            ->getActiveOverviewPages($lang, $host, $countryRootNode->getId());
//        $placeOverviewPages = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')->createQueryBuilder('n')
//            ->where('n.id IN(:ids)')
//            ->setParameter(':ids', $nodeIds)
//            ->orderBy('n.title')
//            ->getQuery()
//            ->getResult();


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
