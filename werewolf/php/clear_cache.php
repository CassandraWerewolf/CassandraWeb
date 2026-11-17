#!/usr/local/bin/php

<?php
// Command-line script only; refuse web requests.
if (PHP_SAPI !== 'cli') {
  http_response_code(404);
  exit;
}

require_once("Cache/Lite.php");

if($argc != 2) {
	print "\nUsage: $argv[0] group\n\n";
	exit;
}

$group = $argv[1];
$dir = '/dev/shm/cache_lite';

$options = array('cacheDir' => $dir);
$cache = new Cache_Lite($options);

$cache->clean($group);

?>
