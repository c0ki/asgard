<?php
/**
 * Solr engine
 * Use SolR to search execute
 *
 * Criteria format:
 *      - default operator: OR
 *      - string format:
 *              - value1 value2 value3 ...
 *              - search on default field (on field 'text' that is the concatenation of all other fields)
 *              - if prefix by field name and ':' => search only on this field (ex. field1:value1)
 *              - if prefix by + => operator is AND (ex. +value1, +field1:value1)
 *              - if prefix by - => operator is AND NOT (ex. -value1, -field1:value1)
 *              - examples:
 *                      value1 value2 => value1 OR value2
 *                      value1 +value2 => value1 AND value2
 *                      value1 -value2 => value1 AND NOT value2
 *                      value1 field1:value2 => value1 OR field1:value2
 *                      value1 +field1:value2 => value1 AND field1:value2
 *                      value1 -field1:value2 => value1 AND NOT field1:value2
 *                      field1:value1 field1:value2 => field1:(value1 OR value2)
 *                      field1:value1 +field1:value2 => field1:(value1 AND value2)
 *                      field1:value1 -field1:value2 => field1:(value1 AND NOT value2)
 *                      field1:value1 field2:value2 => field1:value1 OR field2:value2
 *                      field1:value1 +field2:value2 => field1:value1 AND field2:value2
 *                      field1:value1 -field2:value2 => field1:value1 AND NOT field2:value2
 *                      field1:value1 field1:value2 field2:value3 => field1:(value1 OR value2) OR field2:value3
 *                      field1:value1 +field1:value2 field2:value3 => field1:(value1 AND value2) OR field2:value3
 *                      field1:value1 -field1:value2 field2:value3 => field1:(value1 AND NOT value2) OR field2:value3
 *                      field1:value1 field1:value2 +field2:value3 => field1:(value1 OR value2) AND field2:value3
 *                      field1:value1 field1:value2 -field2:value3 => field1:(value1 OR value2) AND NOT field2:value3
 *                      field1:value1 +field1:value2 +field2:value3 => field1:(value1 AND value2) AND field2:value3
 *                      field1:value1 +field1:value2 -field2:value3 => field1:(value1 AND value2) AND NOT field2:value3
 *                      ...
 */

define('SOLRENGINE_CONFIGURATIONFILE', 'solr.config.ini');
define('SOLRENGINE_DEFAULT_NUMROWS', 10);
define('SOLRENGINE_DEFAULT_FACETMINCOUNT', 1);
define('SOLRENGINE_DEFAULT_SEPARATOR', '#');

class SolrEngine
{

    // Save instance to have only one (singleton)
    private static $instance = null;

    // SolR clients
    protected $solrClients = array();

    /* @var Config $solrConfigurations */
    protected $solrConfigurations;

