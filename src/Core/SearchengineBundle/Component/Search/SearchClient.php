<?php

namespace Core\SearchengineBundle\Component\Search;


interface SearchClient
{
    /**
     * @param SearchQuery $query
     * @return  SearchResponse
     */
    public function query(SearchQuery $query);

    /**
     * @param $criteria
     * @param int $start
     * @param int $numRows
     * @param array $facets
     * @return SearchResponse
     */
    public function search($criteria, $start = null, $numRows = null, array $facets = null);

}