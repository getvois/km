<?php

namespace Sandbox\WebsiteBundle\EventListener;


use Doctrine\ORM\Event\LifecycleEventArgs;
use Kunstmaan\AdminListBundle\Event\AdminListEvent;
use Kunstmaan\TaggingBundle\Entity\Tag;
use Kunstmaan\TranslatorBundle\Entity\Translation;
use Kunstmaan\TranslatorBundle\Repository\TranslationRepository;

class AdminListListener {
    private $locales;

    function __construct($locales)
    {
        $this->locales = $locales;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($entity instanceof Tag) {
            /** @var TranslationRepository $repository */
            $repository = $entityManager->getRepository('KunstmaanTranslatorBundle:Translation');
            $translation = $repository->findOneBy(['keyword' => $entity->getName()]);
            if($translation) return;

            $translationId = $entityManager->getRepository('KunstmaanTranslatorBundle:Translation')->getUniqueTranslationId();

            foreach (explode('|', $this->locales) as $lang) {
                $t = new Translation();
                $t->setLocale($lang);
                $t->setDomain('tag');
                $t->setCreatedAt(new \DateTime());
                $t->setFlag(Translation::FLAG_NEW);
                $t->setTranslationId($translationId);
                $t->setKeyword($entity->getName());
                $t->setText($entity->getName());
                $entityManager->persist($t);
            }

            $entityManager->flush();
        }
    }
} 