<?php

namespace Core\ProjectBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{

    public function indexAction()
    {
        $projectHelper = $this->container->get('project_helper');
        $projects = $projectHelper->listProjects();
        return $this->render('CoreProjectBundle:Default:index.html.twig',
            array('projects' => $projects)
        );
    }

    public function indexProjectAction($project)
    {
        return $this->render('CoreProjectBundle:Default:index_project.html.twig', array('project' => $project));
    }

}
