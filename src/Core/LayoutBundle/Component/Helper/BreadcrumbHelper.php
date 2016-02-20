<?php

namespace Core\LayoutBundle\Component\Helper;

use Core\LayoutBundle\Component\Helper\ToolHelper;
use Symfony\Component\HttpFoundation\RequestStack;

class BreadcrumbHelper
{

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $masterRequest = null;

    /**
     * @var ToolHelper
     */
    protected $toolHelper;

    /**
     * @var String
     */
    protected $template;

    public function __construct(RequestStack $requestStack, ToolHelper $toolHelper, $template)
    {
        $this->masterRequest = $requestStack->getMasterRequest();
        $this->toolHelper = $toolHelper;
        $this->template = $template;
    }

    public function getBreadcrumbData(array $params = array()) {
        $currentTitle = 'Default';
        if (preg_match('/::(\w+)Action$/', $this->masterRequest->attributes->get('_controller'), $matches)) {
            $currentTitle = $matches[1];
        }

        $routes = array();
        $routes[$currentTitle] = array(
            'route'  => $this->masterRequest->attributes->get('_route'),
            'params' => $this->masterRequest->attributes->get('_route_params'),
        );

        $routes['tool'] = $this->toolHelper->getTool();
        $routes['root'] = array('label' => 'home', 'logo' => 'home', 'route' => 'core_layout_root');

        $routes = array_filter($routes);

        return array_reverse($routes);
    }

    public function getBreadcrumbTemplate() {
        return $this->template;
    }

}