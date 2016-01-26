<?php

namespace Core\CoreBundle\Component\Indexer;

use Core\CoreBundle\Component\Indexer\SolrQuery;

class SolrIndexer
{

    const DEFAULT_SEPARATOR = '#';

    /**
     * @var array
     */
    protected $cores = array();

    /**
     * SolRIndexer constructor.
     * @param $hostname
     * @param $port
     * @param array $cores
     */
    public function __construct($hostname, $port, array $cores) {
        var_dump($hostname, $port, $cores);
        foreach ($cores as $name => $fields) {
            $this->cores[$name] = array(
                'hostname' => $hostname,
                'port'     => $port,
                'path'     => "solr/{$name}",
                'fields'   => $fields,
            );
        }
        var_dump($this->cores);
    }

    /**
     * search
     * Search from criteria into SolR
     * @param   string $core SolR core
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
        $query->setFacets($facets);

        // Execute query
        $results = $solrClient->query($query);

        return $results;
    }

    public function importData($core) {
        $this->request($core, '<dataimport command="full-import" rows="10" clean="true" />');
    }

    private function request($core, $content) {
        if (!array_key_exists($core, $this->cores)) {
            throw new \InvalidArgumentException("Invalid core '{$core}'");
        }
        $solrClient = new SolrClient($this->cores[$core]);
        $solrClient->request($content);
    }

    private function commit($core) {
        $this->request($core, "<commit/>");
    }

    private function optimize($core) {
        $this->request($core, "<optimize/>");
    }

    private function rollback($core) {
        if (!array_key_exists($core, $this->cores)) {
            throw new \InvalidArgumentException("Invalid core '{$core}'");
        }
        $solrClient = new SolrClient($this->cores[$core]);
        $solrClient->rollback();
    }

    public function add($core, $object) {
        if (!array_key_exists($core, $this->cores)) {
            throw new \InvalidArgumentException("Invalid core '{$core}'");
        }
        $solrClient = new SolrClient($this->cores[$core]);

        // Build document
        $doc = new SolrInputDocument();
        foreach ($object as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $val) {
                    $doc->addField($key, $val);
                }
            }
            else {
                $doc->addField($key, $value);
            }
        }

        // Add document
        $solrClient->addDocument($doc, false);

        // Commit transaction
        $this->commit($core);
    }

    public function update($core, $object, $id, $id_field = null) {
        if (!array_key_exists($core, $this->cores)) {
            throw new \InvalidArgumentException("Invalid core '{$core}'");
        }
        $solrClient = new SolrClient($this->cores[$core]);

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
                    }
                    else {
                        $field->addAttribute('update', 'add');
                    }
                }
            }
            else {
                $field = $doc->addChild('field', $value);
                $field->addAttribute('name', $key);
                //$field->addAttribute('update', 'set');
                if (is_null($value)) {
                    $field->addAttribute('update', 'set');
                    $field->addAttribute('null', 'true');
                }
                else {
                    $field->addAttribute('update', 'add');
                }
            }

        }
        // Request solr with update message
        $solrClient->request($xml->asXML());

        // Commit transaction
        $this->commit($core);

        // optimize transaction
        //$this->optimize();

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