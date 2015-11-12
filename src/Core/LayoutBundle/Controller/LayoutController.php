<?php

namespace Core\LayoutBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LayoutController extends Controller
{
    public function headerNavAction()
    {
        $requestStack = $this->container->get('request_stack');
        $masterRequest = $requestStack->getMasterRequest();

        $projectHelper = $this->container->get('project_helper');
        $projects = $projectHelper->listProjects();
        return $this->render('CoreLayoutBundle:Layout:header_nav.html.twig',
            array('projects' => $projects)
        );
    }

    public function asideNavAction()
    {
        $toolHelper = $this->container->get('tool_helper');
        $listTools = $toolHelper->listTools();

        return $this->render('CoreLayoutBundle:Layout:aside_nav.html.twig',
            array(
                'tools' => $listTools,
            )
        );
    }
}

