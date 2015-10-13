<?php

namespace Core\LayoutBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LayoutController extends Controller
{
    public function headerNavAction()
    {
        $projectHelper = $this->container->get('project_helper');
        $listProjects = $projectHelper->listProjects();

        /** @var \Symfony\Component\HttpFoundation\RequestStack $requestStack */
        $requestStack = $this->container->get('request_stack');
        $masterRequest = $requestStack->getMasterRequest();
        $currentUrl = $this->generateUrl($masterRequest->get('_route'), $masterRequest->get('_route_params'));

        foreach ($listProjects as &$project) {
            if ($project['route_path'] == substr($currentUrl, 0, strlen($project['route_path']))) {
                $project['class'] .= ' selected';
            }
        }

        return $this->render('CoreLayoutBundle:Layout:header_nav.html.twig',
                             array('projects' => $listProjects)
        );
    }

    public function asideNavAction()
    {
        $listTools = array();

        /** @var \Symfony\Component\HttpFoundation\RequestStack $requestStack */
        $requestStack = $this->container->get('request_stack');
        $masterRequest = $requestStack->getMasterRequest();
        $currentUrl = $this->generateUrl($masterRequest->get('_route'));
        $currentProject = $masterRequest->get('project');

        if (!is_null($currentProject)) {
            $toolHelper = $this->container->get('tool_helper');
            $listTools = $toolHelper->listTools();

            foreach ($listTools as &$tool) {
                if ($tool['route_path'] == substr($currentUrl, 0, strlen($tool['route_path']))) {
                    $tool['class'] .= ' selected';
                }
            }
        }

        return $this->render('CoreLayoutBundle:Layout:aside_nav.html.twig',
                             array(
                                 'tools' => $listTools,
                                 'project' => $currentProject,
                             )
        );
    }
}

