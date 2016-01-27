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
        $projectHelper = $this->container->get('project_helper');
        $indexer = $this->container->get('core.indexer.solr');
        $data = array('dataset' => array(), 'schema' => array());

        $criteria =
            array('project' => $projectHelper->getProject(), 'domain' => $projectHelper->getDomain(), '+type:error');
        $criteria = array_filter($criteria);
        $results = $indexer->search('asgard_logs', $criteria, 0, 1, array('daemon'));
        foreach ($results->facets['daemon'] as $serverType => $val) {
            $criteriaServerType = array_merge($criteria, array('+daemon' => $serverType));
            $resultsServerType = $indexer->search('asgard_logs', $criteriaServerType, 0, 1, array('type_s'));
            $firstDate = $resultsServerType->results[0]->date;
            $firstDay = date('Y-m-d', strtotime($firstDate));
            foreach ($resultsServerType->facets['type_s'] as $type => $val) {
                $criteriaType = array_merge($criteriaServerType, array('+type_s' => $type));
                $resultsType = $indexer->search('asgard_logs', $criteriaType, 0, 1,
                    array('date' => array('date' => array('start' => $firstDay, 'gap' => '+1DAY'))));
                foreach ($resultsType->facets['date'] as $date => $nb) {
                    if (!array_key_exists($date, $data['dataset'])) {
                        $data['dataset'][$date]["total"] = 0;
                    }
                    $data['schema']["{$serverType} / {$type}"] = $resultsType->query;
                    $data['dataset'][$date]["date"] = substr($date, 0, 10);
                    $data['dataset'][$date]["{$serverType} / {$type}"] = $nb;
                    $data['dataset'][$date]["total"] += $nb;
                }
            }
        }
        ksort($data['dataset']);
        $data['dataset'] = array_values($data['dataset']);

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }

    public function searchAction($query, $start, $rows) {

        $indexer = $this->container->get('core.indexer.solr');
        $results = $indexer->search('asgard_logs',
            $query,
            $start,
            $rows,
            array('daemon',
                  'type_s',
                  'date' => array('date' => array('gap' => '+1DAY'))));

        return $this->render('LogTrackerBundle:Default:results.html.twig', array('results' => $results));
    }
}
