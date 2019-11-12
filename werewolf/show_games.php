<?php
require_once 'setup.php';
include_once 'php/accesscontrol.php';
include_once 'menu.php';

// Controller
require_once('src/Games/Games.php');
$games = new Games();

$type = 'all'; // default
if ($_REQUEST['type'] == 'missing_info') {
    $type = 'missing_info';
    $games_list = $games->filter_games_by_missing_info();
}
if (in_array($_REQUEST['type'], Games::STATUSES)) {
    $type = $_REQUEST['type'];
    $games_list = $games->filter_games_by_type($_REQUEST['type']);
}

// Render View
require_once 'templates/shared/header.php'; 
require_once 'templates/games/list.php'; 
require_once 'templates/shared/footer.php'; 
?>