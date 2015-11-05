<?php

namespace Core\LayoutBundle\Twig;

use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Core\LayoutBundle\Component\Helper\AlertHelper;

class AlertExtension extends RoutingExtension
{

    /**
     * @var \Core\LayoutBundle\Component\Helper\AlertHelper
     */
    protected $alertHelper;

    public function __construct(AlertHelper $alertHelper)
    {
        $this->alertHelper = $alertHelper;
    }

    public function getName()
    {
        return __CLASS__;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction ('has_alert', array(
                $this->alertHelper,
                'has'
            )),
            new \Twig_SimpleFunction ('get_alerts', array(
                $this->alertHelper,
                'all'
            )),
        );
    }

}
