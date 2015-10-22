<?php

namespace Core\LayoutBundle\Component\Helper;


use Symfony\Component\DependencyInjection\ContainerInterface;

class ToolHelper
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container = null;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function listTools()
    {
        $listTools = array();
        $routes = $this->container->get('router')->getRouteCollection()->all();
        foreach ($routes as $name => $route) {
            if (preg_match('/_tool_homepage$/', $name)) {
                if (preg_match_all('/{([^}]+)}/', $route->getPath(), $matches)) {
                    foreach ($matches[1] as $param) {
                        if (is_null($route->getDefault($param))) {
                            continue 2;
                        }
                    }
                }
                $listTools[$name]['route_name'] = $name;
                if ($route->hasOption('class')) {
                    $listTools[$name]['class'] = $route->getOption('class');
                }
                if ($route->hasOption('label')) {
                    $listTools[$name]['label'] = $route->getOption('label');
                }
                else {
                    $listTools[$name]['label'] = ucwords(str_replace('_', ' ', str_replace('_tool_homepage', '', $name)));
                }
            }
        }
        return $listTools;
    }

}