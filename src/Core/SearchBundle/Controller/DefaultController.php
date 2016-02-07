<?php

namespace Core\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CoreSearchBundle:Default:index.html.twig', array('name' => $name));
    }
}
