<?php

namespace Sandbox\WebsiteBundle\Helper;


use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class HostHelper {

    private $request;
    private $em;

    //prevent multiple queries if host not found
    private static $isHostSet = false;

    private static $host;

    function __construct(EntityManager $em,ContainerInterface $container)
    {
        $this->request = $container->get('request');
        $this->em = $em;
    }

    public function getHost()
    {
        if(!self::$isHostSet){
            self::$host = $this->em->getRepository('SandboxWebsiteBundle:Host')
                ->findOneBy(['name' => $this->request->getHost()]);
            self::$isHostSet = true;
        }

        return self::$host;
    }

}