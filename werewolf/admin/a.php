<?php

include_once "../php/accesscontrol.php";
include_once "../php/db.php";
include_once "../php/google_calendar.php";
#nclude_once "../menu.php";

#dbConnect();

#checkLevel($level,1);


?>

<html>
<head>
<title>Reset the Calendar with Database data</title>
<link rel='stylesheet' type='text/css' href='/bgg.css'>
</head>
<body>
<h1>Reseting the Calendar</h1>
<ul>
<li>Deleting all entries to Update_calendar table</li>
<li>Adding in Entries from Games Table in the Database</li>
</ul>
</body>
</html>
