<?php

namespace Core\LayoutBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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

        if (array_key_exists('error', $result) && $result['error']) {
            print($result['error']);
            exit();
        }

        if (array_key_exists('header', $result)
            && is_array($result['header'])
            && !empty($result['header'])
        ) {
            foreach ($result['header'] as $header) {
                header($header);
            }
        }

        print($result['content']);
        exit();
    }
}

