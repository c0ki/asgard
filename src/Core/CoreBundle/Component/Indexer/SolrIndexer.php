<?php

namespace Core\CoreBundle\Component\Indexer;

class SolrIndexer
{

    const DEFAULT_SEPARATOR = '#';

    /**
     * @var array
     */
    protected $cores = array();

    /**
     * SolRIndexer constructor.
     * @param       $hostname
     * @param       $port
     * @param array $cores
     */
    public function __construct($hostname, $port, array $cores) {
        foreach ($cores as $name) {
            $this->cores[$name] = array(
                'hostname' => $hostname,
                'port' => $port,
                'path' => "solr/{$name}",
            );
        }
    }

    /**
     * search
     * Search from criteria into SolR
     * @param   string $core SolR core
     * @param   mixed  $criteria Criteria list in array or string
     * @param   int    $start For pagination, start row number return
     * @param   int    $numRows For pagination, number of rows return
     * @param   array  $facets Facets list
     * @return  object  boolean -> success  Success or not
     *                           int     -> numFound Results found number
     *                           array   -> results  Results list
     *                           array   -> facets   Facets list
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
        if (!is_null($facets)) {
            $query->setFacets($facets);
        }

        // Execute query
        $results = $solrClient->query($query);

        return $results;
    }

    public function importData($core, $clean = false) {
        $solrClient = new SolrClient($this->cores[$core]);
        $solrInfo = $solrClient->getOptions();

        $url = "http://{$solrInfo['hostname']}:{$solrInfo['port']}/{$solrInfo['path']}/dataimport";
        $query_data = array(
            'command' => 'full-import',
            'clean' => $clean ? 'true' : 'false',
            'commit' => 'true',
            'wt' => 'json',
            'indent' => 'true',
            'verbose' => 'false',
            'optimize' => 'false',
            'debug' => 'false',
        );
        $url .= "?" . http_build_query($query_data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PROXY, null);
        curl_setopt($ch,
                    CURLOPT_HTTPHEADER,
                    array('Content-type: application/json')); // Assuming you're requesting JSON
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        $data = json_decode($response);

        $data->statusMessages = (array)$data->statusMessages;
        $result = array(
            'fetched' => array_key_exists("Total Rows Fetched",
                                          $data->statusMessages) ? $data->statusMessages["Total Rows Fetched"] : 0,
            'added' => array_key_exists("Total Documents Processed",
                                        $data->statusMessages) ? $data->statusMessages["Total Documents Processed"] : 0,
            'skipped' => array_key_exists("Total Documents Skipped",
                                          $data->statusMessages) ? $data->statusMessages["Total Documents Skipped"] : 0,
            'dateStarted' => array_key_exists("Full Dump Started",
                                              $data->statusMessages) ? $data->statusMessages["Full Dump Started"] : 0,
            'dateCommitted' => array_key_exists("Committed",
                                                $data->statusMessages) ? $data->statusMessages["Committed"] : 0,
            'duration' => array_key_exists("Time taken",
                                           $data->statusMessages) ? $data->statusMessages["Time taken"] : 0,
        );

        return $result;
    }

    public function clean($core) {
        $solrClient = new SolrClient($this->cores[$core]);
        $solrInfo = $solrClient->getOptions();

        $url = "http://{$solrInfo['hostname']}:{$solrInfo['port']}/{$solrInfo['path']}/update";
        $query_data = array(
            'stream.body' => '<delete><query>*:*</query></delete>',
            'commit' => 'true',
            'wt' => 'json',
        );
        $url .= "?" . http_build_query($query_data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PROXY, null);
        curl_setopt($ch,
            CURLOPT_HTTPHEADER,
            array('Content-type: application/json')); // Assuming you're requesting JSON
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        $data = json_decode($response);
        $result = (array)$data->responseHeader;

        return $result;
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