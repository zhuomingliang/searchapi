<?php
class SearchEngine {
    private $client;

    function __construct($options = null) { 
        if($options === null) {
            $options = array(
                'hostname' => SOLR_SERVER_HOSTNAME,
                'login'    => SOLR_SERVER_USERNAME,
                'password' => SOLR_SERVER_PASSWORD,
                'port'     => SOLR_SERVER_PORT,
                'path'     => SOLR_PATH_TO_SOLR,
                'wt'       => SOLR_WRITER_TYPE,
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


    function search() {
        $query = new SolrQuery();
        $query->setQuery('lucene');
        $query->setStart(0);
        $query->setRows(50);
        $query->addField('cat')->addField('features')->addField('id')->addField('timestamp');
        $query_response = $this->client->query($query);
        $response = $query_response->getResponse();
        print_r($response);
    }
}
?>
