<?php

namespace Sandbox\WebsiteBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Kunstmaan\NodeBundle\Entity\Node;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DropdownController extends Controller
{

    /**
     * @return array
     *
     * @Template()
     */
    public function departureAction(Request $request){
        $lang = $request->getLocale();
        /** @var ObjectManager $em */
        $em = $this->getDoctrine()->getManager();
        $placeNodes = $this->getCountries($lang);

        return ['nodes' => $placeNodes, 'lang' => $lang, 'em' => $em];
    }

    /**
     * @return array
     *
     * @Template()
     */
    public function destinationAction(Request $request)
    {
        $lang = $request->getLocale();
        /** @var ObjectManager $em */
        $em = $this->getDoctrine()->getManager();
        $placeNodes = $this->getCountries($lang);

        return ['nodes' => $placeNodes, 'lang' => $lang, 'em' => $em];
    }

    /**
     * @return array
     *
     * @Template()
     */
    public function companyAction(Request $request)
    {
        /////not implemented
        $lang = $request->getLocale();
        /** @var ObjectManager $em */
        $em = $this->getDoctrine()->getManager();

        $nodes = $em->getRepository('KunstmaanNodeBundle:Node')->
        findBy([
                'refEntityName' => 'Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage',
                'deleted' => 0]);

        $companies = [];

        foreach ($nodes as $node) {
            $translation = $node->getNodeTranslation($lang);
            if($translation && $translation->isOnline()) {
                $companies[] = $translation->getRef($em);
            }
        }

        return ['companies' => $companies];
    }
    /**
     * @return array
     *
     * @Template()
     */
    public function companySelectAction(Request $request)
    {
        $lang = $request->getLocale();
        /** @var ObjectManager $em */
        $em = $this->getDoctrine()->getManager();

        $nodes = $em->getRepository('KunstmaanNodeBundle:Node')->
        findBy([
                'refEntityName' => 'Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage',
                'deleted' => 0]);

        $companies = [];

        foreach ($nodes as $node) {
            if($node->getParent()->getId() == 1) continue;//exclude direct root node (Companies)
            $translation = $node->getNodeTranslation($lang);
            if($translation && $translation->isOnline()) {
                $companies[] = $translation->getRef($em);
            }
        }

        return ['companies' => $companies];
    }


    private function getCountries($lang)
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


        $nodes = $em->getRepository('KunstmaanNodeBundle:Node')->
        findBy(['parent' => $countryRootNode,
                'refEntityName' => 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage',
                'deleted' => 0]);

        /** @var Node[] $placeNodes */
        $placeNodes = [];

        $nodeIds = [];
        foreach ($nodes as $node) {
            $translation = $node->getNodeTranslation($lang);
            if($translation)
                $nodeIds[] = $translation->getRef($em)->getId();
        }

        $placeOverviewPages = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')->createQueryBuilder('n')
            ->where('n.id IN(:ids)')
            ->setParameter(':ids', $nodeIds)
            ->orderBy('n.title')
            ->getQuery()
            ->getResult();

        foreach ($placeOverviewPages as $placeOverviewPage) {
            /** @var PlaceOverviewPage $placeOverviewPage */
            $nodeVersion = $em->getRepository('KunstmaanNodeBundle:NodeVersion')
                ->findOneBy(['refId' => $placeOverviewPage->getId(),
                        'refEntityName' => 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage']
                );

            $placeNodes[] = $nodeVersion->getNodeTranslation()->getNode();
        }

        return $placeNodes;
    }

}
