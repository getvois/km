<?php

namespace Sandbox\WebsiteBundle\Twig\Extension;


use IntlDateFormatter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Environment;

class DatelocaleExtension extends \Twig_Extension{

    private $locale;

    function __construct(ContainerInterface $container)
    {
        $locale = $container->get('request')->getLocale();
        if($locale == 'ee') $locale = 'fi';
        $this->locale = $locale;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('datelocale', array($this, 'datelocale'), array('needs_environment' => true)),
        );
    }


    public function datelocale(Twig_Environment $env, $date, $dateFormat = 'long', $timeFormat = 'short', $locale = null, $timezone = null, $format = null)
    {
        if(!$locale) $locale = $this->locale;

        $date = twig_date_converter($env, $date, $timezone);

        $formatValues = array(
            'none'   => IntlDateFormatter::NONE,
            'short'  => IntlDateFormatter::SHORT,
            'medium' => IntlDateFormatter::MEDIUM,
            'long'   => IntlDateFormatter::LONG,
            'full'   => IntlDateFormatter::FULL,
        );

        $formatter = IntlDateFormatter::create(
            $locale,
            $formatValues[$dateFormat],
            $formatValues[$timeFormat],
            $date->getTimezone()->getName(),
            IntlDateFormatter::GREGORIAN,
            $format
        );

        $date = $formatter->format($date->getTimestamp());

        if($dateFormat == 'medium'){
            if(preg_match('/[0-9]{4}/', $date, $matches)){
                $year = $matches[1];
                $pos = strpos($date, $year);
                $date = str_split($date, $pos+1)[0];
            }
        }

        return $date;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'datelocale';
    }
}