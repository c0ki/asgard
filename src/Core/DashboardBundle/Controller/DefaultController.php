<?php

namespace Core\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('CoreDashboardBundle:Default:index.html.twig', array());
    }
}
