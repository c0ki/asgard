<?php

namespace Core\SearchengineBundle\Component\Search\Solr;

use Core\SearchengineBundle\Component\Search\SearchResponse as CoreSearchResponse;

class SearchResponse implements CoreSearchResponse
{
    /**
     * @var \SolrResponse
     */
    protected $response;

    public function __construct(\SolrResponse $response)
    {
        $this->response = $response;
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->response, $name)) {
            return $this->response->$name($arguments);
        }
        throw new SearchException("Call undefined method '{$name}'");
    }

    public function getResponse()
    {
        if (!$this->success()) {
            return null;
        }
        $completeResponse = $this->response->getResponse();

        $response = $completeResponse->response;
        $response->rows = $this->response->response_rows;
        $response->facets = $this->getFacets();

        parse_str($this->response->getRawRequest(), $rawRequest);
        if (array_key_exists('q', $rawRequest)) {
            $response->query = $rawRequest['q'];
            $response->queryParsed = SearchQuery::parseQuery($response->query);
        }

        return $response;
    }

    public function getFacets()
    {
        if (!$this->success()) {
            return null;
        }
        $facets = array();
        $completeFacets = $this->response->getResponse()->facet_counts;
        if (!empty($completeFacets)) {
            $properties = get_object_vars($completeFacets);
            if (!empty($properties)) {
                foreach ($properties as $propertyName => $property) {
                    $property = (array)$property;
                    if (!empty($property)) {
                        foreach ($property as $name => $values) {
                            $values = (array)$values;
                            foreach ($values as $key => $value) {
                                if ($key == '_undefined_property_name' || (trim($key) == '' && $key != '')) {
                                    if (!empty($value)) {
                                        if (!array_key_exists('', $values)) {
                                            $values[''] = 0;
                                        }
                                        $values[''] += $value;
                                    }
                                    unset($values[$key]);
                                } elseif ($propertyName == 'facet_dates' && !strtotime($key)) {
                                    unset($values[$key]);
                                }
                            }
                            $property[$name] = $values;
                        }
                    }
                    $facets = array_merge($facets, $property);
                }
            }
        }

        return $facets;
    }

    public function getFacet($name)
    {
        $facets = $this->getFacets();
        if (!empty($facets) && array_key_exists($name, $facets)) {
            return $facets[$name];
        }

        return null;
    }

    public function getFields() {
        return $this->response->fields;
    }

    public function getFieldNames() {
        return array_keys($this->response->fields);
    }
}