    // SolR facet options
    protected $solrOptionsFacet = array(
        'mincount' => array(
            'type' => 'int',
            'call' => 'setFacetMinCount'
        ),
        'limit' => array(
            'type' => 'int',
            'call' => 'setFacetLimit'
        ),
        'prefix' => array(
            'type' => 'string',
            'call' => 'setFacetPrefix'
        ),
        'offset' => array(
            'type' => 'int',
            'call' => 'setFacetOffset'
        ),
        'sort' => array(
            'type' => 'list',
            'values' => array(
                'index' => SolrQuery::FACET_SORT_INDEX,
                'counter' => SolrQuery::FACET_SORT_COUNT,
            ),
            'call' => 'setFacetSort'
        ),
        'order' => array(
            'type' => 'list',
            'values' => array(
                'asc' => SolrQuery::ORDER_ASC,
                'desc' => SolrQuery::ORDER_DESC,
            ),
            'callafter' => 'this.setFacetOrder'
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
     * create
     * Return instance of SolrEngine, only one
     * @return  SolrEngine
     */
    static function create()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * __construct
     * Constructor, load configuration and initialize SolR client
     */
    protected function __construct()
    {
        $this->loadConfiguration();
        foreach ($this->solrConfigurations->listProfiles() as $profile) {
            $this->solrClients[$profile] = new SolrClient($this->solrConfigurations->getConfiguration($profile));
        }

    }

    /**
     * loadConfiguration
     * Load configuration file and get fields list from SolR schema
     * @return  none
     */
    protected function loadConfiguration()
    {
        // Load config file
        $this->solrConfigurations = Config::create();
    }

    /**
     * search
     * Search from criteria into SolR
     * @param   string $profile Search engine profile
     * @param   mixed $criteria Criteria list in array or string
     * @param   int $start For pagination, start row number return
     * @param   int $numRows For pagination, number of rows return
     * @param   array $facets Facets list
     * @return  object  boolean -> success  Success or not
     *                  int     -> numFound Results found number
     *                  array   -> results  Results list
     *                  array   -> facets   Facets list
     * @throws  SolrEngineException
     */
    public function search($profile, $criteria, $start = null, $numRows = null, array $facets = null)
    {
        if (!in_array($profile, $this->solrConfigurations->listProfiles()))
            throw new SolrEngineException("Invalid profile");

        // Init
        $results = new StdClass();
        $results->success = 0;
        $results->numFound = 0;
        $results->numStart = 0;
        $results->numRows = 0;
        $results->results = array();
        $results->facets = array();

        // Format criteria
        $criteria = $this->formatCriteria($criteria);

        // Check criteria
        $pagination = $this->checkCriteria($criteria, $this->solrConfigurations->listFields($profile));
        if (!is_null($pagination)) {
            $start = (!is_null($start)) ? $start : $pagination->start;
            $numRows = (!is_null($numRows)) ? $numRows : $pagination->numRows;
        }

        // Pagination
        if (is_null($start)) {
            $start = 0;
        }
        if (is_null($numRows)) {
            $numRows = SOLRENGINE_DEFAULT_NUMROWS;
        }

        // Check and format facet
        if (is_null($facets)) {
            $facets = $this->solrConfigurations->listFacets($profile);
        }
        if (!is_null($facets)) {
            $this->checkFacets($facets);
        }

        // Build query
        $query = new SolrQuery();
        $query->setQuery(implode(' ', $criteria));
        $query->setStart($start);
        $query->setRows($numRows);

        if (!is_null($facets)) {
            $this->setFacets($facets, $query);
        }

        // Execute query
        // @var SolrQueryResponse $query_response
        $query_response = $this->solrClients[$profile]->query($query);
        $results->success = $query_response->success();
        if ($results->success) {
            $response = $query_response->getResponse();
            $results->numFound = $response->response->numFound;
            $results->numStart = $start;
            $results->numRows = $numRows;

            $results->results = $response->response->docs;

            if (isset($response->facet_counts) && !empty($response->facet_counts)) {
                $results->facets = array_merge(
                    (array)$response->facet_counts->facet_queries,
                    (array)$response->facet_counts->facet_fields,
                    (array)$response->facet_counts->facet_dates,
                    (array)$response->facet_counts->facet_ranges
                );
                foreach ($results->facets as $field => $resultFacet) {
                    $results->facets[$field] = (array)$resultFacet;
                    foreach ($results->facets[$field] as $value => $counter) {
                        if (array_key_exists($field, $criteria) && $criteria[$field] == "{$field}:\"{$value}\"") {
                            unset($results->facets[$field][$value]);
                        } elseif (trim($value) == '') {
                            unset($results->facets[$field][$value]);
                        }
                    }
                    if (array_key_exists($field, $facets) && !empty($facets[$field])) {
                        foreach ($facets[$field] as $option => $value) {
                            if (!array_key_exists('callafter', $this->solrOptionsFacet[$option])
                                || empty($this->solrOptionsFacet[$option]['callafter'])
                            ) {
                                continue;
                            }
                            $object = $query;
                            $method = $this->solrOptionsFacet[$option]['callafter'];
                            if (substr($this->solrOptionsFacet[$option]['callafter'], 0, 5) == 'this.') {
                                $object = $this;
                                $method = substr($this->solrOptionsFacet[$option]['callafter'], 5);
                            }
                            $resultCallAfter = call_user_func(array($object, $method), $value, $results->facets[$field]);
                            if ($resultCallAfter) {
                                $results->facets[$field] = $resultCallAfter;
                            }
                        }
                    }
                }
            }

        }
//var_dump($results);
        return $results;
    }

    /**
     * checkCriteria
     * Check the validity of criteria
     * @param   array $criteria Criteria list in array
     * @param   array $listFields Fields list
     * @return  object  int ->start     For pagination, start row number return
     *                  int ->numRows   For pagination, number of rows return
     */
    protected function checkCriteria(array &$criteria, array $listFields)
    {
        // Init
        $pagination = new StdClass();
        $pagination->start = 0;
        $pagination->numRows = SOLRENGINE_DEFAULT_NUMROWS;

        // Check pagination
        if (array_key_exists('numRows', $criteria)) {
            $pagination->numRows = $criteria['numRows'];
            unset($criteria['numRows']);
        }
        if (array_key_exists('start', $criteria)) {
            $pagination->start = $criteria['start'];
            unset($criteria['start']);
        } elseif (array_key_exists('page', $criteria)) {
            $pagination->start = $criteria['page'] * $pagination->numRows;
            unset($criteria['page']);
        }

        if (!empty($this->solrFields)) {
            foreach ($criteria as $field => $value) {
                if (is_numeric($field)) {
                    continue;
                } elseif (!array_key_exists($field, $listFields)) {
                    user_error(__CLASS__ . ": Invalid criterion: Field '{$field}' not exists, ignore it", E_USER_WARNING);
                    unset($criteria[$field]);
                }
            }
        }

        return $pagination;
    }

    /**
     * formatCriteria
     * Format criteria
     * @param   mixed $criteria Criteria list in array or string
     * @return  array
     */
    protected function formatCriteria($criteria)
    {
        // Explode criteria
        if (is_string($criteria)) {
            $listCriteria = array();
            $criteria = preg_replace('/\s+([:])/', '$1', $criteria);
            $criteria = preg_replace('/([\+\-:])\s+/', '$1', $criteria);
//            preg_match_all('/[^\s"\'\(]*("[^"]*")*(\'[^\']*\')*(\([^\)]*\))*/', $criteria, $matches);
//            $criteria = $matches[0];
            $criteria = array($criteria);
        }

        if (!is_array($criteria)) {
            user_error(__CLASS__ . ": Invalid criteria: Criteria list is invalid", E_USER_ERROR);
        }

        // Init
        $formattedCriteria = array();

        // Filter criteria to remove empty criterion
        $criteria = array_filter($criteria);

        // Format criteria
        foreach ($criteria as $field => $value) {
            if (is_numeric($field) || $field == '') {
                $formattedCriteria[] = $value;
                continue;
            } elseif (is_array($value) && array_key_exists('from', $value) && array_key_exists('to', $value)) {
                $value = '[' . $value['from'] . ' TO ' . $value['to'] . ']';
            } elseif (!is_string($value) && !is_numeric($value)) {
                user_error(__CLASS__ . ": Invalid criteria: Invalid format to criterion '{$field}', ignore it", E_USER_WARNING);
                continue;
            }
            $formattedCriteria[$field] = "{$field}:{$value}";
        }
        return $formattedCriteria;
    }

    /**
     * checkFacets
     * Check the validity of facets
     * @param   array $facets Facets list
     */
    protected function checkFacets(array &$facets)
    {
        foreach ($facets as $field => $options) {
            if (is_numeric($field) && is_string($options)) {
                $facets[$options] = array();
                unset($facets[$field]);
            } elseif (is_numeric($field)) {
                user_error(__CLASS__ . ": Invalid facet: Facet to field '{$field}' not exists, ignore it", E_USER_WARNING);
                unset($facets[$field]);
            } elseif (!empty($options) && !is_array($options)) {
                user_error(__CLASS__ . ": Invalid facet options: Facet options to field '{$field}' must be an array (or empty), ignore it", E_USER_WARNING);
                unset($facets[$field]);
            }
        }
        foreach ($facets as $field => $options) {
            $options = array_filter($options);
            foreach ($options as $option => $value) {
                if (!array_key_exists($option, $this->solrOptionsFacet)) {
                    user_error(__CLASS__ . ": Invalid facet option: Facet option '{$option}' to field '{$field}' not exists, ignore it", E_USER_WARNING);
                    unset($facets[$field][$option]);
                    continue;
                }
                switch ($this->solrOptionsFacet[$option]['type']) {
                    case 'int':
                        if (!is_numeric($value)) {
                            user_error(__CLASS__ . ": Invalid facet option: Facet option '{$option}' to field '{$field}' must be numeric, ignore it", E_USER_WARNING);
                            unset($facets[$field][$option]);
                            continue;
                        }
                        break;
                    case 'list':
                        if (!in_array($value, $this->solrOptionsFacet[$option]['values'])
                            && !in_array($value, array_keys($this->solrOptionsFacet[$option]['values']))
                        ) {
                            $listValues = is_numeric(key($this->solrOptionsFacet[$option]['values'])) ?
                                implode(',', $this->solrOptionsFacet[$option]['values'])
                                : implode(',', array_keys($this->solrOptionsFacet[$option]['values']));
                            user_error(__CLASS__ . ": Invalid facet option: Facet option '{$option}' to field '{$field}' must be into {$listValues}, ignore it", E_USER_WARNING);
                            unset($facets[$field][$option]);
                            continue;
                        }
                        if (in_array($value, array_keys($this->solrOptionsFacet[$option]['values']))) {
                            $facets[$field][$option] = $this->solrOptionsFacet[$option]['values'][$value];
                        }
                        break;
                }
            }
        }
    }

    /**
     * setFacets
     * Initialize query facets
     * @param array $facets
     * @param SolrQuery $query
     */
    protected function setFacets(array $facets, SolrQuery &$query)
    {
        if (empty($facets)) {
            return;
        }

        // Init facet
        $query->setFacet(true);

        // Init default options
        $query->setFacetMinCount(SOLRENGINE_DEFAULT_FACETMINCOUNT);

        // Add fields and their options
        foreach ($facets as $field => $options) {
            $query->addFacetField($field);

            if (!empty($options)) {
                foreach ($options as $option => $value) {
                    if (empty($this->solrOptionsFacet[$option]['call'])) {
                        continue;
                    }
                    $object = $query;
                    $method = $this->solrOptionsFacet[$option]['call'];
                    if (substr($this->solrOptionsFacet[$option]['call'], 0, 5) == 'this.') {
                        $object = $this;
                        $method = substr($this->solrOptionsFacet[$option]['call'], 5);
                    }
                    call_user_func(array($object, $method), $value, $field);
                }
            }
        }
    }

    private function commit($profile)
    {
        if (!in_array($profile, $this->solrConfigurations->listProfiles()))
            throw new SolrEngineException("Invalid profile");
        $this->solrClients[$profile]->request("<commit/>");
    }

    private function optimize($profile)
    {
        if (!in_array($profile, $this->solrConfigurations->listProfiles()))
            throw new SolrEngineException("Invalid profile");
        $this->solrClients[$profile]->request("<optimize/>");
    }

    private function rollback($profile)
    {
        if (!in_array($profile, $this->solrConfigurations->listProfiles()))
            throw new SolrEngineException("Invalid profile");
        $this->solrClients[$profile]->rollback();
    }

    public function add($profile, $object)
    {
        if (!in_array($profile, $this->solrConfigurations->listProfiles()))
            throw new SolrEngineException("Invalid profile");
        // Build document
        $doc = new SolrInputDocument();
        foreach ($object as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $val) {
                    $doc->addField($key, $val);
                }
            } else {
                $doc->addField($key, $value);
            }
        }

        // Add document
        $this->solrClients[$profile]->addDocument($doc, false);

        // Commit transaction
        $this->commit($profile);
    }

