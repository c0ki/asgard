<?php

namespace Core\SearchBundle\Controller;

use Core\SearchBundle\Component\Search\Solr\SolrQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function formAction($query = null, $_route = null, $_route_params = null) {
        $form = $this->createForm('core_search',
            array('query' => $query, '_route' => $_route, '_route_params' => $_route_params));

        return $this->render('CoreSearchBundle:Default:form.html.twig',
            array('form' => $form->createView(), 'query' => $query));
    }

    public function submitAction(Request $request) {
        $query = null;

        // Form init
        $form = $this->createForm('core_search');
        $form->handleRequest($request);

        // Redirect to valid url if form valid
        if ($form->isValid()) {
            $query = $form->get('query')->getData();
            SolrQuery::formatCriteria($query);
            parse_str($form->get('_route_params')->getData(), $routeParams);
            $routeParams = array_merge($routeParams, array('query' => $query));

            return new RedirectResponse($this->generateUrl($form->get('_route')->getData(), $routeParams));
        }
        $this->addFlash('error', "Invalid search query");
        $url = $this->container->get('request_stack')->getMasterRequest()->headers->get('referer');

        return new RedirectResponse($url);
    }
}
