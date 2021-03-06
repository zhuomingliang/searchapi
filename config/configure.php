<?php

/* Whether or not to run in secure mode */
define('SOLR_SECURE', false);

/* Domain name of the Solr server */
define('SOLR_SERVER_HOSTNAME', 'localhost');

/* HTTP Port to connection */
define('SOLR_SERVER_PORT', ((SOLR_SECURE) ? 8443 : 8080));

/* Path */
define('SOLR_PATH_TO_SOLR', 'solr/core0/');

/* Write Type */
define('SOLR_WRITER_TYPE', 'json');

/* HTTP Basic Authentication Username */
define('SOLR_SERVER_USERNAME', 'admin');

/* HTTP Basic Authentication password */
define('SOLR_SERVER_PASSWORD', 'changeit');

/* HTTP connection timeout */
/* This is maximum time in seconds allowed for the http data transfer operation. Default value is 30 seconds */
define('SOLR_SERVER_TIMEOUT', 10);

/* whether auto commit or not */
/* please set true if you are in product environment for performance */
define('SOLR_AUTO_COMMIT', false );

/* set result return rows */
define('SOLR_RESULT_ROWS', 30 );

/* File name to a PEM-formatted private key + private certificate (concatenated in that order) */
define('SOLR_SSL_CERT', 'certs/combo.pem');

/* File name to a PEM-formatted private certificate only */
define('SOLR_SSL_CERT_ONLY', 'certs/solr.crt');

/* File name to a PEM-formatted private key */
define('SOLR_SSL_KEY', 'certs/solr.key');

/* Password for PEM-formatted private key file */
define('SOLR_SSL_KEYPASSWORD', 'StrongAndSecurePassword');

/* Name of file holding one or more CA certificates to verify peer with*/
define('SOLR_SSL_CAINFO', 'certs/cacert.crt');

/* Name of directory holding multiple CA certificates to verify peer with */
define('SOLR_SSL_CAPATH', 'certs/');
?>
