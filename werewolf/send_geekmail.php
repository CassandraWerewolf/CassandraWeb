<?php

include_once "php/accesscontrol.php";
include_once "php/bgg.php";

// Typed password, or the remembered one if the field was left blank (POST only).
$bgg_password = bgg_password_from_post();

include_once "menu.php";

if ( isset($_POST['submit']) ) {
    $bgg_user = BGG::auth($username,$bgg_password);
    $bgg_user->send_geekmail($_POST['to'],$_POST['subject'],$_POST['message']);
?>
<html>
<head>
<script language='javascript'>
<!--
location.href=''
//-->
</script>
</head>
<body>
Message has been sent.
</body>
</html>

<?php
exit;
}

?>
<html>
<head>
<title>Compose GeekMail</title>
<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>
</head>
<body>
<?php 
print display_menu();
print geekmail_form($_REQUEST['to'],$_REQUEST['message']);
?>
</body>
</html>
