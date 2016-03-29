<?php

namespace Core\SearchengineBundle\Component\Search\Solr;

use Core\SearchengineBundle\Component\Search\SearchClient as CoreSearchClient;
use Core\SearchengineBundle\Component\Search\SearchQuery as CoreSearchQuery;

class SearchClient extends \SolrClient implements CoreSearchClient
{
    protected $logger;

    static $query = SearchQuery::class;

    public function __construct($options)
    {
        $options['path'] .= "/{$options['core']}";
        parent::__construct($options);
    }

    /**
     * @param CoreSearchQuery $query
     * @return SearchResponse
     */
    public function query(CoreSearchQuery $query)
    {
        // Execute query
        try {
            $response = parent::query($query);
            $response->response_rows = $query->getRows();
            $response->fields = $this->fields();
        }
        catch (\SolrClientException $e) {
            $message = $e->getMessage();
            $content = null;
            $error = null;
            if (preg_match("#^(.*)(<html>.*)$#", preg_replace('/\s+/', ' ', $message), $matches)) {
                $message = strip_tags($matches[1]);
                $content = $matches[2];
                if (preg_match("#<p>(.*)</p>#", preg_replace('/\s+/', ' ', $e->getMessage()), $matches)) {
                    $error = strip_tags($matches[1]);
                }
            } elseif (preg_match("#^(.*)(<\?xml.*)$#", preg_replace('/\s+/', ' ', $message), $matches)) {
                $message = strip_tags($matches[1]);
                $content = simplexml_load_string($matches[2]);
                $errorCode = (string)array_shift($content->xpath('//lst[@name="error"]/*[@name="code"]'));
                $errorMessage = (string)array_shift($content->xpath('//lst[@name="error"]/*[@name="msg"]'));
                $error = sprintf("%s: %s", $errorCode, $errorMessage);
            }

            throw new SearchException(sprintf("%s [error:%s]", $message, $error));
        }

        return new SearchResponse($response);
    }

    /**
     * @param $criteria
     * @param int $start
     * @param int $numRows
     * @param array|string $facets
     * @return SearchResponse
     */
    public function search($criteria, $start = null, $numRows = null, $facets = null)
    {
        $query = new SearchQuery();
        $query->setQuery($criteria);
        $query->setStart($start);
        $query->setRows($numRows);
        if ((is_string($facets) && $facets == '*')
            || (is_array($facets) && count($facets) == 1 && $facets[0] == '*')
        ) {
            $facets = $this->fieldNames();
        }
        $query->setFacets($facets);

        return $this->query($query);
    }

    public function importData($clean = false)
    {
        $options = $this->getOptions();

        $url = "http://{$options['hostname']}:{$options['port']}/{$options['path']}/dataimport";
        $query_data = array(
            'command'  => 'full-import',
            'clean'    => $clean ? 'true' : 'false',
            'commit'   => 'true',
            'wt'       => 'json',
            'indent'   => 'true',
            'verbose'  => 'false',
            'optimize' => 'true',
            'debug'    => 'false',
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
            'fetched'       => array_key_exists("Total Rows Fetched",
                $data->statusMessages) ? $data->statusMessages["Total Rows Fetched"] : 0,
            'added'         => array_key_exists("Total Documents Processed",
                $data->statusMessages) ? $data->statusMessages["Total Documents Processed"] : 0,
            'skipped'       => array_key_exists("Total Documents Skipped",
                $data->statusMessages) ? $data->statusMessages["Total Documents Skipped"] : 0,
            'dateStarted'   => array_key_exists("Full Dump Started",
                $data->statusMessages) ? $data->statusMessages["Full Dump Started"] : 0,
            'dateCommitted' => array_key_exists("Committed",
                $data->statusMessages) ? $data->statusMessages["Committed"] : 0,
            'duration'      => array_key_exists("Time taken",
                $data->statusMessages) ? $data->statusMessages["Time taken"] : 0,
        );

        return $result;
    }

    public function clean()
    {
        $options = $this->getOptions();

        $url = "http://{$options['hostname']}:{$options['port']}/{$options['path']}/update";
        $query_data = array(
            'stream.body' => '<delete><query>*:*</query></delete>',
            'commit'      => 'true',
            'wt'          => 'json',
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

    public function add($object)
    {
        // Build document
        $doc = new \SolrInputDocument();
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
        $this->addDocument($doc, false);

        // Commit transaction
        $this->commit();
    }

    public function update($object, $id, $id_field = null)
    {
        if (empty($id_field)) {
            $id_field = "id";
        }

        // Build xml message update
        $xml = new \SimpleXMLElement("<add></add>");
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
        // Request solr with update message
        $this->request($xml->asXML());

        // Commit transaction
        $this->commit();

        // optimize transaction
        $this->optimize();
    }

    public function fields($includeInternalFields = false)
    {
        $fieldsNames = $this->api('select', array('q' => '*:*', 'wt' => 'csv', 'rows' => '0'));
        $fieldsNames = explode(',', $fieldsNames);
        sort($fieldsNames);
        $fieldsNames = implode(',', $fieldsNames);

        $response = $this->api('schema/fields');
        $fields = $response->fields;

        $response = $this->api('schema/fields', array('includeDynamic' => 'true', 'fl' => $fieldsNames));
        $fields = array_unique(array_merge($fields, $response->fields), SORT_REGULAR);

        $fieldsNames = array_map(function ($field) {
            return $field->name;
        }, $fields);
        $fields = array_combine($fieldsNames, $fields);

        if (!$includeInternalFields) {
            $fields = array_filter($fields, function ($field) {
                return !preg_match('/^_.*_$/', $field->name);
            });
        }

        return $fields;
    }

    public function fieldNames($includeInternalFields = false)
    {
        return array_keys($this->fields($includeInternalFields));
    }

    protected function api($function, $params = null)
    {
        if (!is_array($params)) {
            parse_str($params, $params);
        }
        $params = array_filter(array_map('trim', $params), function ($param) {
            return (!is_null($param) && $param !== '');
        });
        if (!array_key_exists('wt', $params)) {
            $params['wt'] = 'json';
        }

        $options = $this->getOptions();
        $url = "http://{$options['hostname']}:{$options['port']}/{$options['path']}/{$function}";
        if (!empty($params)) {
            $url .= "?" . http_build_query($params);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PROXY, null);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($info['http_code'] != 200) {
            $doc = new \DOMDocument();
            $doc->loadHTML($response);
            $message = sprintf("%s: %s", $doc->getElementsByTagName('p')->item(0)->nodeValue,
                $doc->getElementsByTagName('p')->item(1)->nodeValue);
            throw new SearchException(sprintf("%s [error:%s]", $message,
                $info['http_code']));
        }

        if ($params['wt'] == 'json') {
            $response = json_decode($response);
            if (empty($response)) {
                throw new SearchException(sprintf("%s [error:%s]", "Invalid format",
                    "unknown"));
            }
            if ($response->responseHeader->status != 0) {
                throw new SearchException(sprintf("%s [error:%s]", $response->responseHeader->error,
                    $response->responseHeader->status));
            }
        }

        return $response;
    }
}