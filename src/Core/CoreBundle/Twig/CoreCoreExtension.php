<?php

namespace Core\CoreBundle\Twig;

class CoreCoreExtension extends \Twig_Extension
{
    public function __construct() {
    }

    public function getName() {
        return __CLASS__;
    }

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter ('regex', array($this, 'regexFilter')),
            new \Twig_SimpleFilter ('date_format', array($this, 'dateFormat')),
        );
    }

    public function regexFilter($string, $from, $to = null) {
        if (!is_array($from)) {
            return preg_replace($string, $from, $to);
        }
        else {
            if (is_null($to) && is_array($from)) {
                $to = array_values($from);
                $from = array_keys($from);
            }

            return preg_replace($from, $to, $string);
        }
    }

    public function dateFormat($date, $format) {
        if ($date instanceof \DateTime) {
            return $date->format($format);
        }

        return null;
    }

}
