<?php

namespace Core\LayoutBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $layoutHelper = $this->container->get('layout_helper');
        return $this->render('CoreLayoutBundle:Default:index.html.twig');
    }

}

