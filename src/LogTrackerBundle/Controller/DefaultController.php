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

    public function viewDataErrorAction() {
        $indexer = $this->container->get('core.indexer.solr');
        $data = array();

        $criteria = array('log_type:error');
        $results = $indexer->search('asgard_logs', $criteria, 0, 1, array('server_type'));
        foreach ($results->facets['server_type'] as $serverType => $val) {
            $criteriaServerType = array_merge($criteria, array('server_type' => $serverType));
            $resultsServerType = $indexer->search('asgard_logs', $criteriaServerType, 0, 1, array('type_s'));
            foreach ($resultsServerType->facets['type_s'] as $type => $val) {
                $criteriaType = array_merge($criteriaServerType, array('type_s' => $type));
                $resultsType = $indexer->search('asgard_logs', $criteriaType, 0, 1,
                    array('date' => array('date' => array('gap' => '+1DAY'), 'mincount' => 0)));
                foreach ($resultsType->facets['date'] as $date => $nb) {
                    if (!is_numeric($nb)) {
                        continue;
                    }
                    if (!array_key_exists($date, $data)) {
                        $data[$date]["total"] = 0;
                    }
                    $data[$date]["date"] = $date;
                    $data[$date]["{$serverType} / {$type}"] = $nb;
                    $data[$date]["total"] += $nb;
                    $data[$date]["query_{$serverType} / {$type}"] = "server_type:'{$serverType}' type_s:'{$type}'";
                }
            }
        }
        $data = array_values($data);

//        var_dump($data);
//        exit();

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }

    public function searchAction($query) {

        $indexer = $this->container->get('core.indexer.solr');
        $results = $indexer->search('asgard_logs', $query, 0, 10,
            array('server_type', 'type_s', 'date' => array('date' => array('gap' => '+1WEEK'))));

        return $this->render('LogTrackerBundle:Default:results.html.twig', array('results' => $results));
    }
}
