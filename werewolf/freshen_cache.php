#!/usr/bin/php
<?php

include_once("php/common.php");

if(count($argv) <= 2)
{
	echo "\nUsage: $argv[0] [start|end|player] (game_id)\n\n";
	exit;
}

$cache = init_cache();

$status = $argv[1];
$game_id = $argv[2];

switch($status){
	case "start":
		$cache->remove('game-counts', 'front');
		$cache->remove('games-in-progress-list', 'front');
		$cache->remove('games-signup-list', 'front');
		$cache->remove('games-signup-fast-list', 'front');
		$cache->remove('games-signup-swf-list', 'front');
		$cache->clean('front-signup-' . quote_smart($game_id));
		$cache->clean('front-signup-swf-' . quote_smart($game_id));
		$cache->clean('front-signup-fast-' . quote_smart($game_id));
		break;
	case "end":
		$cache->remove('game-counts', 'front');
		$cache->remove('games-in-progress-list', 'front');
		$cache->remove('games-ended-list', 'front');
		break;
    case "player":
        $cache->clean('front-signup-'.quote_smart($game_id));
        $cache->clean('front-signup-swf-'.quote_smart($game_id));
        $cache->clean('front-signup-fast-'.quote_smart($game_id));
        break;
	default:
		echo "\n$status must be one of: start, end\n\n";
}
?>
