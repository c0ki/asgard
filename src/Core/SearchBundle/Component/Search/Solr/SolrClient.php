<?php

namespace Core\SearchBundle\Component\Search\Solr;

use \SolrClient as CoreSolrClient;

class SolrClient extends CoreSolrClient
{

    /**
     * @param SolrQuery $query
     * @return  object  boolean -> success  Success or not
     *                  int     -> numFound Results found number
     *                  array   -> results  Results list
     *                  array   -> facets   Facets list
     */
    public function &query(SolrQuery &$query) {
        // Init
        $results = new \StdClass();
        $results->success = 0;
        $results->numFound = 0;
        $results->numStart = 0;
        $results->numRows = 0;
        $results->results = array();
        $results->facets = array();
        $results->query = $query->getQuery();

        // Execute query
        // @var SolrQueryResponse $query_response
        $query_response = parent::query($query);
        $results->success = $query_response->success();
        if ($results->success) {
            $response = $query_response->getResponse();

            $results->numFound = $response->response->numFound;
            $results->numStart = $response->response->start;
            $results->numRows = count($response->response->docs);
            $results->results = $response->response->docs;

            if (isset($response->facet_counts) && !empty($response->facet_counts)) {
                $results->facets = array_merge(
                    (array)$response->facet_counts->facet_queries,
                    (array)$response->facet_counts->facet_fields,
                    (array)$response->facet_counts->facet_dates,
                    (array)$response->facet_counts->facet_ranges
                );
                foreach ($results->facets as $field => $resultFacet) {
                    $results->facets[$field] = (array)$resultFacet;
                    foreach ($results->facets[$field] as $value => $counter) {
                        if ($value == '_undefined_property_name' || trim($value) == '') {
                            unset($results->facets[$field][$value]);
                            if ($counter != 0) {
                                $value = '';
                                $results->facets[$field][$value] = $counter;
                            }
                        }
                        if (!is_numeric($counter)) {
                            unset($results->facets[$field][$value]);
                        }
                    }
                }
                $results->facets = $query->getFacet()->formatResult($results->facets);
            }
        }

        return $results;
    }

}