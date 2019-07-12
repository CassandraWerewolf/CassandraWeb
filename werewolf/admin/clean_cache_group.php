<?php

include_once "../php/accesscontrol.php";
checkLevel($level,1);

include_once "../php/common.php";
include_once "../menu.php";
require_once 'Cache/Lite.php';

?>
<html>
<head>
<title>Cache cleaning</title>
<link rel='stylesheet' type='text/css' href='../bgg.css'>
</head>
<body>
<?php display_menu();?>
<h1>Cache group cleaning</h1>
<?php


$cache = init_cache();

if($cache->clean('front-signup-0416'))
{
	echo "cache was succesfully cleaned";
}
else
{
	echo "there was an error, cache was not cleaned";
}	
?>
</body>
</html>
