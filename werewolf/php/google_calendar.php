<?php

require_once 'Zend.php';
require_once 'Zend/Gdata/Calendar.php';
require_once 'Zend/Gdata/ClientLogin.php';

$email = 'cassandra.project@gmail.com';
$passwd = getenv('BGG_PASSWORD');

/**
 * Create an authenticated HTTP Client to talk to Google.
 */
$client = Zend_Gdata_ClientLogin::getHttpClient($email, $passwd, 'cl');

?>
