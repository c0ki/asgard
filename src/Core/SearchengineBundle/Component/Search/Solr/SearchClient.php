<?php

namespace Core\SearchengineBundle\Component\Search\Solr;

use Core\SearchengineBundle\Component\Search\SearchClient as CoreSearchClient;
use Core\SearchengineBundle\Component\Search\SearchQuery as CoreSearchQuery;

class SearchClient extends \SolrClient implements CoreSearchClient
{
    protected $logger;

    public function __construct($options)
    {
        $options['path'] .= "/{$options['core']}";
        parent::__construct($options);
    }

    /**
     * @param CoreSearchQuery $query
     * @return SearchResponse
     */
    public function query(CoreSearchQuery $query)
    {
        // Execute query
        try {
            $response = parent::query($query);
            $response->response_rows = $query->getRows();
        }
        catch (\SolrClientException $e) {
            $message = $e->getMessage();
            $content = null;
            $error = null;
            if (preg_match("#^(.*)(<html>.*)$#", preg_replace('/\s+/', ' ', $message), $matches)) {
                $message = strip_tags($matches[1]);
                $content = $matches[2];
                if (preg_match("#<p>(.*)</p>#", preg_replace('/\s+/', ' ', $e->getMessage()), $matches)) {
                    $error = strip_tags($matches[1]);
                }
            }
            elseif (preg_match("#^(.*)(<\?xml.*)$#", preg_replace('/\s+/', ' ', $message), $matches)) {
                $message = strip_tags($matches[1]);
                $content = simplexml_load_string($matches[2]);
                $errorCode = (string)array_shift($content->xpath('//lst[@name="error"]/*[@name="code"]'));
                $errorMessage = (string)array_shift($content->xpath('//lst[@name="error"]/*[@name="msg"]'));
                $error = sprintf("%s: %s", $errorCode, $errorMessage);
            }

            throw new SearchException(sprintf("%s [error:%s]", $message, $error));
        }
        return new SearchResponse($response);
    }

    /**
     * @param $criteria
     * @param int $start
     * @param int $numRows
     * @param array $facets
     * @return SearchResponse
     */
    public function search($criteria, $start = null, $numRows = null, array $facets = null) {
        $query = new SearchQuery();
        $query->setQuery($criteria);
        $query->setStart($start);
        $query->setRows($numRows);
        $query->setFacets($facets);

        return $this->query($query);
    }

    static $query = SearchQuery::class;
}