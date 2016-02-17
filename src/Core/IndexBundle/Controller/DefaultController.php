<?php

namespace Core\IndexBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CoreIndexBundle:Default:index.html.twig', array('name' => $name));
    }
}
