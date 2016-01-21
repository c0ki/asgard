<?php

namespace Core\CoreBundle\Component\Indexer;

use \SolrQuery as CoreSolrQuery;
use Core\CoreBundle\Component\Indexer\SolrFacet;

class SolrQuery extends CoreSolrQuery
{
    const DEFAULT_ROWS      = 10;

    /**
     * @var SolrFacet
     */
    protected $facet;

    public function &setStart($start) {
        if (empty($start)) {
            $start = 0;
        }

        return parent::setStart($start);
    }

    public function &setRows($rows) {
        if (empty($rows)) {
            $rows = self::DEFAULT_ROWS;
        }

        return parent::setRows($rows);
    }

    /**
     * @param array $facets
     * @return $this
     */
    public function setFacets(array $facets) {
        $this->facet = new SolrFacet;
        $this->facet->setFacets($facets);
        $this->facet->addToQuery($this);
        return $this;
    }

    /**
     * @return \Core\CoreBundle\Component\Indexer\SolrFacet
     */
    public function getFacet() {
        return $this->facet;
    }

    /**
     * @param $criteria
     * @return $this
     */
    public function setCriteria($criteria) {
        if (is_array($criteria)) {
            if (array_key_exists('numRows', $criteria)) {
                $this->setRows($criteria['numRows']);
                unset($criteria['numRows']);
            }
            if (array_key_exists('start', $criteria)) {
                $this->setStart($criteria['start']);
                unset($criteria['start']);
            }
            if (array_key_exists('page', $criteria)) {
                $this->setStart($criteria['page'] * $this->getRows());
                unset($criteria['page']);
            }
        }

        // Format criteria
        if (empty($criteria)) {
            $criteria = '*:*';
        }
        $this->formatCriteria($criteria);

        return $this->setQuery($criteria);
    }

    /**
     * Format criteria
     * @param mixed $criteria Criteria list in array or string
     * @param string $operator
     */
    protected function formatCriteria(&$criteria, $operator = ' ') {
        // Implode criteria
        if (is_array($criteria)) {
            foreach ($criteria as $key => &$value) {
                $key = trim($key);
                if (is_array($value)) {
                    if (array_key_exists('from', $value) && array_key_exists('to', $value)) {
                        $value = "{$key}:[" . $value['from'] . ' TO ' . $value['to'] . ']';
                    }
                    elseif (is_numeric($key)) {
                        $this->formatCriteria($value, ' or ');
                        $value = '(' . $value . ')';
                    }
                    else {
                        $value = "({$key}:\"" . implode("\" or {$key}:\"", $value) . "\")";
                    }
                }
                elseif (!is_numeric($key)) {
                    $key = trim($key);
                    $value = trim($value);
                    $value = "{$key}:\"{$value}\"";
                }
            }
            $criteria = array_filter($criteria);
            $criteria = array_unique($criteria);
            $criteria = implode($operator, $criteria);
        }

        // Remove bad spaces
        $criteria = preg_replace('/\s+:/', ':', $criteria);
        $criteria = preg_replace('/([\+\-:])\s+/', '$1', $criteria);

        if (preg_match('/\d{4}-\d\d-\d\dT\d\d:\d\d:\d\dZ/', $criteria, $matches)) {
            var_dump($matches);
        }

//        2015-12-27T00:00:00Z

    }

}