<?php

include_once "../php/accesscontrol.php";
include_once "../php/db.php";
include_once "../php/google_calendar.php";
include_once "../menu.php";

dbConnect();

checkLevel($level,1);


?>
<html>
<head>
<title>Reset the Calendar with Database data</title>
<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>
</head>
<body>
<?php display_menu();?>
<h1>Reseting the Calendar</h1>
<ul>
<li>Deleting all entries to Update_calendar table</li>
<li>Deleting all calendar entries</li>
<li>Adding in Entries from Games Table in the Database</li>
</li>Finished</li>
</ul>
</body>
</html>
