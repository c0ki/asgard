<?php

namespace Core\SearchBundle\Component\Search\Solr;

use SolrQuery as CoreSolrQuery;

class SolrQuery extends CoreSolrQuery
{
    const DEFAULT_ROWS = 10;

    /**
     * @var SolrFacet
     */
    protected $facet;

    public function &setStart($start)
    {
        if (empty($start)) {
            $start = 0;
        }

        return parent::setStart($start);
    }

    public function &setRows($rows)
    {
        if (empty($rows)) {
            $rows = self::DEFAULT_ROWS;
        }

        return parent::setRows($rows);
    }

    /**
     * @param array $facets
     * @return $this
     */
    public function setFacets(array $facets)
    {
        $this->facet = new SolrFacet;
        $this->facet->setFacets($facets);
        $this->facet->addToQuery($this);

        return $this;
    }

    /**
     * @return SolrFacet
     */
    public function getFacet()
    {
        return $this->facet;
    }

    /**
     * @param $criteria
     * @return $this
     */
    public function setCriteria($criteria)
    {
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
        $this->formatCriteria($criteria, true);

        return $this->setQuery($criteria);
    }

    /**
     * Format criteria
     * @param mixed $criteria Criteria list in array or string
     * @param bool  $formatSolr
     */
    static public function formatCriteria(&$criteria, $formatSolr = false)
    {
        // Implode criteria
        if (is_array($criteria)) {
            foreach ($criteria as $key => &$value) {
                $key = trim($key);
                if (is_array($value)) {
                    if (array_key_exists('from', $value) && array_key_exists('to', $value)) {
                        $value = "{$key}:[" . $value['from'] . ' TO ' . $value['to'] . ']';
                    } elseif (is_numeric($key)) {
                        static::formatCriteria($value, $formatSolr);
                        $value = '(' . $value . ')';
                    } else {
                        $value = "({$key}:\"" . implode("\" or {$key}:\"", $value) . "\")";
                    }
                } elseif (!is_numeric($key)) {
                    $key = trim($key);
                    $value = trim($value);
                    $value = "{$key}:\"{$value}\"";
                }
            }
            $criteria = array_filter($criteria);
            $criteria = array_unique($criteria);
            $criteria = implode(' ', $criteria);
        }

        // Trim criteria
        $criteria = trim($criteria);

        // Add + on start
        if (!preg_match('/^\s*\+/', $criteria)) {
            $criteria = "+{$criteria}";
        }
        // Remove + if only on criterion
        if (preg_match('/^\+[^\s]*(:[^\s]*)?$/', $criteria)) {
            $criteria = substr($criteria, 1);
        }
        // Remove bad spaces
        $criteria = preg_replace('/\s+:/', ':', $criteria);
        $criteria = preg_replace('/([\+\-:])\s+/', '$1', $criteria);
        // Remove " on *
        $criteria = preg_replace('/"\s*\*\s*"/', '*', $criteria);

        // Remove "\w+"
        $criteria = preg_replace('/"(\w[^\s]*)"/', '$1', $criteria);

        if (!$formatSolr
            && preg_match_all('/\[(\d{4})-(\d{2})-(\d{2})T00:00:00Z\s+TO\s+(\d{4})-(\d{2})-(\d{1,2})T00:00:00Z\]/',
                $criteria,
                $matches, PREG_SET_ORDER)
        ) {
            foreach ($matches as $match) {
                if ($match[1] == $match[4] && $match[2] == $match[5] && ($match[3] + 1) == $match[6]) {
                    $criteria = str_replace($match[0], "{$match[1]}-{$match[2]}-{$match[3]}", $criteria);
                }
            }
        } elseif ($formatSolr
            && preg_match_all('/:(\d{4}-\d{1,2}-)(\d{1,2})\b/', $criteria, $matches, PREG_SET_ORDER)
        ) {
            foreach ($matches as $match) {
                $final = ":[{$match[1]}{$match[2]}T00:00:00Z TO {$match[1]}" . ($match[2] + 1) . "T00:00:00Z] ";
                $criteria = str_replace($match[0], $final, $criteria);
            }
        }

        // Replace / and ~
        if ($formatSolr) {
            $criteria = str_replace('~', '/', $criteria);
        } else {
            $criteria = str_replace('/', '~', $criteria);
        }

        // Replace empty by * or null
        if ($formatSolr && empty($criteria)) {
            $criteria = '*';
        } elseif (!$formatSolr && empty($criteria)) {
            $criteria = null;
        }
    }

}