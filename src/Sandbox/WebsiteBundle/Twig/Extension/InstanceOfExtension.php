<?php

namespace Sandbox\WebsiteBundle\Twig\Extension;


class InstanceOfExtension extends \Twig_Extension{
    public function getTests()
    {
        return [
            'instanceof' =>  new \Twig_Function_Method($this, 'isInstanceof')
        ];
    }

    /**
     * @param $var
     * @param $instance
     * @return bool
     */
    public function isInstanceof($var, $instance) {
        return  $var instanceof $instance;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'instance_of';
    }
}