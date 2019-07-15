<?php

if ( $_REQUEST['remember'] == "on" ) {
  setcookie('bgg_password', $_REQUEST['bggpwd'], time()+60*60*24*365, '/', '', true, true);
}

include_once "php/accesscontrol.php";
include_once "php/bgg.php";
include_once "menu.php";

if ( isset($_POST['submit']) ) {

send_geekmail($_POST['to'],$_POST['subject'],$_POST['message'],$username,$_POST['bggpwd']);

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
