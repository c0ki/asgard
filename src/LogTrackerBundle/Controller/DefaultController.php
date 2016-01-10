<?php

namespace LogTrackerBundle\Controller;

use Core\CoreBundle\Component\Indexer\SolRIndexer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction() {
        return $this->render('LogTrackerBundle:Default:index.html.twig', array());
    }

    public function viewAction() {
        $indexer = $this->container->get('core.indexer.solr');

        $criteria = array('*:*');
        $facets = array('server', 'server_type' => array('mincount' => '5'));

        $results = $indexer->search('asgard_logs', $criteria, null, null, $facets);
        var_dump($results);

        return $this->render('LogTrackerBundle:Default:view.html.twig', array());
    }
}
