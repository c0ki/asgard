<?php

namespace LogTrackerBundle\Controller;

use Core\SearchBundle\Component\Search\SearchException;
use Core\SearchBundle\Component\Search\Solr\SolrQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller
{
    public function indexAction() {
        $projectHelper = $this->container->get('project_helper');
        $searchService = $this->container->get('search');

        $criteria = array();
        if ($projectHelper->hasProject()) {
            $criteria['+project'] = $projectHelper->getProject()->getName();
        }
        if ($projectHelper->hasDomain()) {
            $criteria['+domain'] = $projectHelper->getDomain()->getName();
        }

        $results = $searchService->search('asgard_logs', $criteria, 0, 1, array('daemon'));
        $daemons = array_keys($results->facets['daemon']);
        foreach ($daemons as $daemon) {
            $criteriaDaemon = $criteria + array('+daemon' => $daemon);
            $results = $searchService->search('asgard_logs', $criteriaDaemon, 0, 1, array('type'));
            foreach ($results->facets['type'] as $type => $nb) {
                if ($type == "unknown") {
                    $criteriaType = $criteriaDaemon + array('-type' => '*');
                }
                else {
                    $criteriaType = $criteriaDaemon + array('+type' => $type);
                }
                SolrQuery::formatCriteria($criteriaType);
                $data[$daemon][$type] = array('query' => $criteriaType, 'nb' => $nb);
            }
        }

        return $this->render('LogTrackerBundle:Default:index.html.twig', array('list' => $data));
    }

    public function chartAction($query, $preventMonth) {
        // Redirect to valid url if query have date or not formatted
        $initialQuery = $query;
        $query = preg_replace('/\+?date:[\d-:]+/', '', $query);
        SolrQuery::formatCriteria($query);
        if ($query != $initialQuery) {
            return new RedirectResponse($this->generateUrl('log_tracker_chart', array('query' => $query)));
        }

        return $this->render('LogTrackerBundle:Default:chart.html.twig',
            array('query' => $query, 'preventMonth' => $preventMonth));
    }

    public function dataAction($query, $preventMonth) {
        $projectHelper = $this->container->get('project_helper');
        $searchService = $this->container->get('search');
        $data = array('dataset' => array(), 'schema' => array());

        $criteria = array($query);
        if ($projectHelper->hasProject()) {
            $criteria['+project'] = $projectHelper->getProject()->getName();
        }
        if ($projectHelper->hasDomain()) {
            $criteria['+domain'] = $projectHelper->getDomain()->getName();
        }
        $criteria = array_filter($criteria);

        $results = $searchService->search('asgard_logs', $criteria, 0, 1, array('subtype_s'));
        $firstDay = date('Y-m-d', strtotime("-{$preventMonth} MONTH"));
        $lastDay = date('Y-m-d');
        foreach ($results->facets['subtype_s'] as $name => $val) {
            if ($name === '') {
                $criteriaFacet = array_merge($criteria, array("-subtype_s" => '*'));
                $name = "unknown";
            }
            else {
                $criteriaFacet = array_merge($criteria, array("+subtype_s" => $name));
            }
            $resultsSubtype = $searchService->search('asgard_logs',
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
                $date = substr($date, 0, 10);
                if (!array_key_exists($date, $data['dataset'])) {
                    $data['dataset'][$date]["preview"] = 0;
                }
                $data['schema'][$name] = $initialCriteriaFacet;
                $data['dataset'][$date]["date"] = $date;
                $data['dataset'][$date][$name] = $nb;
                $data['dataset'][$date]["preview"] += $nb / 1000;
            }
        }
        if (!empty($data['dataset']) && !array_key_exists($lastDay, $data['dataset'])) {
            $data['dataset'][$lastDay]['date'] = $lastDay;
            $data['dataset'][$lastDay]['preview'] = 0;
        }
        if (!empty($data['dataset']) && !array_key_exists($firstDay, $data['dataset'])) {
            $data['dataset'][$firstDay]['date'] = $firstDay;
            $data['dataset'][$firstDay]['preview'] = 0;
        }

        ksort($data['dataset']);
        $data['dataset'] = array_values($data['dataset']);

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }

    public function searchAction($query, $start, $rows) {
        // Redirect to valid url if query not formatted
        $initialQuery = $query;
        SolrQuery::formatCriteria($query);
        if ($query != $initialQuery) {
            return new RedirectResponse($this->generateUrl('log_tracker_search', array('query' => $query)));
        }

        $facets = array('project', 'domain', 'daemon', 'type', 'subtype_s',
                        'date' => array('date' => array('gap' => '+1DAY'), 'order' => SolrQuery::ORDER_DESC));

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

        $searchService = $this->container->get('search');
        try {
            $results = $searchService->search('asgard_logs',
                $query,
                $start,
                $rows,
                $facets);
        }
        catch (SearchException $e) {
            $this->addFlash('error', $e->getMessage());

            return new RedirectResponse($this->generateUrl('log_tracker_tool_homepage'));
        }
        $results->start = $start;
        $results->rows = $rows;

        return $this->render('LogTrackerBundle:List:results.html.twig',
            array('results' => $results, 'query' => $initialQuery));
    }
}
