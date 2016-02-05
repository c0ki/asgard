<?php

namespace Core\LayoutBundle\Twig;


class GlobalExtension extends \Twig_Extension
{
    public function getName()
    {
        return __CLASS__;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter ('plat', array($this, 'platFilter')),
            new \Twig_SimpleFilter ('array', array($this, 'arrayFilter')),
            new \Twig_SimpleFilter ('regex', array($this, 'regexFilter')),
            new \Twig_SimpleFilter ('date_format', array($this, 'dateFormat')),
        );
    }

    public function platFilter($element, $maxlevel = 3)
    {
        $plat_element = null;
        if (is_array($element) && $maxlevel != 0) {
            $plat_element = array();
            foreach ($element as $key => $value) {
                $plat_element[$key] = $this->platFilter($value, $maxlevel - 1);
            }
        }
        elseif (is_object($element) && $maxlevel != 0) {
            $plat_element = array();
            foreach ((array)$element as $key => $value) {
                if (preg_match('/\\x00.*\\x00(.*)$/', $key, $matches)) {
                    $key = $matches[1];
                }
                $plat_element[$key] = $this->platFilter($value, $maxlevel - 1);
            }
        }
        elseif (!is_array($element) && !is_object($element)) {
            $plat_element = $element;
        }
        else {
            $plat_element = "resource of type " . gettype($element);
        }

        return $plat_element;
    }

    public function arrayFilter($element)
    {
        if (is_array($element) || is_object($element)) {
            return $this->platFilter($element, 1, true);
        }

        return array($element);
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