    public function update($profile, $object, $id, $id_field = null)
    {
        if (!in_array($profile, $this->solrConfigurations->listProfiles()))
            throw new SolrEngineException("Invalid profile");

        if (empty($id_field)) {
            $id_field = "id";
        }

        //var_dump($object);

        // Build xml message update
        $xml = new SimpleXMLElement("<add></add>");
        $doc = $xml->addChild('doc');
        $field_id = $doc->addChild('field', $id);
        $field_id->addAttribute('name', $id_field);
        foreach ($object as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $val) {
                    $field = $doc->addChild('field', $val);
                    $field->addAttribute('name', $key);
                    //$field->addAttribute('update', 'set');
                    if (is_null($val)) {
                        $field->addAttribute('update', 'set');
                        $field->addAttribute('null', 'true');
                    } else {
                        $field->addAttribute('update', 'add');
                    }
                }
            } else {
                $field = $doc->addChild('field', $value);
                $field->addAttribute('name', $key);
                //$field->addAttribute('update', 'set');
                if (is_null($value)) {
                    $field->addAttribute('update', 'set');
                    $field->addAttribute('null', 'true');
                } else {
                    $field->addAttribute('update', 'add');
                }
            }

        }
        //var_dump($xml->asXML());


        // Request solr with update message
        $this->solrClients[$profile]->request($xml->asXML());

        // Commit transaction
        $this->commit($profile);

        // optimize transaction
        //$this->optimize();

    }

    public function setFacetOrder($order, array $values)
    {
        if ($order == SolrQuery::ORDER_ASC) {
            ksort($values);
        } elseif ($order == SolrQuery::ORDER_DESC) {
            krsort($values);
        }
        return $values;
    }
}

/**
 * SolrEngineException
 */
class SolrEngineException extends Exception
{
}
