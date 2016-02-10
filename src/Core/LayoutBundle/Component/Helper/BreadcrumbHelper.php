<?php

namespace Core\LayoutBundle\Component\Helper;

use Core\LayoutBundle\Component\Helper\ToolHelper;
use Symfony\Component\HttpFoundation\RequestStack;

class BreadcrumbHelper
{

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request = null;

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
        $this->request = $requestStack->getMasterRequest();
        $this->toolHelper = $toolHelper;
        $this->template = $template;
    }

    public function getBreadcrumbData(array $params = array()) {
        $routes = array();
        $routes['current'] = array(
            'route'  => $this->request->attributes->get('_route'),
            'params' => $this->request->attributes->get('_route_params'),
        );

        $routes['tool'] = $this->toolHelper->getTool();

        return array_reverse($routes);
    }

    public function getBreadcrumbTemplate() {
        return $this->template;
    }

}