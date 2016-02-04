<?php

namespace LogTrackerBundle\Controller;

use Core\CoreBundle\Component\Indexer\SolrQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    public function indexAction() {
        $projectHelper = $this->container->get('project_helper');
        $indexer = $this->container->get('core.indexer.solr');
        $data = array('daemon' => array(), 'type' => array(), 'daemontype' => array());

        $criteria = array();
        if ($projectHelper->hasProject()) {
            $criteria['project'] = $projectHelper->getProject()->getName();
        }
        if ($projectHelper->hasDomain()) {
            $criteria['domain'] = $projectHelper->getDomain()->getName();
        }
        $criteria = array_filter($criteria);
        $results = $indexer->search('asgard_logs', $criteria, 0, 1, array('daemon', 'type'));
        $data['daemon'] = $results->facets['daemon'];
        $data['type'] = $results->facets['type'];
        foreach ($results->facets['daemon'] as $daemon => $val) {
            $criteriaDaemon = array_merge($criteria, array('+daemon' => $daemon));
            $resultsDaemon = $indexer->search('asgard_logs', $criteriaDaemon, 0, 1, array('type'));
            $data['daemontype'][$daemon] = $resultsDaemon->facets['type'];
        }

        return $this->render('LogTrackerBundle:Default:index.html.twig', array('list' => $data));
    }

    public function viewAction($daemon, $type) {
        $query = array();
        $query['+daemon'] = $daemon;
        $query['+type'] = $type;
        $query = array_filter($query);
        SolrQuery::formatCriteria($query);
        return $this->render('LogTrackerBundle:Default:view.html.twig', array('daemon' => $daemon, 'type' => $type, 'query' => $query));
    }

    public function viewDataAction($query, $preventMonth) {
        $projectHelper = $this->container->get('project_helper');
        $indexer = $this->container->get('core.indexer.solr');
        $data = array('dataset' => array(), 'schema' => array());

        $criteria = array($query);
        if ($projectHelper->hasProject()) {
            $criteria['+project'] = $projectHelper->getProject()->getName();
        }
        if ($projectHelper->hasDomain()) {
            $criteria['+domain'] = $projectHelper->getDomain()->getName();
        }
        $criteria = array_filter($criteria);

        $results = $indexer->search('asgard_logs', $criteria, 0, 1, array('subtype_s'));
        if (empty($preventMonth) || !is_numeric($preventMonth)) {
            $preventMonth = 6;
        }
        $firstDay = date('Y-m-d', strtotime("-{$preventMonth} MONTH"));
        foreach ($results->facets['subtype_s'] as $name => $val) {
            if ($name === '') {
                $criteriaFacet = array_merge($criteria, array("-subtype_s" => '*'));
                $name = "unknown";
            }
            else {
                $criteriaFacet = array_merge($criteria, array("+subtype_s" => $name));
            }
            $resultsSubtype = $indexer->search('asgard_logs',
                                            $criteriaFacet,
                                            0,
                                            1,
                                            array('date' => array('date' => array('start' => $firstDay,
                                                                                  'gap' => '+1DAY'))));
            foreach ($resultsSubtype->facets['date'] as $date => $nb) {
                if (!array_key_exists($date, $data['dataset'])) {
                    $data['dataset'][$date]["preview"] = 0;
                }
                $data['schema'][$name] = $resultsSubtype->query;
                $data['dataset'][$date]["date"] = substr($date, 0, 10);
                $data['dataset'][$date][$name] = $nb;
                $data['dataset'][$date]["preview"] += $nb / 1000;
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

        return $this->render('LogTrackerBundle:List:results.html.twig', array('results' => $results));
    }
}
