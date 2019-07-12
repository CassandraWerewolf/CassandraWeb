<?php

require_once 'Zend.php';
require_once 'Zend/Gdata/Calendar.php';
require_once 'Zend/Gdata/ClientLogin.php';

$entry = 'http://www.google.com/calendar/feeds/default/private/full/9953kmnevbb0utdid8q5e0dmj4';
$entry = 'http://www.google.com/calendar/feeds/default/private/full/h324g0kgsodrapuktajmcj0068';

$email = 'cassandra.project@gmail.com';
$passwd = getenv('BGG_PASSWORD');


$client = Zend_Gdata_ClientLogin::getHttpClient($email, $passwd, 'cl');
$cal = new Zend_Gdata_Calendar($client);
$cal->delete($entry);

?>

<html>
<body>
deletion attempted
</body>
</html>


