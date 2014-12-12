<?php
/**
 * Created by PhpStorm.
 * User: kosmos
 * Date: 12.12.14
 * Time: 17:05
 */

namespace Sandbox\WebsiteBundle\EventListener;


use Kunstmaan\AdminListBundle\Event\AdminListEvent;
use Kunstmaan\TaggingBundle\Entity\Tag;
use Kunstmaan\TranslatorBundle\Entity\Translation;
use Kunstmaan\TranslatorBundle\Repository\TranslationRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AdminListListener {
    private $container;
    /** @var \Doctrine\ORM\EntityManager $em */
    private $em;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine.orm.entity_manager');
    }

    public function onAdminListPostPersist(AdminListEvent $adminListEvent){

        $adminList = $adminListEvent->getAdminList();
        if($adminList instanceof Tag){

            /** @var TranslationRepository $repository */
            $repository = $this->em->getRepository('KunstmaanTranslatorBundle:Translation');
            $translation = $repository->findOneBy(['keyword' => $adminList->getName()]);
            if($translation) return;

            $translationId = $this->em->getRepository('KunstmaanTranslatorBundle:Translation')->getUniqueTranslationId();

            $languages = $this->container->getParameter('requiredlocales');

            foreach (explode('|', $languages) as $lang) {
                $t = new Translation();
                $t->setLocale($lang);
                $t->setDomain('tag');
                $t->setCreatedAt(new \DateTime());
                $t->setFlag(Translation::FLAG_NEW);
                $t->setTranslationId($translationId);
                $t->setKeyword($adminList->getName());
                $t->setText($adminList->getName());
                $this->em->persist($t);
            }

            $this->em->flush();
        }


    }
} 