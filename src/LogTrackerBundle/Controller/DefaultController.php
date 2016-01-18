<?php

namespace LogTrackerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    public function indexAction() {
        return $this->render('LogTrackerBundle:Default:index.html.twig', array());
    }

    public function viewAction() {
        return $this->render('LogTrackerBundle:Default:view.html.twig', array());
    }

    public function viewDataAction() {
        $indexer = $this->container->get('core.indexer.solr');

        $criteria = array('server_type:apache2 log_type:error');
        $facets = array('type_s', 'date_s');

        $results = $indexer->search('asgard_logs', $criteria, null, null, $facets);
        var_dump($results);
        exit();

        $response = new JsonResponse();
        $response->setData($results);

        return $response;
    }
}
