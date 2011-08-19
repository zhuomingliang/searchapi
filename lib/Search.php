<?php
class Search {
	private $client;
	function __construct($options = null) {
		$this->client = new SolrClent($options);


	}

	function update() {

		$options = array (
						 'hostname' => SOLR_SERVER_HOSTNAME,
						 'login'    => SOLR_SERVER_USERNAME,
						 'password' => SOLR_SERVER_PASSWORD,
						 'port'     => SOLR_SERVER_PORT,
						 'path'     => SOLR_PATH_TO_SOLR,
						 'wt'       => SOLR_PHP_NATIVE_RESPONSE_WRITER,
						);

		$client = new SolrClient($options);

		$doc = new SolrInputDocument();

		$doc->addField('id', 334455);
		$doc->addField('cat', 'Software');
		$doc->addField('cat', 'Lucene');

		$updateResponse = $client->addDocument($doc);
	}

	function delete() {

	}


	function commit() {


	}


	function search() {

		include "bootstrap.php";

$options = array
(
    'hostname' => SOLR_SERVER_HOSTNAME,
    'login'    => SOLR_SERVER_USERNAME,
    'password' => SOLR_SERVER_PASSWORD,
    'port'     => SOLR_SERVER_PORT,
);

$client = new SolrClient($options);

$query = new SolrQuery();

$query->setQuery('lucene');

$query->setStart(0);

$query->setRows(50);

$query->addField('cat')->addField('features')->addField('id')->addField('timestamp');

$query_response = $client->query($query);

$response = $query_response->getResponse();

print_r($response);

	}
}
?>
