<?php

namespace Sandbox\WebsiteBundle\EventListener;


use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleListener implements EventSubscriberInterface
{
    private $defaultLocale;
    private $em;

    public function __construct($defaultLocale = 'en', EntityManager $em)
    {
        $this->em = $em;
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {

        $request = $event->getRequest();
        $host = $this->em->getRepository('SandboxWebsiteBundle:Host')
            ->findOneBy(['name' => $request->getHost()]);

        if($host){
            if($host->getLocale()){
                $request->setLocale($host->getLocale());
            }
        }else{
//            if (!$request->hasPreviousSession()) {
//                return;
//            }
//
//            // try to see if the locale has been set as a _locale routing parameter
//            if ($locale = $request->attributes->get('_locale')) {
//                $request->getSession()->set('_locale', $locale);
//            } else {
//                // if no explicit locale has been set on this request, use one from the session
//                $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
//            }
        }


    }

    public static function getSubscribedEvents()
    {
        return array(
            // must be registered before the default Locale listener
            KernelEvents::REQUEST => array(array('onKernelRequest', 17)),
        );
    }
}
