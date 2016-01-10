<?php

namespace Core\CoreBundle\Component\Indexer;

use \SolrQuery as CoreSolrQuery;

class SolrQuery extends CoreSolrQuery
{
    const DEFAULT_MIN_COUNT = 1;
    const DEFAULT_ROWS      = 10;

    // SolR facet options
    protected static $facetOptions = array(
        'mincount' => array(
            'type' => 'int',
            'call' => 'setFacetMinCount',
        ),
        'limit'    => array(
            'type' => 'int',
            'call' => 'setFacetLimit',
        ),
        'prefix'   => array(
            'type' => 'string',
            'call' => 'setFacetPrefix',
        ),
        'offset'   => array(
            'type' => 'int',
            'call' => 'setFacetOffset',
        ),
        'sort'     => array(
            'type'   => 'list',
            'values' => array(
                'index'   => self::FACET_SORT_INDEX,
                'counter' => self::FACET_SORT_COUNT,
            ),
            'call'   => 'setFacetSort',
        ),
        'order'    => array(
            'type'      => 'list',
            'values'    => array(
                'asc'  => self::ORDER_ASC,
                'desc' => self::ORDER_DESC,
            ),
            'callafter' => 'this.setFacetOrder',
        ),
    );
//setFacetDateEnd ( string $value [, string $field_override ] )
//setFacetDateGap ( string $value [, string $field_override ] )
//setFacetDateHardEnd ( bool $value [, string $field_override ] )
//setFacetDateStart ( string $value [, string $field_override ] )
//setFacetEnumCacheMinDefaultFrequency ( int $frequency [, string $field_override ] )
//setFacetMethod ( string $method [, string $field_override ] )
//setFacetMissing ( bool $flag [, string $field_override ] )


    public function setStart($start) {
        if (empty($start)) {
            $start = 0;
        }

        return parent::setStart($start);
    }

    public function setRows($rows) {
        if (empty($rows)) {
            $rows = self::DEFAULT_ROWS;
        }

        return parent::setRows($rows);
    }

    /**
     * @param array $facets
     * @return $this
     * @throws \SolrIllegalArgumentException
     */
    public function setFacets(array $facets) {
        if (empty($facets)) {
            return $this;
        }

        // Format facets
        $this->formatFacets($facets);

        // Init facet
        $this->setFacet(true);

        // Init default options
        $this->setFacetMinCount(self::DEFAULT_MIN_COUNT);

        // Add fields and their options
        foreach ($facets as $field => $options) {
            $this->addFacetField($field);

            if (!empty($options)) {
                foreach ($options as $option => $value) {
                    if (empty(self::$facetOptions[$option]['call'])) {
                        continue;
                    }
                    $method = self::$facetOptions[$option]['call'];
                    call_user_func(array($this, $method), $value, $field);
                }
            }
        }

        return $this;
    }

    /**
     * Check the validity of facets
     * @param array $facets
     * @throws \SolrIllegalArgumentException
     */
    protected function formatFacets(array &$facets) {
        foreach ($facets as $field => $options) {
            if (is_numeric($field) && is_string($options)) {
                $facets[$options] = array();
                unset($facets[$field]);
                continue;
            }
            if (is_numeric($field)) {
                throw new \SolrIllegalArgumentException("Invalid facet to field '{$field}'");
            }
            elseif (!empty($options) && !is_array($options)) {
                throw new \SolrIllegalArgumentException("Invalid facet options to field '{$field}' must be an array (or empty)");
            }
        }
        foreach ($facets as $field => $options) {
            if (empty($options)) {
                continue;
            }
            $options = array_filter($options);
            foreach ($options as $option => $value) {
                if (!array_key_exists($option, self::$facetOptions)) {
                    throw new \SolrIllegalArgumentException("Invalid facet option '{$option}' to field '{$field}'");
                }
                switch (self::$facetOptions[$option]['type']) {
                    case 'int':
                        if (!is_numeric($value)) {
                            throw new \SolrIllegalArgumentException("Invalid facet option value, facet option '{$option}' to field '{$field}' must be numeric");
                        }
                        break;
                    case 'list':
                        if (!in_array($value, self::$facetOptions[$option]['values'])
                            && !in_array($value, array_keys(self::$facetOptions[$option]['values']))
                        ) {
                            $listValues = is_numeric(key(self::$facetOptions[$option]['values'])) ?
                                implode(',', self::$facetOptions[$option]['values'])
                                : implode(',', array_keys(self::$facetOptions[$option]['values']));
                            throw new \SolrIllegalArgumentException("Invalid facet option value, facet option '{$option}' to field '{$field}' must be into {$listValues}");
                        }
                        if (in_array($value, array_keys(self::$facetOptions[$option]['values']))) {
                            $facets[$field][$option] = self::$facetOptions[$option]['values'][$value];
                        }
                        break;
                }
            }
        }
    }

    public function setFacetOrder($order, array $values) {
        if ($order == self::ORDER_ASC) {
            ksort($values);
        }
        elseif ($order == self::ORDER_DESC) {
            krsort($values);
        }

        return $values;
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
                        $value = "({$key}:" . implode(" or {$key}:", $value) . ')';
                    }
                }
                elseif (!is_numeric($key)) {
                    $key = trim($key);
                    $value = trim($value);
                    $value = "{$key}:{$value}";
                }
            }
            $criteria = array_filter($criteria);
            $criteria = array_unique($criteria);
            $criteria = implode($operator, $criteria);
        }

        // Remove bad spaces
        $criteria = preg_replace('/\s+:/', ':', $criteria);
        $criteria = preg_replace('/([\+\-:])\s+/', '$1', $criteria);
    }

}