<?php

namespace Sandbox\WebsiteBundle\Entity\Pages;

use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\NodeBundle\Helper\RenderContext;
use Sandbox\WebsiteBundle\Form\Pages\ContentPageAdminType;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\NodeBundle\Entity\AbstractPage;
use Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Request;

/**
 * ContentPage
 *
 * @ORM\Entity()
 * @ORM\Table(name="sb_content_pages")
 */
class ContentPage extends AbstractPage  implements HasPageTemplateInterface
{

    /**
     * Returns the default backend form type for this page
     *
     * @return AbstractType
     */
    public function getDefaultAdminType()
    {
        return new ContentPageAdminType();
    }

    /**
     * @return array
     */
    public function getPossibleChildTypes()
    {
        return array (
            array(
                'name'  => 'ContentPage',
                'class' => 'Sandbox\WebsiteBundle\Entity\Pages\ContentPage'
            ),
            array(
                'name'  => 'SatelliteOverviewPage',
                'class' => 'Sandbox\WebsiteBundle\Entity\Pages\SatelliteOverviewPage'
            ),
            array(
                'name' => 'Company',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage'
            ),
        );
    }

    /**
     * @return string[]
     */
    public function getPagePartAdminConfigurations()
    {
        return array('SandboxWebsiteBundle:main');
    }

    /**
     * {@inheritdoc}
     */
    public function getPageTemplates()
    {
        return array('SandboxWebsiteBundle:contentpage');
    }

    /**
     * @return string
     */
    public function getDefaultView()
    {
        return 'SandboxWebsiteBundle:Pages\ContentPage:view.html.twig';
    }

    /**
     * @param ContainerInterface $container
     * @param Request $request
     * @param RenderContext $context
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|void
     */
    public function service(ContainerInterface $container, Request $request, RenderContext $context)
    {
        $locale = $request->getLocale();//page language code
        $placesLocale = [];//array of translated online nodes to return to template

        /** @var NodeTranslation $nodeTranslation */
        $nodeTranslation = $context->getArrayCopy()['nodetranslation'];
        $nodeChildren = $nodeTranslation->getNode()->getChildren();

        /** @var Node $node */
        foreach ($nodeChildren as $node) {
            $translation = $node->getNodeTranslation($locale);
            if($translation && $translation->isOnline()){
                $placesLocale[] = $translation;
            }
        }

        //$em = $container->get('doctrine.orm.entity_manager');
        //$this->getSubNews($nodeTranslation->getNode(), $locale, $em, $news);

//        $em = $container->get('doctrine.orm.entity_manager');
//        $places = $em->getRepository('SandboxWebsiteBundle:Place\PlaceOverviewPage')->findAll();
//
//        $translationIds = [];//array of node translation ids
//        /** @var PlaceOverviewPage[] $placesLocale */
//        $placesLocale = [];//array of translated online nodes to return to template
//
//        foreach ($places as $place) {
//            //get node version
//            /** @var NodeVersion $nodeVersion */
//            $nodeVersion = $em->getRepository('KunstmaanNodeBundle:NodeVersion')
//                ->findOneBy([
//                    'refId' => $place->getId(),
//                    'refEntityName' => 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage',
//                    'type' => 'public'
//                ]);
//
//            //check node versions (node could have same node translations ids)
//            //check node online and lang
//            if($nodeVersion
//                && $nodeVersion->getNodeTranslation()->isOnline()
//                && $nodeVersion->getNodeTranslation()->getLang() == $locale
//            ){
//                //add node if translation does not exist
//                if(!in_array($nodeVersion->getNodeTranslation()->getId(), $translationIds)) {
//                    $placesLocale[] = $place;
//                    $translationIds[] = $nodeVersion->getNodeTranslation()->getId();
//                }
//            }
//        }


        //$node = $em->getRepository('KunstmaanNodeBundle:Node')->find(12);//15 spain
        //var_dump($em->getRepository('KunstmaanNodeBundle:Node')->childrenHierarchy()[0]['__children'][5]);
        //var_dump($em->getRepository('KunstmaanNodeBundle:Node')->getChildren($node));
        //var_dump($placesLocale[1]->()->count());


        $context['places'] = $placesLocale;
        //$context['news'] = $news;
        $context['lang'] = $locale;

        parent::service($container, $request, $context);
    }

}
