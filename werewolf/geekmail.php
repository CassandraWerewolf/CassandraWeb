<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "php/bgg.php";
dbConnect();
?>

<html>
<head>
<title>PM Players</title>
<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>
</head>
<body>
<?php print geekmail_form($to); ?>
</body>
</html>
