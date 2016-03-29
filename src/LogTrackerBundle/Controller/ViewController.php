<?php

namespace LogTrackerBundle\Controller;

use Core\SearchBundle\Component\Search\SearchException;
use Core\SearchBundle\Component\Search\Solr\SolrQuery;
use Core\SearchengineBundle\Component\Search\SearchQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ViewController extends Controller
{
    public function indexAction($query, $start, $rows, $onlydata)
    {
        $projectHelper = $this->container->get('project_helper');
        $searchClient = $this->container->get('searchengine.client.logs');

        if (!is_array($query)) {
            $query = array($query);
        }

        if ($projectHelper->hasProject()) {
            $query['+project'] = $projectHelper->getProject()->getName();
        }
        if ($projectHelper->hasDomain()) {
            $query['+domain'] = $projectHelper->getDomain()->getName();
        }

        $facets = '*';
        if ($onlydata) {
            $facets = null;
        }

        $response = $searchClient->search($query, $start, $rows, $facets);
        if (!$response->success()) {
            return $this->render('LogTrackerBundle:View:error.html.twig', array('message' => "tutu"));
        }

        if ($onlydata) {
            return $this->render('LogTrackerBundle:View:elements.html.twig',
                array('docs'   => $response->getResponse()->docs,
                      'fields' => $response->getFields()));
        }

        return $this->render('LogTrackerBundle:View:index.html.twig', array('response' => $response));
    }

}
