<?php
include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "php/common.php";
dbConnect();

if ( isset($_POST['submit']) ) {
  $sql = sprintf("select * from Misc_users where user_id=%s",$uid);
  $result = mysql_query($sql);
  $count = mysql_num_rows($result);
  $_POST['cal_code'] = safe_html($_POST['cal_code'],"<iframe>");
  if ( $count == 1 ) {
    $sql = sprintf("update Misc_users set google_calendar=%s where user_id=%s",quote_smart($_POST['cal_code']),$uid);
  } else {
    $sql = sprintf("insert into Misc_users (user_id, google_calendar) values(%s,%s)",$uid,quote_smart($_POST['cal_code']));
  }
  $result = mysql_query($sql);
?>
<html>
<head>
<script language='javascript'>
<!--
window.location.href='/player/<?=$username;?>'
//-->
</script>
</head>
<body>
If page does not re-direct <a href='/player/<?=$username;?>'>click here</a>.
</body>
</html>

<?php
exit;
}
?>
<html>
<head>
<title>Google Calendar Guide</title>
<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>
</head>
<body>
<h1>Adding My Google Werewolf Calendar Guide</h1>
<p>You can skip to the section that pertains to your current situation.  I've started from the basics and worked up.</p>
<h3>Creating a Google Account</h3>
<ol>
<li>Go to <a href='http://www.google.com/calendar' target='gcal'>Google Calendars</a></li>
<li>Click on "Create a new Google Account"</li>
<li>Fill in the form</li>
</ol>
<h3>Create a Werewolf Calendar</h3>
<ol>
<li>Go to <a href='http://www.google.com/calendar' target='gcal'>Google Calendars</a></li>
<li>Log in using your Google Account</li>
<li>Click on the + next to the words "My Calendars" in the left hand column.</li>
<li>Add a Calendar name (something like "My Werewolf Games").</li>
<li>Click on "Share all information on this calendar with everyone.<br /> - This is needed to have the calendar display on the Cassandra pages.</li>
<li>Click "Create Calendar"</li>
</ol>
<h3>Add Your Werewolf Games to your Werewolf Calendar</h3>
<ol>
<li>Go to your <a href='/player/<?=$username;?>' target='stats'>Stats Page</a>.</li>
<li>Click on the games you are signed up for and click on the Google Calendar Buton</li>
<li>Be sure to change the "Calendar" in the dropdown list to your werewolf calendar.  And click "Save Changes".</li>
<li>Go back to google Calendars and view you updated Werewolf Calendar.</li>
<li>Repeat this step everytime you sign up for a new game.</li>
</ol>
<h3>Add Your Werewolf Calendar to your Stats Page</h3>
<ol>
<li>Go to <a href='http://www.google.com/calendar' target='gcal'>Google Calendars</a></li>
<li>Make sure your calendar is public.  If you followed my directions above it is, if you already had a werewolf calendar you will need to double check this.</li>
<li>Click on the down arrow next to your Werwolf Calendar and then click on "Calendar Settings"</li>
<li>Click on the Blue "HTML" button next to "Calendar Address:"</li>
<li>Click on "Configuration tool"</li>
<li>Set the calendar to look how you want it to look on your Stats page.</li>
<li>Click Update URL</li>
<form method='post' action='<?=$_SERVER['PHP_SELF'];?>'>
<li>Copy the html from section 2 into the textbox below.<br />
<textarea name='cal_code' cols='50' rows='5' ></textarea></li>
<li>Click Submit.  <input type='submit' name='submit' value='submit' /></li>
<ol>
</body>
</html>
