<?php

namespace Core\LayoutBundle\Twig;

use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Ind\DefaultBundle\Component\Routing\SiteAccessRouter;

class CoreLayoutExtension extends RoutingExtension
{

    public function getName()
    {
        return __CLASS__;
    }

    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $siteAccessRouter = new SiteAccessRouter($container);
        parent::__construct($siteAccessRouter);
    }
}
