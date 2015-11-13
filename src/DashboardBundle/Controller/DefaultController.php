<?php

namespace DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $project = $this->get('request_stack')->getMasterRequest()->attributes->get('@project');
        return $this->render('DashboardBundle:Default:index.html.twig', array('project' => $project));
    }
}
