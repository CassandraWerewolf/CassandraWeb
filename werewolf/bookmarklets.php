<?php
include_once "php/accesscontrol.php";
include_once "php/common.php";
include_once "menu.php";

?>
<html>
<head>
<title>Bookmarklets</title>
<link rel='stylesheet' type='text/css' href='/bgg.css'>
</head>
<body>
<?php print display_menu(); ?>
<div style='padding-left:10px;'>
<h1>Bookmarklets</h1>
Drag these links to your bookmark bar or add them to your favorites.

<ul>
<li><a href="javascript:(function() {var s = document.createElement('script'); s.setAttribute('src', '/vote.js');s.setAttribute('type', 'text/javascript'); document.getElementsByTagName('head')[0].appendChild(s);})();">Place a vote</a>: place a correctly formatted vote into a BGG text box</li>
</ul>
</div>
</body>
</html>

