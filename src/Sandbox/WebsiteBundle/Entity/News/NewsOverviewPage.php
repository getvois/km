<?php

namespace Sandbox\WebsiteBundle\Entity\News;

use Doctrine\ORM\Mapping as ORM;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Sandbox\WebsiteBundle\Entity\Host;
use Sandbox\WebsiteBundle\Form\News\NewsOverviewPageAdminType;
use Sandbox\WebsiteBundle\PagePartAdmin\News\NewsOverviewPagePagePartAdminConfigurator;
use Kunstmaan\ArticleBundle\Entity\AbstractArticleOverviewPage;
use Kunstmaan\NodeBundle\Helper\RenderContext;
use Kunstmaan\PagePartBundle\PagePartAdmin\AbstractPagePartAdminConfigurator;
use Sandbox\WebsiteBundle\Repository\News\NewsPageRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * The article overview page which shows its articles
 *
 * @ORM\Entity(repositoryClass="Sandbox\WebsiteBundle\Repository\News\NewsOverviewPageRepository")
 * @ORM\Table(name="sb_news_overviewpages")
 */
class NewsOverviewPage extends AbstractArticleOverviewPage
{

    /**
     * @return AbstractPagePartAdminConfigurator[]
     */
    public function getPagePartAdminConfigurations()
    {
        return array(new NewsOverviewPagePagePartAdminConfigurator());
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

        /** @var NewsPage[] $articles */
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
     * @return NewsPageRepository
     */
    public function getArticleRepository($em)
    {
        return $em->getRepository('SandboxWebsiteBundle:News\NewsPage');
    }

    /**
     * @return string
     */
    public function getDefaultView()
    {
        return 'SandboxWebsiteBundle:News/NewsOverviewPage:view.html.twig';
    }

    /**
     * Returns the default backend form type for this page
     *
     * @return AbstractType
     */
    public function getDefaultAdminType()
    {
        return new NewsOverviewPageAdminType();
    }

}
