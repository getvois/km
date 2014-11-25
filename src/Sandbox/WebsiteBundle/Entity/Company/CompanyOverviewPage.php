<?php

namespace Sandbox\WebsiteBundle\Entity\Company;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Sandbox\WebsiteBundle\Form\Company\CompanyOverviewPageAdminType;
use Sandbox\WebsiteBundle\PagePartAdmin\Company\CompanyOverviewPagePagePartAdminConfigurator;
use Kunstmaan\ArticleBundle\Entity\AbstractArticleOverviewPage;
use Kunstmaan\NodeBundle\Helper\RenderContext;
use Kunstmaan\PagePartBundle\PagePartAdmin\AbstractPagePartAdminConfigurator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * The article overview page which shows its articles
 *
 * @ORM\Entity(repositoryClass="Sandbox\WebsiteBundle\Repository\Company\CompanyOverviewPageRepository")
 * @ORM\Table(name="sb_company_overviewpages")
 */
class CompanyOverviewPage extends AbstractArticleOverviewPage
{

    /**
     * @var integer
     *
     * @ORM\Column(name="company_id", type="integer", nullable=true)
     */
    private $companyId;

    /**
     * @return int
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * @param int $companyId
     * @return $this
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
        return $this;
    }

    /**
     * @return AbstractPagePartAdminConfigurator[]
     */
    public function getPagePartAdminConfigurations()
    {
        return array(new CompanyOverviewPagePagePartAdminConfigurator());
    }



    public function getSubNews(Node $node, $locale, $em, &$news = [])
    {
        $nodeTranslation = $node->getNodeTranslation($locale);
        if($nodeTranslation && $nodeTranslation->isOnline()){
            /** @var PlaceOverviewPage $placeOverviewPage */
            $placeOverviewPage = $nodeTranslation->getPublicNodeVersion()->getRef($em);
            /** @var NewsPage $item */
            foreach ($placeOverviewPage->getNews() as $item) {

                //get node version
                /** @var NodeVersion $nodeVersion */
                $nodeVersion = $em->getRepository('KunstmaanNodeBundle:NodeVersion')
                    ->findOneBy([
                        'refId' => $item->getId(),
                        'refEntityName' => 'Sandbox\WebsiteBundle\Entity\News\NewsPage',
                        'type' => 'public'
                    ]);
                //check node online and lang
                if($nodeVersion
                    && $nodeVersion->getNodeTranslation()->isOnline()
                    && $nodeVersion->getNodeTranslation()->getLang() == $locale
                ){
                    $news[$nodeVersion->getNodeTranslation()->getId()] = $item;
                }
            }
        }

        foreach ($node->getChildren() as $child) {
            $this->getSubNews($child, $locale, $em, $news);
        }

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

        $em = $container->get('doctrine.orm.entity_manager');
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

    public function getArticleRepository($em)
    {
        return $em->getRepository('SandboxWebsiteBundle:Company\CompanyPage');
    }

    /**
     * @return string
     */
    public function getDefaultView()
    {
        return 'SandboxWebsiteBundle:Company/CompanyOverviewPage:view.html.twig';
    }

    /**
     * Returns the default backend form type for this page
     *
     * @return AbstractType
     */
    public function getDefaultAdminType()
    {
        return new CompanyOverviewPageAdminType();
    }

    /**
     * @return array
     */
    public function getPossibleChildTypes()
    {
        return array(
            array(
                'name'  => 'Country',
                'class' => 'Sandbox\WebsiteBundle\Entity\Pages\ContentPage'
            ),
            array(
                'name' => 'Company',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage'
            ),
        );
    }

}
