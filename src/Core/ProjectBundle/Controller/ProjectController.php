<?php

namespace Core\ProjectBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ProjectController extends Controller
{
    public function indexAction($project)
    {
        return $this->render('CoreProjectBundle:Project:index.html.twig', array('project' => $project));
    }
}
