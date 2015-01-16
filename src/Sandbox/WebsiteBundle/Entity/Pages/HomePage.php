<?php

namespace Sandbox\WebsiteBundle\Entity\Pages;

use Kunstmaan\NodeBundle\Helper\RenderContext;
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

        $news = $em->getRepository('SandboxWebsiteBundle:News\NewsPage')->createQueryBuilder('n')
            ->select('n')
            ->where('n.dateUntil > :date')
            ->setParameter(':date', new \DateTime())
            ->orderBy('n.date', 'desc')
            ->getQuery()
            ->getResult();

        $realNews = [];
        $i = 0;
        foreach ($news as $n) {
            $translation = $em->getRepository('KunstmaanNodeBundle:NodeTranslation')->getNodeTranslationFor($n);
            if(!$translation) continue;

            if($translation->isOnline() && !$translation->getNode()->isDeleted() && $translation->getLang() == $locale){
                $realNews[] = $n;
                $i++;
            }

            if($i >= 5) break;
        }




        $articles = $em->getRepository('SandboxWebsiteBundle:Article\ArticlePage')->createQueryBuilder('n')
            ->select('n')
            ->orderBy('n.date', 'desc')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        $context['news'] = $realNews;
        $context['articles'] = $articles;
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
