<?php

namespace Core\SearchBundle\Component\Search\Solr;

use Monolog\Logger;

class SolrSearch
{

    const DEFAULT_SEPARATOR = '#';

    /**
     * @var array
     */
    protected $cores = array();

    /**
     * Logger object
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * SolrSearch constructor.
     * @param Logger $logger
     * @param        $hostname
     * @param        $port
     * @param array  $cores
     */
    public function __construct(Logger $logger, $hostname, $port, array $cores) {
        $this->logger = $logger;
        foreach ($cores as $name) {
            $this->cores[$name] = array(
                'hostname' => $hostname,
                'port' => $port,
                'path' => "solr/{$name}",
            );
        }
    }

    /**
     * search
     * Search from criteria into SolR
     * @param   string $core SolR core
     * @param   mixed  $criteria Criteria list in array or string
     * @param   int    $start For pagination, start row number return
     * @param   int    $numRows For pagination, number of rows return
     * @param   array  $facets Facets list
     * @return  object  boolean -> success  Success or not
     *                           int     -> numFound Results found number
     *                           array   -> results  Results list
     *                           array   -> facets   Facets list
     * @throws  SolrSearchException
     */
    public function search($core, $criteria, $start = null, $numRows = null, array $facets = null) {
        if (!array_key_exists($core, $this->cores)) {
            throw new \InvalidArgumentException("Invalid core '{$core}'");
        }
        $solrClient = new SolrClient($this->cores[$core]);

        // Build query
        $query = new SolrQuery();
        $query->setStart($start);
        $query->setRows($numRows);
        $query->setCriteria($criteria);
        if (!is_null($facets)) {
            $query->setFacets($facets);
        }

        // Execute query
        try {
            $results = $solrClient->query($query);
        }
        catch (\SolrServerException $e) {
            $trace = $e->getTrace();
            $this->logger->warning("Solr Search: {$e->getMessage()}", array($trace[2]['file'].':'.$trace[2]['line']));
            throw new SolrSearchException("Search error: {$e->getMessage()}", 0, $e);
        }

        return $results;
    }

    public function setFacetOrder($order, array $values) {
        if ($order == SolrQuery::ORDER_ASC) {
            ksort($values);
        }
        elseif ($order == SolrQuery::ORDER_DESC) {
            krsort($values);
        }

        return $values;
    }

}