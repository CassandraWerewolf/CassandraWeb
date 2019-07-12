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
$cal = new Zend_Gdata_Calendar($client);

if(file_exists('event.xml')) {
	$xml = simplexml_load_file('event.xml');
} else {
	exit('Failed to open event.xml');
}

$xml->title='Test 1';
$xml->content='there is nothing to see here';
$xml->author->name='jeremy';
$xml->{'gd:when'}['startTime']='2007-03-10';
$xml->{'gd:when'}['endTime']='2007-03-15';

$resp = $cal->post($xml->asXML());

$resp_xml_str = $resp->getBody();
$resp_xml = new SimpleXMLElement($resp_xml_str);

echo $resp_xml->id;


?>
