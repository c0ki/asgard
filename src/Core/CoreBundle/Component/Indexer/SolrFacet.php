<?php

namespace Core\CoreBundle\Component\Indexer;

class SolrFacet
{
    const DEFAULT_MIN_COUNT = 1;

    // SolR facet options
    protected static $SolrFacetOptions = array(
        'mincount' => array(
            'type' => 'int',
            'call' => 'SolrQuery::setFacetMinCount',
        ),
        'limit' => array(
            'type' => 'int',
            'call' => 'SolrQuery::setFacetLimit',
        ),
        'prefix' => array(
            'type' => 'string',
            'call' => 'SolrQuery::setFacetPrefix',
        ),
        'offset' => array(
            'type' => 'int',
            'call' => 'SolrQuery::setFacetOffset',
        ),
        'sort' => array(
            'type' => 'list',
            'values' => array(
                'index' => SolrQuery::FACET_SORT_INDEX,
                'counter' => SolrQuery::FACET_SORT_COUNT,
            ),
            'call' => 'SolrQuery::setFacetSort',
        ),
        'order' => array(
            'type' => 'list',
            'values' => array(
                'asc' => SolrQuery::ORDER_ASC,
                'desc' => SolrQuery::ORDER_DESC,
            ),
            'callafter' => 'SolrFacet::setFacetOrder',
        ),
    );
//setFacetDateEnd ( string $value [, string $field_override ] )
//setFacetDateGap ( string $value [, string $field_override ] )
//setFacetDateHardEnd ( bool $value [, string $field_override ] )
//setFacetDateStart ( string $value [, string $field_override ] )
//setFacetEnumCacheMinDefaultFrequency ( int $frequency [, string $field_override ] )
//setFacetMethod ( string $method [, string $field_override ] )
//setFacetMissing ( bool $flag [, string $field_override ] )

    /**
     * @var SolrFacet[]
     */
    protected $facets;

    /**
     * @var string
     */
    protected $field;

    /**
     * @var array
     */
    protected $options = array();

    public function addToQuery(SolrQuery $query) {
        if (empty($this->facets) && empty($this->field)) {
            return;
        }

        if (!empty($this->field)) {
            // Init facet
            $query->setFacet(true);
            // Init default options
            $query->setFacetMinCount(self::DEFAULT_MIN_COUNT);

            $query->addFacetField($this->field);
            if (!empty($this->options)) {
                foreach ($this->options as $option => $value) {
                    if (!array_key_exists('call', self::$SolrFacetOptions[$option])
                        || empty(self::$SolrFacetOptions[$option]['call'])
                    ) {
                        continue;
                    }
                    $func = self::$SolrFacetOptions[$option]['call'];
                    list($class, $method) = explode('::', $func);
                    if ($class == 'SolrQuery') {
                        $func = array($query, $method);
                    }
                    elseif ($class == 'SolrFacet') {
                        $func = array($this, $method);
                    }
                    call_user_func($func, $value, $this->field);
                }
            }
        }
        // Add facets
        else {
            foreach ($this->facets as $facet) {
                $facet->addToQuery($query);
            }
        }
        return $this;
    }

    /**
     * @param array $facets
     * @return $this
     */
    public function setFacets(array $facets) {
        foreach ($facets as $field => $options) {
            if (is_numeric($field)) {
                $field = $options;
                $options = null;
            }
            $this->addFacet($field, $options);
        }

        return $this;
    }

    /**
     * @param            $field
     * @param array|null $options
     * @return $this
     */
    public function addFacet($field, array $options = null) {
        $this->facets[] = new SolrFacet($field, $options);

        return $this;
    }

    /**
     * @param null       $field
     * @param array|null $options
     * @throws \SolrIllegalArgumentException
     */
    public function __construct($field = null, array $options = null) {
        if (empty($field)) {
            return;
        }
        if (is_numeric($field)) {
            throw new \SolrIllegalArgumentException("Invalid facet to field '{$field}'");
        }
        $this->field = $field;
        if (empty($options)) {
            return;
        }
        if (!is_array($options)) {
            throw new \SolrIllegalArgumentException("Invalid facet options to field '{$field}' must be an array (or empty)");
        }
        $this->options = $this->formatOtions($options);
    }

    /**
     * @param array $options
     * @return array
     * @throws \SolrIllegalArgumentException
     */
    protected function formatOtions(array $options) {
        $options = array_filter($options);
        foreach ($options as $option => &$value) {
            if (!array_key_exists($option, self::$SolrFacetOptions)) {
                throw new \SolrIllegalArgumentException("Invalid facet option '{$option}' to field '{$this->field}'");
            }
            switch (self::$SolrFacetOptions[$option]['type']) {
                case 'int':
                    if (!is_numeric($value)) {
                        throw new \SolrIllegalArgumentException("Invalid facet option value, facet option '{$option}' to field '{$this->field}' must be numeric");
                    }
                    break;
                case 'list':
                    if (!in_array($value, self::$SolrFacetOptions[$option]['values'])
                        && !in_array($value, array_keys(self::$SolrFacetOptions[$option]['values']))
                    ) {
                        $listValues = is_numeric(key(self::$SolrFacetOptions[$option]['values'])) ?
                            implode(',', self::$SolrFacetOptions[$option]['values'])
                            : implode(',', array_keys(self::$SolrFacetOptions[$option]['values']));
                        throw new \SolrIllegalArgumentException("Invalid facet option value, facet option '{$option}' to field '{$this->field}' must be into {$listValues}");
                    }
                    if (in_array($value, array_keys(self::$SolrFacetOptions[$option]['values']))) {
                        $value = self::$SolrFacetOptions[$option]['values'][$value];
                    }
                    break;
            }
        }

        return $options;
    }

    /**
     * @param array $results
     * @return array
     */
    public function formatResult(array $results) {
        if (!empty($this->field) && array_key_exists($this->field, $results)) {
            foreach ($this->options as $option => $value) {
                if (!array_key_exists('callafter', self::$SolrFacetOptions[$option])
                    || empty(self::$SolrFacetOptions[$option]['callafter'])
                ) {
                    continue;
                }
                $func = self::$SolrFacetOptions[$option]['callafter'];
                list($class, $method) = explode('::', $func);
                if ($class == 'SolrFacet') {
                    $func = array($this, $method);
                }
                call_user_func($func, $value, $results[$this->field]);
            }
        }
        // Add facets
        else {
            foreach ($this->facets as $facet) {
                $results = $facet->formatResult($results);
            }
        }

        return $results;
    }

    /**
     * @param       $order
     * @param array $values
     */
    public function setFacetOrder($order, array &$values) {
        if ($order == SolrQuery::ORDER_ASC) {
            ksort($values);
        }
        elseif ($order == SolrQuery::ORDER_DESC) {
            krsort($values);
        }
    }

}