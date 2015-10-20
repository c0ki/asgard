<?php

namespace Core\ProjectBundle\Component\Helper;


use Symfony\Component\DependencyInjection\ContainerInterface;

class ProjectHelper
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
    public function all()
    {
        $repository = $this->container->get('doctrine')->getRepository('CoreProjectBundle:Project');
        return $repository->findAll();
    }

//    public function listProjects()
//    {
//        $listProjects = array();
//        $routes = $this->container->get('router')->getRouteCollection()->all();
//        foreach ($routes as $name => $route) {
//            if (preg_match('/_homepage$/', $name)) {
//                if (preg_match('/{project}/', $route->getPath())) {
//                    continue;
//                }
//                if (preg_match_all('/{([^}]+)}/', $route->getPath(), $matches)) {
//                    foreach ($matches[1] as $param) {
//                        if (is_null($route->getDefault($param))) {
//                            continue 2;
//                        }
//                    }
//                }
//                $listProjects[$name]['route_name'] = $name;
//                $listProjects[$name]['route_path'] = $route->getPath();
//                if ($route->hasOption('class')) {
//                    $listProjects[$name]['class'] = $route->getOption('class');
//                }
//                if ($route->hasOption('label')) {
//                    $listProjects[$name]['label'] = $route->getOption('label');
//                }
//                else {
//                    $listProjects[$name]['label'] = ucwords(str_replace('_', ' ', str_replace('_homepage', '', $name)));
//                }
//            }
//        }
//        return $listProjects;
//    }

}