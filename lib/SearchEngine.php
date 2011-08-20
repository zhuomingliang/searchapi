<?php
class SearchEngine {
    private $client = null;
    private static $order = array(
        'asc'  => SolrQuery::ORDER_ASC,
        'desc' => SolrQuery::ORDER_DESC
    );

    function __construct($options = null) { 
        if($options === null) {
            $options = array(
                'hostname' => SOLR_SERVER_HOSTNAME,
                'login'    => SOLR_SERVER_USERNAME,
                'password' => SOLR_SERVER_PASSWORD,
                'port'     => SOLR_SERVER_PORT,
                'path'     => SOLR_PATH_TO_SOLR,
                'wt'       => SOLR_WRITER_TYPE
            );
        }

        $this->client = new SolrClient($options);
    }

    function update($data) {
        $doc = new SolrInputDocument();

        $doc->addField('id', $data['id']);
        
        for($i = 1; $i <= 100; ++$i) {
            if(isset($data["cat_level_{$i}"])) {
                $doc->addField("cat_level_{$i}", (int) $data["cat_level_{$i}"]);
                continue;
            }

            break;
        }
                
        $doc->addField('title',          $data['title']);
        $doc->addField('manu',   (int)   $data['manu']);
        $doc->addField('price',  (float) $data['price']);
        // XXX uncomment it when it's ready
        //$doc->addField('date',   (float) $data['date']);
        $doc->addField('sales',  (int)   $data['sales']);

        try {
            $this->client->addDocument($doc);
        } catch(SolrClientException $e) {
            return false;
        }
        
        return true;
    }

    function delete($id) {
        try {
            $this->client->deleteById($id);
        } catch(SolrClientException $e) {
            return false;
        }
        
        return true;

    }

    function commit() {
        if (SOLR_AUTO_COMMIT) {
            return true;
        }

        try {
            $this->client->commit();
        } catch(SolrClientException $e) {
            return false;
        }
        
        return true;

    }


    function search($keywords = '', $filters = array(), $sorts = array(), $start = 0, $rows = SOLR_RESULT_ROWS) {
        $query    = new SolrQuery();
        $keywords = SolrUtils::escapeQueryChars($keywords);

        $query->setQuery($keywords)->setStart($start)->setRows($rows);

        foreach($filters as $key => $value) {
            $query->addFilterQuery("{$key}:{$value}");
        }

        foreach($sorts as $key => $value) {
            $query->addSortField($key, self::$order[$value]);
        }

        try {
            $response = $this->client->query($query)->getResponse();
        } catch(SolrClientException $e) {
            return array(
                'responseHeader' => array(
                    'status' => 0,
                    'QTime'  => 0,
                    'params' => array(
                        'q'     => $keywords
                    )
                ),
                'response'      => array(
                    'numFound'  => 0,
                    'start'     => 0,
                    'doc'       => array()
                )
            );
        }

        print_r($response);

        return $response;
    }


    public static function getConnection($options = null) {
        static $se_connection = null;

        if($se_connection !== null){
            return $se_connection;
        }

        return $se_connection = new SearchEngine($options);
    }
}
?>
