<?php

namespace Sandbox\WebsiteBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Kunstmaan\AdminBundle\Helper\Security\Acl\Permission\PermissionMap;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\NodeBundle\Entity\NodeVersion;
use Kunstmaan\NodeBundle\Helper\NodeMenu;
use Kunstmaan\TaggingBundle\Entity\Tag;
use Sandbox\WebsiteBundle\Entity\Article\ArticlePage;
use Sandbox\WebsiteBundle\Entity\Host;
use Sandbox\WebsiteBundle\Entity\News\NewsPage;
use Sandbox\WebsiteBundle\Entity\Place\PlaceOverviewPage;
use Sandbox\WebsiteBundle\Entity\PreferredTag;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class RouteController extends Controller
{
    /**
     * @Route("/{path}/")
     * @param $path
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pathAction(Request $request, $path)
    {

        if(preg_match("/api-filter/", $path))
            return $this->forward("KunstmaanNodeBundle:Slug:slug", ['url' => $path]);

        /** @var ObjectManager $em */
        $em = $this->getDoctrine()->getManager();
        $host = $em->getRepository('SandboxWebsiteBundle:Host')
            ->findOneBy(['name' => $request->getHost()]);

        $originalLocale = substr($path, 0, 2);

        $locale = substr($path, 0, 2);
        if($host){
            if(!$host->getMultiLanguage()){
                $locale = $host->getLang();
            }
        }
        if(!$locale) $locale = substr($path, 0, 2);


        $newPath = preg_replace('/' . $originalLocale . "\//", "", $path, 1);
        if($newPath == $path)
            $path = preg_replace('/' . $originalLocale . '/' , "", $path, 1);
        else
            $path = $newPath;

        //redirect to host lang
        if($locale != $originalLocale){
            $request->setLocale($locale);
            return $this->redirect("http://" . $request->getHost() . $request->getBaseUrl() . "/" . $locale);
        }


        //check for tag as last argument of path
        $args = explode('/', $path);
        $lastArg = $args[count($args)-1];
        $lastArg = str_replace("_", " ", $lastArg);

        $subject = null;
        if(array_key_exists(count($args) - 2, $args))
            $subject = $args[count($args) - 2];

        $tag = $em->getRepository('KunstmaanTaggingBundle:Tag')
            ->findOneBy(['name' => $lastArg]);

        //check if tag page // $subject could be from tag/{tag} page
        if($tag && $subject && $subject != "tag"){
            $locale = $request->getLocale();//page language code
            $placesLocale = [];//array of translated online nodes to return to template

            /** @var NodeTranslation $nodeTranslation */
            $nodeTranslation = $em->getRepository('KunstmaanNodeBundle:NodeTranslation')
                ->findOneBy(['slug' => $subject, 'online' => 1]);
            if(!$nodeTranslation)
                throw new NotFoundHttpException("Translation does not exist");

            $nodeChildren = $nodeTranslation->getNode()->getChildren();

            /** @var Node $node */
            foreach ($nodeChildren as $node) {
                $translation = $node->getNodeTranslation($locale);
                if($translation && $translation->isOnline()){
                    $placesLocale[] = $translation;
                }
            }

            $host = $em->getRepository('SandboxWebsiteBundle:Host')
                ->findOneBy(['name' => $request->getHost()]);
            $this->getSubNews($nodeTranslation->getNode(), $locale, $em, $news, $host, $tag);
            $this->getSubArticles($nodeTranslation->getNode(), $locale, $em, $articles, $host, $tag);

            //tags
            $tags = [];
            if($articles)
                /** @var ArticlePage $article */
                foreach ($articles as $article) {
                    foreach ($article->getTags() as $tag) {
                        $tags[$tag->getId()] = $tag;
                    }
                }
            if($news)
                /** @var NewsPage $article */
                foreach ($news as $article) {
                    foreach ($article->getTags() as $tag) {
                        $tags[$tag->getId()] = $tag;
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
                    unset($preferredTags[$index]);
                }
            }

            $context['preferredtags'] = $preferredTags;
            $context['tags'] = $tags;




            $context['places'] = $placesLocale;
            $context['news'] = $news;
            $context['articles'] = $articles;
            $context['lang'] = $locale;
            $context['em'] = $em;
            $context['title'] = $nodeTranslation->getTitle();

            //for top and bottom menu
            $securityContext = $this->get('security.context');
            $aclHelper      = $this->container->get('kunstmaan_admin.acl.helper');
            $node = $nodeTranslation->getNode();
            $nodeMenu       = new NodeMenu($em, $securityContext, $aclHelper, $request->getLocale(), $node, PermissionMap::PERMISSION_VIEW);
            //

            $context['nodemenu'] = $nodeMenu;

            return $this->render('@SandboxWebsite/Tag/placetag.html.twig', $context);
        }

        $path = trim($path, '/');
        return $this->forward("KunstmaanNodeBundle:Slug:slug", ['_locale' => $locale, 'url' => $path]);

    }


    public function getSubNews(Node $node, $locale,ObjectManager $em, &$news = [], $host, Tag $tag)
    {
        $nodeTranslation = $node->getNodeTranslation($locale);
        if($nodeTranslation && $nodeTranslation->isOnline()){
            /** @var PlaceOverviewPage $placeOverviewPage */
            $placeOverviewPage = $nodeTranslation->getPublicNodeVersion()->getRef($em);
            /** @var NewsPage $item */
            foreach ($placeOverviewPage->getNews() as $item) {

                //get node version
                /** @var NodeVersion $nodeVersion */
                $nodeVersion = $em->getRepository('KunstmaanNodeBundle:NodeVersion')->getNodeVersionFor($item);
                //check node online and lang
                if($nodeVersion
                    && $nodeVersion->getNodeTranslation()->isOnline()
                    && $nodeVersion->getNodeTranslation()->getLang() == $locale
                ){
                    //check host
                    /** @var Host $host */
                    if($host){
                        /** @var Host $itemHost */
                        foreach ($item->getHosts() as $itemHost) {
                            if($itemHost->getId() == $host->getId()){
                                if($item->getTags()->contains($tag)) {
                                    $news[$nodeVersion->getNodeTranslation()->getId()] = $item;
                                    break;
                                }
                            }
                        }


                    }else {
                        if($item->getTags()->contains($tag)) {
                            $news[$nodeVersion->getNodeTranslation()->getId()] = $item;
                        }
                    }
                }
            }
        }

        foreach ($node->getChildren() as $child) {
            $this->getSubNews($child, $locale, $em, $news, $host, $tag);
        }

    }

    public function getSubArticles(Node $node, $locale,ObjectManager $em, &$articles = [], $host, Tag $tag)
    {
        $nodeTranslation = $node->getNodeTranslation($locale);
        if($nodeTranslation && $nodeTranslation->isOnline()){
            /** @var PlaceOverviewPage $placeOverviewPage */
            $placeOverviewPage = $nodeTranslation->getPublicNodeVersion()->getRef($em);
            /** @var NewsPage $item */
            foreach ($placeOverviewPage->getArticles() as $item) {
                //get node version
                /** @var NodeVersion $nodeVersion */
                $nodeVersion = $em->getRepository('KunstmaanNodeBundle:NodeVersion')->getNodeVersionFor($item);
                //check node online and lang
                if($nodeVersion
                    && $nodeVersion->getNodeTranslation()->isOnline()
                    && $nodeVersion->getNodeTranslation()->getLang() == $locale
                ){
                    //check host
                    /** @var Host $host */
                    if($host){
                        /** @var Host $itemHost */
                        foreach ($item->getHosts() as $itemHost) {
                            if($itemHost->getId() == $host->getId()){
                                if($item->getTags()->contains($tag)) {
                                    $articles[$nodeVersion->getNodeTranslation()->getId()] = $item;
                                    break;
                                }
                            }
                        }
                    }else {
                        if($item->getTags()->contains($tag)) {
                            $articles[$nodeVersion->getNodeTranslation()->getId()] = $item;
                        }
                    }
                }
            }
        }

        foreach ($node->getChildren() as $child) {
            $this->getSubArticles($child, $locale, $em, $articles, $host, $tag);
        }

    }
}
