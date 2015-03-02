<?php
namespace Sandbox\WebsiteBundle\Twig\Extension;


use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Extension;

class HostExtension extends Twig_Extension {

    protected $container;
    private static $host;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getGlobals()
    {
        if(!self::$host){
            // Retrieve the Request object form the container and get the hostname
            $hostname = $this->container->get('request')->getHost();
            self::$host = $this->container->get('doctrine.orm.entity_manager')->getRepository('SandboxWebsiteBundle:Host')
                ->findOneBy(['name' => $hostname]);
        }

        return array('host' => self::$host);
    }


    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'travelbase.getHost';
    }
}