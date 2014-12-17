<?php

namespace Sandbox\WebsiteBundle\EventListener;


use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
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

    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        if ($eventArgs->getEntity() instanceof Tag) {
            if ($eventArgs->hasChangedField('name')) {
                $newVal = $eventArgs->getNewValue('name');
                $oldVal = $eventArgs->getOldValue('name');
                $entityManager = $eventArgs->getEntityManager();

                $translation = $entityManager->getRepository('KunstmaanTranslatorBundle:Translation')
                    ->findOneBy(['domain'=>'tag', 'keyword' => $oldVal]);

                if(!$translation) {
                    //!!infinite loop!!
                    //create new
//                    $translationId = $entityManager->getRepository('KunstmaanTranslatorBundle:Translation')->getUniqueTranslationId();
//
//                    foreach (explode('|', $this->locales) as $lang) {
//                        $t = new Translation();
//                        $t->setLocale($lang);
//                        $t->setDomain('tag');
//                        $t->setCreatedAt(new \DateTime());
//                        $t->setFlag(Translation::FLAG_NEW);
//                        $t->setTranslationId($translationId);
//                        $t->setKeyword($newVal);
//                        $t->setText($newVal);
//                        $entityManager->persist($t);
//                    }

                    //$entityManager->flush();
                }else{
                    //update old
                    $translation->setKeyword($newVal);
                    $entityManager->persist($translation);
                    $entityManager->flush();
                }



            }
        }
    }
} 