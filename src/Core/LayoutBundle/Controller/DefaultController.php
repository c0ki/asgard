<?php

namespace Core\LayoutBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('CoreLayoutBundle:Default:index.html.twig');
    }

    public function getUrlAction()
    {
        $urlHelper = $this->container->get('url_helper');
        $url = $this->container->get('request_stack')->getMasterRequest()->get('url');

        $result = $urlHelper->getContentUrl($url);

        $response = new Response();

        if (array_key_exists('error', $result) && $result['error']) {
            $response->setContent($result['error']);
        }
        else {
            $response->setContent($result['content']);
            if (array_key_exists('header', $result)
                && is_array($result['header'])
                && !empty($result['header'])
            ) {
                $response->headers->add($result['header']);
            }

        }

        return $response;
    }
}

