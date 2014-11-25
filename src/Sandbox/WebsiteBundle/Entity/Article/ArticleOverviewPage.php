<?php

namespace Sandbox\WebsiteBundle\Entity\Article;

use Doctrine\ORM\Mapping as ORM;
use Sandbox\WebsiteBundle\Form\Article\ArticleOverviewPageAdminType;
use Sandbox\WebsiteBundle\PagePartAdmin\Article\ArticleOverviewPagePagePartAdminConfigurator;
use Kunstmaan\ArticleBundle\Entity\AbstractArticleOverviewPage;
use Kunstmaan\NodeBundle\Helper\RenderContext;
use Kunstmaan\PagePartBundle\PagePartAdmin\AbstractPagePartAdminConfigurator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * The article overview page which shows its articles
 *
 * @ORM\Entity(repositoryClass="Sandbox\WebsiteBundle\Repository\Article\ArticleOverviewPageRepository")
 * @ORM\Table(name="sb_article_overviewpages")
 */
class ArticleOverviewPage extends AbstractArticleOverviewPage
{




    /**
     * @return AbstractPagePartAdminConfigurator[]
     */
    public function getPagePartAdminConfigurations()
    {
        return array(new ArticleOverviewPagePagePartAdminConfigurator());
    }

    /**
     * @param ContainerInterface $container
     * @param Request $request
     * @param RenderContext $context
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|void
     */
    public function service(ContainerInterface $container, Request $request, RenderContext $context)
    {
        parent::service($container, $request, $context);
    }

    public function getArticleRepository($em)
    {
        return $em->getRepository('SandboxWebsiteBundle:Article\ArticlePage');
    }

    /**
     * @return string
     */
    public function getDefaultView()
    {
        return 'SandboxWebsiteBundle:Article/ArticleOverviewPage:view.html.twig';
    }

    /**
     * Returns the default backend form type for this page
     *
     * @return AbstractType
     */
    public function getDefaultAdminType()
    {
        return new ArticleOverviewPageAdminType();
    }

    /**
     * @return array
     */
    public function getPossibleChildTypes()
    {
        return array(
            array(
                'name' => 'Article',
                'class'=> 'Sandbox\WebsiteBundle\Entity\Article\ArticleOverviewPage'
            )
        );
    }
}
