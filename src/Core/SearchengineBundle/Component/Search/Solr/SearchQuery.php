<?php

namespace Core\SearchengineBundle\Component\Search\Solr;

use Core\SearchengineBundle\Component\Search\SearchQuery as CoreSearchQuery;

class SearchQuery extends \SolrQuery implements CoreSearchQuery
{

    const DEFAULT_MIN_COUNT = 1;

    // Facet options
    protected static $FacetOptions = array(
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
            'callafter' => 'setFacetOrder',
        ),
        'date'     => array(
            'type' => 'array',
            'call' => 'setFacetDate',
        ),
    );

    /**
     * @see \SolrQuery->setQuery()
     */
    public function setQuery($query)
    {
        // Format query criteria
        if (empty($query)) {
            $query = '*:*';
        }
        parent::setQuery(self::formatQuery($query));

        return $this;
    }

    /**
     * Format query
     * @param mixed $query Query criteria in array or string
     * @return string
     */
    public static function formatQuery($query)
    {
        // Implode criteria
        if (is_array($query)) {
            foreach ($query as $key => &$value) {
                $key = trim($key);
                if (is_array($value)) {
                    if (array_key_exists('from', $value) && array_key_exists('to', $value)) {
                        $value = "{$key}:[" . $value['from'] . ' TO ' . $value['to'] . ']';
                    } elseif (is_numeric($key)) {
                        $value = self::formatQuery($value);
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
            $query = array_filter($query);
            $query = array_unique($query);
            $query = implode(' ', $query);
        }

        // Trim criteria
        $query = trim($query);

        // Add + on start
        if (!preg_match('/^\s*\+/', $query)) {
            $query = "+{$query}";
        }
        // Remove + if only on criterion
        if (preg_match('/^\+[^\s]*(:[^\s]*)?$/', $query)) {
            $query = substr($query, 1);
        }
        // Remove bad spaces
        $query = preg_replace('/\s+:/', ':', $query);
        $query = preg_replace('/([\+\-:])\s+/', '$1', $query);
        // Remove " on *
        $query = preg_replace('/"\s*\*\s*"/', '*', $query);

        // Remove "\w+"
        $query = preg_replace('/"(\w[^\s]*)"/', '$1', $query);

        if (preg_match_all('/:(\d{4}-\d{1,2}-)(\d{1,2})\b/', $query, $matches, PREG_SET_ORDER)
        ) {
            foreach ($matches as $match) {
                $final = ":[{$match[1]}{$match[2]}T00:00:00Z TO {$match[1]}" . ($match[2] + 1) . "T00:00:00Z] ";
                $query = str_replace($match[0], $final, $query);
            }
        }

        // Replace empty by * or null
        if (empty($query)) {
            $query = '*';
        }

        return $query;
    }

    public static function parseQuery($query) {
        preg_match_all('/(or\s+|and\s+)?([+-]?\w+\:)?("[^"]*"|\(((?>[^()]+)|(?R))*\)|\w+)/i', $query, $matches);
        if (implode(' ', $matches[0]) != $query) {
            return $query;
        }
        else {
            $query = $matches[0];
            $query = array_unique($query);
            $queryParsed = array();
            foreach ($query as $param) {
                if (preg_match('/^((or\s+)?[+-]?\w+)\:(.*)$/i', $param, $matches)) {
                    $queryParsed[$matches[1]] = $matches[3];
                }
                elseif (preg_match('/^\((.*)\)$/', $param, $matches)) {
                    $queryParsed[] = self::parseQuery($matches[1]);
                }
                else {
                    $queryParsed[] = $param;
                }
            }
        }
        return $queryParsed;
    }

    public function setFacets($facets)
    {
        if (empty($facets)) {
            return $this;
        }

        if (is_string($facets)) {
            $facets = array_filter(array_map('trim', explode(',', $facets)));
            if (empty($facets)) {
                return $this;
            }
        }

        // Init facet
        $this->setFacet(true);
        $this->setFacetMissing(true);

        foreach ($facets as $name => $options) {
            if (is_numeric($name)) {
                $name = $options;
                $options = null;
            }

            // Init default options
            $this->setFacetMinCount(self::DEFAULT_MIN_COUNT);
            $this->addFacetField($name);

            if (!empty($options)) {
                foreach ($options as $option => $value) {
                    if (!array_key_exists('call', self::$FacetOptions[$option])
                        || empty(self::$FacetOptions[$option]['call'])
                    ) {
                        continue;
                    }
                    $func = array($this, self::$FacetOptions[$option]['call']);
                    $params = array($value, $name);
                    call_user_func_array($func, $params);
                }
            }
        }
    }

    /**
     * @param       $order
     * @param array $values
     */
    public function setFacetOrder($order, array &$values) {
        if ($order == SearchQuery::ORDER_ASC) {
            ksort($values);
        }
        elseif ($order == SearchQuery::ORDER_DESC) {
            krsort($values);
        }
    }

    public function setFacetDate($value, $field) {
        $this->removeFacetField($field);
        $this->addFacetDateField($field);

        if (!array_key_exists('start', $value)) {
            $value['start'] = date("Y-m-d", strtotime("-10 YEAR")) . "T00:00:00Z";
        }
        elseif (is_numeric($value['start'])) {
            $value['start'] = date("Y-m-d\TH:i:s\Z", $value['start']);
        }
        else {
            $value['start'] = date("Y-m-d\TH:i:s\Z", strtotime($value['start']));
        }
        $this->setFacetDateStart($value['start'], $field);

        if (!array_key_exists('end', $value)) {
            $value['end'] = date("Y-m-d", strtotime("+1 day")) . "T00:00:00Z";
        }
        elseif (is_numeric($value['end'])) {
            $value['end'] = date("Y-m-d\TH:i:s\Z", $value['end']);
        }
        else {
            $value['end'] = date("Y-m-d\TH:i:s\Z", strtotime($value['end']));
        }
        $this->setFacetDateEnd($value['end'], $field);

        if (!array_key_exists('gap', $value)) {
            $value['gap'] = '+10DAY';
        }
        $this->setFacetDateGap($value['gap'], $field);
        return $this;
    }
}