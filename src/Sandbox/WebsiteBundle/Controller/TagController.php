<?php

namespace Sandbox\WebsiteBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Kunstmaan\AdminBundle\Helper\Security\Acl\Permission\PermissionMap;
use Kunstmaan\NodeBundle\Helper\NodeMenu;
use Kunstmaan\TaggingBundle\Entity\Tag;
use Kunstmaan\TaggingBundle\Entity\Tagging;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TagController extends Controller
{
    /**
     * @Route("/tag/{tag}")
     * @Template()
     * @param $tag
     * @return array
     */
    public function tagAction(Request $request, $tag)
    {
        /** @var ObjectManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var Tag $tag */
        $tag = $em->getRepository('KunstmaanTaggingBundle:Tag')
            ->findOneBy(['name'=>$tag]);

        $articles = [];
        $news = [];

        $host = $em->getRepository('SandboxWebsiteBundle:Host')
            ->findOneBy(['name' => $request->getHost()]);

        if($tag){
            /** @var Tagging[] $articleTags */
            $articleTags = $em->getRepository('KunstmaanTaggingBundle:Tagging')
                ->findBy(['tag' => $tag, 'resourceType' => 'Sandbox\WebsiteBundle\Entity\Article\ArticlePage']);
            /** @var Tagging[] $newsTags */
            $newsTags = $em->getRepository('KunstmaanTaggingBundle:Tagging')
                ->findBy(['tag' => $tag, 'resourceType' => 'Sandbox\WebsiteBundle\Entity\News\NewsPage']);

            foreach ($articleTags as $tag) {
                /** @var Tagging $tag */
                $page = $em->getRepository('SandboxWebsiteBundle:Article\ArticlePage')
                    ->find($tag->getResourceId());
                $node = $em->getRepository('KunstmaanNodeBundle:Node')
                    ->getNodeFor($page);

                if(!$node->isDeleted()){
                    if($host){
                        //check host
                        if($page->getHosts()->contains($host))
                            $articles[] = $page;
                    }else{
                        $articles[] = $page;
                    }
                }
            }

            foreach ($articleTags as $tag) {
                /** @var Tagging $tag */
                $page = $em->getRepository('SandboxWebsiteBundle:News\NewsPage')
                    ->find($tag->getResourceId());
                $node = $em->getRepository('KunstmaanNodeBundle:Node')
                    ->getNodeFor($page);

                if(!$node->isDeleted()){
                    if($host){
                        //check host
                        if($page->getHosts()->contains($host))
                            $news[] = $page;
                    }else{
                        $news[] = $page;
                    }
                }
            }
        }

        //for top and bottom menu
        $securityContext = $this->get('security.context');
        $aclHelper      = $this->container->get('kunstmaan_admin.acl.helper');
        $node = $em->getRepository('KunstmaanNodeBundle:Node')->find(1);
        $nodeMenu       = new NodeMenu($em, $securityContext, $aclHelper, $request->getLocale(), $node, PermissionMap::PERMISSION_VIEW);
        //

        return ['articles' => $articles, 'news' => $news, 'lang' => $request->getLocale(), 'nodemenu' => $nodeMenu];
    }
}
