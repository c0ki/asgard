<?php

namespace LogTrackerBundle\Controller;

use Core\CoreBundle\Component\Indexer\SolrQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

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
            if (count($resultsDaemon->facets['type']) != 1
                || !array_key_exists('unknown', $resultsDaemon->facets['type'])
            ) {
                $data['daemontype'][$daemon] = $resultsDaemon->facets['type'];
            }
        }

        return $this->render('LogTrackerBundle:Default:index.html.twig', array('list' => $data));
    }

    public function chartAction(Request $request, $query) {
        // Form init
        $form = $this->createForm('core_search', array('query' => $query));
        $form->handleRequest($request);

        // Redirect to valid url if form valid
        if ($form->isValid()) {
            $query = $form->get('query')->getData();
            SolrQuery::formatCriteria($query);

            return new RedirectResponse($this->generateUrl('log_tracker_chart', array('query' => $query)));
        }

        // Redirect to valid url if query have date or not formatted
        $initialQuery = $query;
        $query = preg_replace('/\+?date:[\d-:]+/', '', $query);
        SolrQuery::formatCriteria($query);
        if ($query != $initialQuery) {
            return new RedirectResponse($this->generateUrl('log_tracker_chart', array('query' => $query)));
        }

        return $this->render('LogTrackerBundle:Default:chart.html.twig',
            array('form' => $form->createView(), 'query' => $query));
    }

    public function dataAction($query, $preventMonth) {
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
                                                      'gap'   => '+1DAY'))));
            $initialCriteriaFacet = $criteriaFacet;
            unset($initialCriteriaFacet['+project']);
            unset($initialCriteriaFacet['+domain']);
            SolrQuery::formatCriteria($initialCriteriaFacet);
            foreach ($resultsSubtype->facets['date'] as $date => $nb) {
                if (!array_key_exists($date, $data['dataset'])) {
                    $data['dataset'][$date]["preview"] = 0;
                }
                $data['schema'][$name] = $initialCriteriaFacet;
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

    public function searchAction(Request $request, $query, $start, $rows) {
        // Form init
        $form = $this->createForm('core_search', array('query' => $query));
        $form->handleRequest($request);

        // Redirect to valid url if form valid
        if ($form->isValid()) {
            $query = $form->get('query')->getData();
            SolrQuery::formatCriteria($query);

            return new RedirectResponse($this->generateUrl('log_tracker_search', array('query' => $query)));
        }

        // Redirect to valid url if query not formatted
        $initialQuery = $query;
        SolrQuery::formatCriteria($query);
        if ($query != $initialQuery) {
            return new RedirectResponse($this->generateUrl('log_tracker_search', array('query' => $query)));
        }

        $facets = array('project', 'domain', 'daemon', 'type', 'subtype_s',
                        'date' => array('date' => array('gap' => '+1DAY')));

        $projectHelper = $this->container->get('project_helper');
        if ($query == '*') {
            $query = null;
        }
        $initialQuery = $query;
        $query = array($query);
        if ($projectHelper->hasProject()) {
            $query['+project'] = $projectHelper->getProject()->getName();
            $facets = array_filter($facets, function ($value) {
                return $value != 'project';
            });
        }
        if ($projectHelper->hasDomain()) {
            $query['+domain'] = $projectHelper->getDomain()->getName();
            $facets = array_filter($facets, function ($value) {
                return $value != 'domain';
            });
        }

        $indexer = $this->container->get('core.indexer.solr');
        $results = $indexer->search('asgard_logs',
            $query,
            $start,
            $rows,
            $facets);
        $results->start = $start;
        $results->rows = $rows;

        return $this->render('LogTrackerBundle:List:results.html.twig',
            array('form' => $form->createView(), 'results' => $results, 'query' => $initialQuery));
    }
}
