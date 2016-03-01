<?php

namespace Core\SearchBundle\Component\Search;


interface SearchQuery
{
    /**
     * @param SearchQuery $query
     * @return  object  boolean -> success  Success or not
     *                  int     -> numFound Results found number
     *                  array   -> results  Results list
     *                  array   -> facets   Facets list
     */
//    public function query(SearchQuery $query);

}