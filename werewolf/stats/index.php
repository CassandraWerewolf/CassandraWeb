<?php
include_once "../setup.php";

include_once ROOT_PATH . "/php/accesscontrol.php";
include_once ROOT_PATH . "/php/db.php";
include_once ROOT_PATH . "/menu.php";
include_once ROOT_PATH . "/php/common.php";

$mysql = dbConnect();

$stat_id = 0;
$limit = 10;
if ( isset($_GET['stat']) ) {
  $stat_id = $_GET['stat'];
}
if ( isset($_GET['limit']) ) {
  $limit = $_GET['limit'];
}

?>
<html>
<head>
<title>Fun Statistics</title>
<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>
<script language='javascript'>
<!--

function show_stat() {
myTopRadio = new Array (
	document.getElementById('top_10'),
	document.getElementById('top_25'),
	document.getElementById('top_50'),
	document.getElementById('All') )
  stat_id = document.getElementById('stat_name').value
  for (i=0; i<myTopRadio.length; i++ ) {
    if ( myTopRadio[i].checked ) {
	  mytop = myTopRadio[i].value
	}
  }
  alert("Please be patient")
  agent.call('','get_stat','update_div',stat_id,mytop)
}

function link() {
myTopRadio = new Array (
	document.getElementById('top_10'),
	document.getElementById('top_25'),
	document.getElementById('top_50'),
	document.getElementById('All') )
  stat_id = document.getElementById('stat_name').value
  for (i=0; i<myTopRadio.length; i++ ) {
    if ( myTopRadio[i].checked ) {
	  mytop = myTopRadio[i].value
	}
  }
  location.href='index.php?stat='+stat_id+'&limit='+mytop
}

function update_div(obj) {
  my_div = document.getElementById('stat_div')
  my_div.innerHTML = obj
}
//-->
</script>
</head>
<body>
<?php print display_menu(); ?>
<div style='padding-left:10px;'>
<h1>Fun Statistics</h1>
<form name='stat_type'>
<select id='stat_name' name='stat_name' onChange='show_stat()'>
<?php
if ( $stat_id == 0 ) {
  print "<option selected value='0'>Please select a Statistic</option>";
} else {
  print "<option value='0'>Please select a Statistic</option>";
}
$sql = "select id, title from Stats order by title";
$result = mysqli_query($mysql, $sql);
while ( $stat = mysqli_fetch_array($result) ) {
  $select = "";
  if ( $stat_id == $stat['id'] ) { $select = "selected"; }
  print "<option $select value='".$stat['id']."'>".$stat['title']."</option>";
}
?>
</select>
<?php
if ( $limit == 10 ) {
   print "<input type='radio' id='top_10' name='top' checked='checked' value='10' onClick='show_stat()' />Top 10 (fast)";
   print "<input type='radio' id='top_25' name='top' value='25' onClick='show_stat()' />Top 25 ";
   print "<input type='radio' id='top_50' name='top' value='50' onClick='show_stat()' />Top 50 ";
   print "<input type='radio' id='All' name='top' value='All' onClick='show_stat()' />All (slow) ";
} elseif ( $limit == 25 ) {
   print "<input type='radio' id='top_10' name='top' value='10' onClick='show_stat()' />Top 10 (fast)";
   print "<input type='radio' id='top_25' name='top' checked='checked' value='25' onClick='show_stat()' />Top 25 ";
   print "<input type='radio' id='top_50' name='top' value='50' onClick='show_stat()' />Top 50 ";
   print "<input type='radio' id='All' name='top' value='All' onClick='show_stat()' />All (slow) ";
} elseif ( $limit == 50 ) {
   print "<input type='radio' id='top_10' name='top' value='10' onClick='show_stat()' />Top 10 (fast)";
   print "<input type='radio' id='top_25' name='top' value='25' onClick='show_stat()' />Top 25 ";
   print "<input type='radio' id='top_50' name='top' checked='checked' value='50' onClick='show_stat()' />Top 50 ";
   print "<input type='radio' id='All' name='top' value='All' onClick='show_stat()' />All (slow) ";
} elseif ( $limit == "All" ) {
   print "<input type='radio' id='top_10' name='top' value='10' onClick='show_stat()' />Top 10 (fast)";
   print "<input type='radio' id='top_25' name='top' value='25' onClick='show_stat()' />Top 25 ";
   print "<input type='radio' id='top_50' name='top' value='50' onClick='show_stat()' />Top 50 ";
   print "<input type='radio' id='All' name='top' checked='checked' value='All' onClick='show_stat()' />All (slow) ";
} else {
   print "<input type='radio' id='top_10' name='top' checked='checked' value='10' onClick='show_stat()' />Top 10 (fast)";
   print "<input type='radio' id='top_25' name='top' value='25' onClick='show_stat()' />Top 25 ";
   print "<input type='radio' id='top_50' name='top' value='50' onClick='show_stat()' />Top 50 ";
   print "<input type='radio' id='All' name='top' value='All' onClick='show_stat()' />All (slow) ";
}
?>
</form><br />
<div id='stat_div'>
<?php
print get_stat($stat_id,$limit);
?>
</div>
<p><a href='javascript:link()'>Make address bookmarkable</a></p>
<p>These stats are only as good as the data in our database.  We take no responsibility for the accuracy of the statistics.</p>
</div>
</body>
</html>
<?php
function get_stat($id,$top) {
  if ( $id == 0 ) {
   return;
  }
  $mysql = dbConnect();
  $sql = sprintf("select title, `sql` from Stats where id=%s",quote_smart($id));
  $result = mysqli_query($mysql, $sql);
  $title = mysqli_result($result,0,0);
  $sql = mysqli_result($result,0,1);
  if ( $top != "All" ) {
    $sql .= " Limit 0,".$top;
  }
  $result = mysqli_query($mysql, $sql);
  $count = 0;
  $output = '';
  while ( $row = mysqli_fetch_array($result, MYSQLI_ASSOC) ) {
      if ( $count == 0 ) {
        $output .= "<table class='forum_table'>";
        $output .= "<tr>";
      }
      $data_row = "<tr>";
    foreach ($row as $header => $value ) {
      if ( is_numeric($header) ) { continue; }
      if ( $count == 0 ) {
        $output .= "<th>$header</th>";
      }
      if ( $header == "Game" ) {
        $value = get_game($value,"num, in, title");
      } 
	  if ( $header == "User" || $header == "Player" || $header == "Moderator") {
        $value = get_player_page($value);
	  }
      $data_row .= "<td>$value</td>";
    }
    if ( $count == 0 ) {
      $output .= "</tr>";
    }
    $output .= $data_row;
    $output .= "</tr>";
    $count++;
  }
  $output .= "</table>";

  return $output;
}

?>
