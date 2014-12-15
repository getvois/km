<?php

namespace Sandbox\WebsiteBundle\EventListener;


use Kunstmaan\NodeBundle\Event\CopyPageTranslationNodeEvent;
use Kunstmaan\PagePartBundle\Helper\HasPagePartsInterface;
use Kunstmaan\PagePartBundle\Helper\PagePartConfigurationReader;
use Kunstmaan\PagePartBundle\Repository\PagePartRefRepository;
use Symfony\Component\DependencyInjection\Container;

class CopyPageTranslationListener {
    private $container;
    /** @var \Doctrine\ORM\EntityManager $em */
    private $em;
    private $user;
    private $kernel;

    function __construct(Container $container)
    {
        $this->kernel = $container->get('kernel');
        $this->container = $container;
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->user = $container->get('security.context')->getToken()->getUser();
    }

    public function onCopyPageTranslation(CopyPageTranslationNodeEvent $nodeEvent)
    {
        $page = $nodeEvent->getOriginalPage();
        if($page instanceof HasPagePartsInterface){
            $langPage = $nodeEvent->getPage();

            $pagePartConfigurationReader = new PagePartConfigurationReader($this->kernel);
            $contexts = $pagePartConfigurationReader->getPagePartContexts($page);
            /** @var PagePartRefRepository $pagePartRepo */
            $pagePartRepo = $this->em->getRepository('KunstmaanPagePartBundle:PagePartRef');
            foreach ($contexts as $context) {
                if(!$pagePartRepo->hasPageParts($langPage, $context)) {
                    $pagePartRepo->copyPageParts($this->em, $page, $langPage, $context);
                }
            }
        }
    }
}