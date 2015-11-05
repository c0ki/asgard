<?php

namespace TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $name = 'tutu';
        return $this->render('TestBundle:Default:index.html.twig', array('name' => $name));
    }
}
