<?php

include "timezone_functions.php";
include_once "php/accesscontrol.php";
include_once "php/db.php";
dbConnect();
include "menu.php";

?>
<html>
<head>
<title>BGG Werewolf Time Zones</title>
<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>
<link rel='stylesheet' type='text/css' href='/hint.css'>
<script src="/hint.js"></script>
<script language='javascript'>
<!--
game_id = ""
//-->
</script>
</head>
<body>
<?php display_menu();?>
<table>
<tr>
<td><h1>Time Zone Chart</h1></td>
<td><?php print timezone_changer("relative"); ?></td>
</tr>
</table>
<div id='divDescription' class='clDescriptionCont'>
<!--Empty Div used for hint popup-->
</div>
<div id='tz_div'>
<?php print timezone_chart(); ?>
</div>
<?php timezone_js(); ?>
<script language='javascript'>
<!--
setHint()
//-->>
</script>
</body>
</html>
