<?php

namespace Sandbox\WebsiteBundle\Entity\Article;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\NodeBundle\Controller\SlugActionInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Sandbox\WebsiteBundle\Entity\Host;
use Sandbox\WebsiteBundle\Entity\PreferredTag;
use Sandbox\WebsiteBundle\Form\Article\ArticleOverviewPageAdminType;
use Sandbox\WebsiteBundle\PagePartAdmin\Article\ArticleOverviewPagePagePartAdminConfigurator;
use Kunstmaan\ArticleBundle\Entity\AbstractArticleOverviewPage;
use Kunstmaan\NodeBundle\Helper\RenderContext;
use Kunstmaan\PagePartBundle\PagePartAdmin\AbstractPagePartAdminConfigurator;
use Sandbox\WebsiteBundle\Repository\Article\ArticlePageRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * The article overview page which shows its articles
 *
 * @ORM\Entity(repositoryClass="Sandbox\WebsiteBundle\Repository\Article\ArticleOverviewPageRepository")
 * @ORM\Table(name="sb_article_overviewpages")
 */
class ArticleOverviewPage extends AbstractArticleOverviewPage implements SlugActionInterface
{




    /**
     * @return AbstractPagePartAdminConfigurator[]
     */
    public function getPagePartAdminConfigurations()
    {
        return array(new ArticleOverviewPagePagePartAdminConfigurator());
    }

    public function getControllerAction()
    {
        return "SandboxWebsiteBundle:BackwardCompatibility:service";
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

        $em = $container->get('doctrine')->getManager();
        $repository = $this->getArticleRepository($em);

        /** @var ArticlePage[] $articles */
        $articles = $repository->getArticles($request->getLocale());

        $tags = [];
        $host = $em->getRepository('SandboxWebsiteBundle:Host')
            ->findOneBy(['name' => $request->getHost()]);

        //check host
        if($host){
            for($i = 0; $i < count($articles); $i++){
                $remove = true;
                /** @var Host $itemHost */
                foreach ($articles[$i]->getHosts() as $itemHost) {
                    //check host in item
                    if($itemHost->getId() == $host->getId()){
                        $remove = false;
                        break;
                    }
                }

                //host was not found in item
                if($remove){
                    //remove item
                    unset($articles[$i]);
                }else{
                    //add tags
                    foreach ($articles[$i]->getTags() as $tag) {
                        $tags[$tag->getId()] = $tag;
                    }

                }
            }
        }else{
            //add tags
            foreach ($articles as $article) {
                foreach ($article->getTags() as $tag) {
                    $tags[$tag->getId()] = $tag;
                }
            }
        }

        //preferred tags
        /** @var PreferredTag[] $preferredTags */
        $preferredTags = $em->getRepository('SandboxWebsiteBundle:PreferredTag')
            ->findAll();

        //delete preferred tags from tags
        foreach ($preferredTags as $index => $tag) {
            if(($key = array_search($tag->getTag(), $tags)) !== false){
                unset($tags[$key]);
            }else{
                //unset($preferredTags[$index]);
            }
        }


        $context['preferredtags'] = $preferredTags;
        $context['tags'] = $tags;

        $adapter = new ArrayAdapter($articles);
        $pagerfanta = new Pagerfanta($adapter);

        $pagenumber = $request->get('page');
        if (!$pagenumber || $pagenumber < 1) {
            $pagenumber = 1;
        }
        $pagerfanta->setCurrentPage($pagenumber);
        $context['pagerfanta'] = $pagerfanta;
    }

    /**
     * @param $em
     * @return ArticlePageRepository
     */
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
