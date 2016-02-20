<?php

namespace Core\LayoutBundle\Component\Helper;


use Symfony\Bundle\FrameworkBundle\Routing\Router;

class ToolHelper
{

    /**
     * @var Router
     */
    protected $router = null;

    public function __construct(Router $router)
    {
        $this->router = $router;

    }

    /**
     * @return array
     */
    public function listTools()
    {
        if (empty($this->tools)) {
            $routes = $this->router->getRouteCollection()->all();
            foreach ($routes as $name => $route) {
                if (preg_match('/_tool_homepage$/', $name)) {
                    if (preg_match_all('/{([^}]+)}/', $route->getPath(), $matches)) {
                        foreach ($matches[1] as $param) {
                            if (is_null($route->getDefault($param))) {
                                continue 2;
                            }
                        }
                    }
                    $this->tools[$name]['route'] = $name;
                    if ($route->hasOption('class')) {
                        $this->tools[$name]['class'] = $route->getOption('class');
                    }
                    if ($route->hasOption('label')) {
                        $this->tools[$name]['label'] = $route->getOption('label');
                    } else {
                        $this->tools[$name]['label'] = ucwords(str_replace('_',
                            ' ',
                            str_replace('_tool_homepage', '', $name)));
                    }
                }
            }
        }

        ksort($this->tools);

        return $this->tools;
    }

    /**
     * @var array
     */
    private $tools = array();

    public function getTool()
    {
        $currentPath = $this->router->getContext()->getPathInfo();
        foreach ($this->listTools() as $tool) {
            $route = $this->router->getRouteCollection()->get($tool['route']);
            if (preg_match("#^{$route->getPath()}#", $currentPath)) {
                return $tool;
            }
        }
        return null;
    }

}