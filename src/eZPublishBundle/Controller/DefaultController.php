<?php

namespace eZPublishBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('eZPublishBundle:Default:index.html.twig');
    }
}
