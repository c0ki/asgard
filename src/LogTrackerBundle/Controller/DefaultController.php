<?php

namespace LogTrackerBundle\Controller;

use Core\SearchBundle\Component\Search\SearchException;
use Core\SearchBundle\Component\Search\Solr\SolrQuery;
use Core\SearchengineBundle\Component\Search\SearchQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $projectHelper = $this->container->get('project_helper');
        $searchClient = $this->container->get('searchengine.client.logs');
        $searchQuery = $this->container->get('searchengine.query.logs');

        $data = array();

        $criteria = array();
        if ($projectHelper->hasProject()) {
            $criteria['+project'] = $projectHelper->getProject()->getName();
        }
        if ($projectHelper->hasDomain()) {
            $criteria['+domain'] = $projectHelper->getDomain()->getName();
        }
        $response = $searchClient->search($criteria, 0, 1, array('daemon'));
        if ($response->success()) {
            foreach (array_keys($response->getFacet('daemon')) as $daemon) {
                $criteriaDaemon = array('+daemon' => $daemon);
                $responseDaemon = $searchClient->search(array_merge($criteria, $criteriaDaemon), 0, 1, array('type'));
                foreach ($responseDaemon->getFacet('type') as $type => $nb) {
                    if ($type == '') {
                        $criteriaType = array_merge($criteriaDaemon, array('-type' => '*'));
                    } else {
                        $criteriaType = array_merge($criteriaDaemon, array('+type' => $type));
                    }
                    $criteriaType = $searchQuery::formatQuery($criteriaType);
                    $data[$daemon][$type] = array('query' => $criteriaType, 'nb' => $nb);
                }
            }
        }

        return $this->render('LogTrackerBundle:Default:index.html.twig', array('list' => $data));
    }

    public function chartAction($query, $preventMonth)
    {
        $searchQuery = $this->container->get('searchengine.query.logs');

        // Redirect to valid url if query have date or not formatted
        $queryFormatted = preg_replace('/\+?date:[\d-:]+/', '', $query);
        $queryFormatted = $searchQuery::formatQuery($queryFormatted);
        if ($queryFormatted != $query) {
            return new RedirectResponse($this->generateUrl('log_tracker_chart', array('query' => $queryFormatted)));
        }

        return $this->render('LogTrackerBundle:Default:chart.html.twig',
            array('query' => $query, 'preventMonth' => $preventMonth));
    }

    public function dataAction($query, $preventMonth)
    {
        $projectHelper = $this->container->get('project_helper');
        $searchClient = $this->container->get('searchengine.client.logs');
        $searchQuery = $this->container->get('searchengine.query.logs');
        $data = array('dataset' => array(), 'schema' => array());

        $criteriaProject = array();
        if ($projectHelper->hasProject()) {
            $criteriaProject['+project'] = $projectHelper->getProject()->getName();
        }
        if ($projectHelper->hasDomain()) {
            $criteriaProject['+domain'] = $projectHelper->getDomain()->getName();
        }
        $criteria = array_filter(array($query));
        $response = $searchClient->search(array_merge($criteria, $criteriaProject), 0, 1, array('subtype_s'));
        $firstDay = date('Y-m-d', strtotime("-{$preventMonth} MONTH"));
        $lastDay = date('Y-m-d');
        foreach ($response->getFacet('subtype_s') as $name => $val) {
            if ($name === '') {
                $criteriaFacet = array_merge($criteria, array("-subtype_s" => '*'));
                $name = "unknown";
            } else {
                $criteriaFacet = array_merge($criteria, array("+subtype_s" => $name));
            }
            $responseSubtype = $searchClient->search(
                array_merge($criteriaFacet, $criteriaProject),
                0,
                1,
                array('date' => array('date' => array('start' => $firstDay,
                                                      'gap'   => '+1DAY'))));
            $criteriaFacet = $searchQuery::formatQuery($criteriaFacet);

            foreach ($responseSubtype->getFacet('date') as $date => $nb) {
                $date = substr($date, 0, 10);
                if (!array_key_exists($date, $data['dataset'])) {
                    $data['dataset'][$date]["preview"] = 0;
                }
                $data['schema'][$name] = $criteriaFacet;
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

    public function searchAction($query, $start, $rows)
    {
        $searchClient = $this->container->get('searchengine.client.logs');
        $searchQuery = $this->container->get('searchengine.query.logs');
        // Redirect to valid url if query not formatted
        $queryFormatted = $searchQuery::formatQuery($query);
        if ($queryFormatted != $query) {
            return new RedirectResponse($this->generateUrl('log_tracker_search', array('query' => $queryFormatted)));
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

        try {
            $response = $searchClient->search(
                $query,
                $start,
                $rows,
                $facets);
        }
        catch (SearchException $e) {
            $this->addFlash('error', $e->getMessage());

            return new RedirectResponse($this->generateUrl('log_tracker_tool_homepage'));
        }
        return $this->render('LogTrackerBundle:List:results.html.twig',
            array('response' => $response, 'query' => $initialQuery));
    }
}
