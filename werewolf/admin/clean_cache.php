<?php

include_once "../php/accesscontrol.php";
checkLevel($level,1);

include_once "../php/common.php";
include_once "../menu.php";
$cache = init_cache();

?>
<html>
<head>
<title>Cache cleaning</title>
<link rel='stylesheet' type='text/css' href='../bgg.css'>
</head>
<body>
<?php display_menu();?>
<h1>Cache cleaning</h1>
<?php
if($cache->clean())
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
