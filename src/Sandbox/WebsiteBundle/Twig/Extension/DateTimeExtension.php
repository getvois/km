<?php

namespace Sandbox\WebsiteBundle\Twig\Extension;


use Twig_Extension;
use Twig_Filter_Method;

class DateTimeExtension extends Twig_Extension
{
    public function getFilters()
    {
        return array(
            'datetime' => new Twig_Filter_Method($this, 'datetime', array('is_safe' => array('html')))
        );
    }

    public function datetime($d, $format = "%B %e, %Y %H:%M")
    {
        if ($d instanceof \DateTime) {
            $d = $d->getTimestamp();
        }

        return strftime($format, $d);
    }

    public function getName()
    {
        return 'datetime.locale';
    }
}