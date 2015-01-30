<?php

namespace Sandbox\WebsiteBundle\Entity\Pages;

use Kunstmaan\NodeBundle\Helper\RenderContext;
use Sandbox\WebsiteBundle\Entity\Article\ArticlePage;
use Sandbox\WebsiteBundle\Entity\News\NewsPage;
use Sandbox\WebsiteBundle\Form\Pages\HomePageAdminType;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\NodeBundle\Entity\AbstractPage;
use Kunstmaan\PagePartBundle\Helper\HasPageTemplateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Request;

/**
 * HomePage
 *
 * @ORM\Entity()
 * @ORM\Table(name="sb_home_pages")
 */
class HomePage extends AbstractPage  implements HasPageTemplateInterface
{
    public function service(ContainerInterface $container, Request $request, RenderContext $context)
    {
        parent::service($container, $request, $context);

        $locale = $request->getLocale();

        $em = $container->get('doctrine.orm.entity_manager');


        $host = $em->getRepository('SandboxWebsiteBundle:Host')
            ->findOneBy(['name' => $request->getHost()]);

        $news = $em->getRepository('SandboxWebsiteBundle:News\NewsPage')->createQueryBuilder('n')
            ->select('n')
            //->where('n.dateUntil > :date')
            //->setParameter(':date', new \DateTime())
            ->orderBy('n.date', 'desc')
            ->getQuery()
            ->getResult();

        $realNews = [];
        $i = 0;
        foreach ($news as $n) {
            $node = $em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($n);
            if(!$node) continue;

            $translation = $node->getNodeTranslation($locale);
            if(!$translation) continue;

            if(!$node->isDeleted() && $translation->isOnline()){
                if($host){
                    /** @var NewsPage $page */
                    $page = $translation->getRef($em);
                    if($page->getHosts()->contains($host)){
                        $realNews[$node->getId()] = $page;
                        $i = count($realNews);
                    }
                }else {
                    $realNews[$node->getId()] = $translation->getRef($em);
                    $i = count($realNews);
                }
            }

            if($i >= 5) break;
        }




        $articles = $em->getRepository('SandboxWebsiteBundle:Article\ArticlePage')->createQueryBuilder('n')
            ->select('n')
            ->orderBy('n.date', 'desc')
            ->getQuery()
            ->getResult();

        $realArticles = [];
        $i = 0;
        foreach ($articles as $n) {
            $node = $em->getRepository('KunstmaanNodeBundle:Node')->getNodeFor($n);
            if(!$node) continue;

            $translation = $node->getNodeTranslation($locale);
            if(!$translation) continue;

            if(!$node->isDeleted() && $translation->isOnline()){
                if($host){
                    /** @var ArticlePage $page */
                    $page = $translation->getRef($em);
                    if($page->getHosts()->contains($host)){
                        $realArticles[$node->getId()] = $page;
                        $i = count($realArticles);
                    }
                }else {
                    $realArticles[$node->getId()] = $translation->getRef($em);
                    $i = count($realArticles);
                }
            }

            if($i >= 5) break;
        }

        $context['news'] = $realNews;
        $context['articles'] = $realArticles;
        $context['lang'] = $locale;
        $context['em'] = $em;
    }

    /**
     * Returns the default backend form type for this page
     *
     * @return AbstractType
     */
    public function getDefaultAdminType()
    {
        return new HomePageAdminType();
    }

    /**
     * @return array
     */
    public function getPossibleChildTypes()
    {
        return array(
            array(
                'name' => 'TagPage',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Pages\TagPage'
            ),
            array(
                'name' => 'PlacesPage',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Pages\PlacesPage'
            ),
            array(
                'name'  => 'ContentPage',
                'class' => 'Sandbox\WebsiteBundle\Entity\Pages\ContentPage'
            ),
            array(
                'name'  => 'FormPage',
                'class' => 'Sandbox\WebsiteBundle\Entity\Pages\FormPage'
            ),
            array(
                'name'  => 'BehatTestPage',
                'class' => 'Sandbox\WebsiteBundle\Entity\Pages\BehatTestPage'
            ),
            array(
                'name' => 'News Overview Page',
                'class'=> 'Sandbox\WebsiteBundle\Entity\News\NewsOverviewPage'
            ),
            array(
                'name' => 'Country',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage'
            ),
            array(
                'name' => 'Company',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Company\CompanyOverviewPage'
            ),
            array(
                'name' => 'Article',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Article\ArticleOverviewPage'
            )
        );
    }

    /**
     * @return string[]
     */
    public function getPagePartAdminConfigurations()
    {
        return array('SandboxWebsiteBundle:middle-column', 'SandboxWebsiteBundle:slider', 'SandboxWebsiteBundle:left-column', 'SandboxWebsiteBundle:right-column');
    }

    /**
     * {@inheritdoc}
     */
    public function getPageTemplates()
    {
        return array('SandboxWebsiteBundle:homepage', 'SandboxWebsiteBundle:homepage-no-slider');
    }

    /**
     * @return string
     */
    public function getDefaultView()
    {
        return 'SandboxWebsiteBundle:Pages\HomePage:view.html.twig';
    }
}
