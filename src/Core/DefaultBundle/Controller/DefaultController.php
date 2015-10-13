<?php

namespace Core\DefaultBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $projectHelper = $this->container->get('project_helper');
        $listProjects = $projectHelper->listProjects();

        return $this->render('CoreDefaultBundle:Default:index.html.twig', array('projects' => $listProjects));
    }
}
