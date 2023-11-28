<?php

	include_once "../setup.php";

	include      ROOT_PATH . "/php/accesscontrol.php";
	include_once ROOT_PATH . "/php/db.php";
	include_once ROOT_PATH . "/php/common.php";
	include_once ROOT_PATH . "/menu.php";
	require_once 'HTML/Table.php';

	$here = "/";
	$pagename = "show_player_stats.php";
	$game = "/game/";

	$player = $_REQUEST['player']; 
	if ( $player == "" ) {
?>
<html>
	<head>
		<script language='javascript'>
		<!--
		window.history.back();
		//-->
		</script>
	</head>
<body>
	Please hit your browsers back button.
</body>
</html>
<?php
	}

	$player_link = "/player/$player/games_played";
	$mod_link = "/player/$player/games_modded";
	$with_link = "/player/$player/with";
	$sql = sprintf("select id from Users where name=%s",quote_smart($player));
	$result = mysqli_query($mysql, $sql);
	if ( mysqli_num_rows($result) != 0 ) {
		$user_id = mysqli_result($result,0,0);
	} else {
		$user_id = 0;
	}
?>
<html>
<head>
<title>Player Stats for <?=$player;?></title>
<link rel='stylesheet' type='text/css' href='/assets/css/application.css'>
</head>
<body>
<?php display_menu(); ?>
<h1>Player Stats for <?=$player;?></h1>
<?php
	$mysql = dbConnect();

	$sql_games_played_stats = "SELECT games_played, rank FROM Users_game_ranks WHERE name = '$player';";

	$sql_games_modded_stats = "SELECT games_moderated, rank FROM Users_modded_ranks WHERE name = '$player';";
	
	$sql_played_with = sprintf("select u.id, u.name, count(*) as num from Players_all p, Users u where p.user_id=u.id and p.game_id in (select g.id from Players p, Games g where p.game_id=g.id and g.status='Finished' and p.user_id=%s union select g.id from Replacements r, Games g where r.game_id=g.id and g.status='Finished' and r.replace_id=%s) group by u.id order by num desc",quote_smart($user_id),quote_smart($user_id));

	$sql_current_games_signup = "SELECT Games.id, if(swf='Yes','When Full',DATE_FORMAT(start_date, '%b-%d-%y')) as start, if((datediff(now(), start_date) <=3 or automod_id is not null),0,1) as old_games  FROM Games, Players_all WHERE Players_all.user_id = $user_id AND Games.id = Players_all.game_id AND Games.status = 'Sign-up' ORDER BY old_games, swf, start_date;";

	#$sql_current_games_played = "SELECT Games.id, Games.thread_id FROM Games, Players_r WHERE Players_r.user_id = $user_id AND Games.id = Players_r.game_id AND Games.status = 'In Progress' and (Players_r.death_day ='' or Players_r.death_day is null) and (Players_r.death_phase ='' or Players_r.death_phase is null) ORDER BY number;";
	$sql_current_games_played = "SELECT Games.id, Games.thread_id FROM Games, Players_r WHERE Players_r.user_id = $user_id AND Games.id = Players_r.game_id AND Games.status = 'In Progress' ORDER BY number;";



	$sql_last_games_played = "SELECT g.id, g.thread_id FROM Games g, Players_r p WHERE p.user_id = $user_id AND p.game_id = g.id AND ( (g.status = 'Finished') or (((p.death_day != '') and (p.death_day is not null)) or ((p.death_phase != '') and (p.death_phase is not null))))   and number is not null ORDER BY end_date desc, number DESC LIMIT 0, 5;";


	$sql_current_games_modded = "SELECT Games.id, Games.thread_id FROM Games, Moderators WHERE Moderators.user_id = $user_id AND Moderators.game_id = Games.id AND Games.status = 'In Progress' ORDER BY number;";

	$sql_future_games_modded = "SELECT Games.id, if(swf='Yes','When Full',DATE_FORMAT(start_date, '%b-%d-%y')) as start FROM Games, Moderators WHERE Moderators.user_id = $user_id AND Moderators.game_id = Games.id AND Games.status = 'Sign-up' ORDER BY swf, start_date asc;";

	$sql_last_games_modded = "SELECT Games.id, Games.thread_id FROM Games, Moderators, Users WHERE Users.name = '$player' AND Users.id = Moderators.user_id AND Moderators.game_id = Games.id AND Games.status = 'Finished' ORDER BY number DESC LIMIT 0, 5;";
	 


	#
	# get games played stats
	#
	$res_games_played_stats = dbGetResult($sql_games_played_stats);
	$row = mysqli_fetch_row($res_games_played_stats);
	$games_played = $row[0];
	$games_played_rank = $row[1];
	mysqli_free_result($res_games_played_stats);

	#
	# get games modded stats
	#
	$res_games_modded_stats = dbGetResult($sql_games_modded_stats);
	$row = mysqli_fetch_row($res_games_modded_stats);
	$games_modded = $row[0];
	$games_modded_rank = $row[1];
	mysqli_free_result($res_games_modded_stats);

	#
	# get players played with
	#
	$res_played_with = dbGetResult($sql_played_with);
	$played_with = mysqli_num_rows($res_played_with);
	mysqli_free_result($res_played_with);

	#
	# get current games signed-up for
	#
	$res = dbGetResult($sql_current_games_signup);
	$count = dbGetResultRowCount($res);
	$current_games_signup[] = "Currently Signed Up For ($count)";
	$games_signup_date[] = "";
	while($row = mysqli_fetch_array($res)){
		#$current_games_signup[] = "<a href='$game".$row['thread_id']."'>".$row['title']."</a>";
		$current_games_signup[] = get_game($row['id'],'complex, title, mod');
		$games_signup_date[] = $row['start'];
	}
	mysqli_free_result($res);

	#
	# get current games played
	#
	$res = dbGetResult($sql_current_games_played);
	$count = dbGetResultRowCount($res);
	$current_games_played[] = "Currently Playing ($count)";
	while($row = mysqli_fetch_array($res)){
	    $sql = "select count(*) from Posts, Users where Posts.user_id=Users.id and game_id='".$row['id']."' and name='$player'";
		$posts = mysqli_query($mysql, $sql);
		$num_post = mysqli_result($posts,0,0);
		mysqli_free_result($posts);
		#$current_games_played[] = "<a href='$game".$row['thread_id']."'>".$row['number'].") ".$row['title']."</a> <a href='$game".$row['thread_id']."/$player'>($num_post posts)</a>";
		if ( $uid == $user_id ) {
		  $current_games_played[] = get_game($row['id'],'num, chat, title, mod')."<a href='$game".$row['thread_id']."/$player'>($num_post posts)</a>";
		} else {
		  $current_games_played[] = get_game($row['id'],'num, title, mod')."<a href='$game".$row['thread_id']."/$player'>($num_post posts)</a>";
		}
	}
	mysqli_free_result($res);

	#
	# get last games played
	#
	$res = dbGetResult($sql_last_games_played);
	$last_games_played[] = "Last 5 Games Played";
	while($row = mysqli_fetch_array($res)){
	    $sql = "select count(*) from Posts, Users where Posts.user_id=Users.id and game_id='".$row['id']."' and name='$player'";
		$posts = mysqli_query($mysql, $sql);
		$num_post = mysqli_result($posts,0,0);
		mysqli_free_result($posts);
		#$last_games_played[] = "<a href='$game".$row['thread_id']."'>".$row['number'].") ".$row['title']."</a> <a href='$game".$row['thread_id']."/$player'>($num_post posts)</a>";
		$last_games_played[] = get_game($row['id'],'num, title')."<a href='$game".$row['thread_id']."/$player'>($num_post posts)</a>";
	}
	mysqli_free_result($res);

	#
	# get current games modded
	#
	$res = dbGetResult($sql_current_games_modded);
	$count = dbGetResultRowCount($res);
	$current_games_modded[] = "Currently Moderating ($count)";
	while($row = mysqli_fetch_array($res)){
	    $sql = "select count(*) from Posts, Users where Posts.user_id=Users.id and game_id='".$row['id']."' and name='$player'";
		$posts = mysqli_query($mysql, $sql);
		$num_post = mysqli_result($posts,0,0);
		mysqli_free_result($posts);
		#$current_games_modded[] = "<a href='$game".$row['thread_id']."'>".$row['number'].") ".$row['title']."</a> <a href='$game".$row['thread_id']."/$player'>($num_post posts)</a>";
		if ( $uid == $user_id ) {
		  $current_games_modded[] = get_game($row['id'],'num, chat, title')."<a href='$game".$row['thread_id']."/$player'>($num_post posts)</a>";
		} else {
		  $current_games_modded[] = get_game($row['id'],'num, title')."<a href='$game".$row['thread_id']."/$player'>($num_post posts)</a>";
		}
	}
	mysqli_free_result($res);

	#
	# get future modded games
	#
	$res = dbGetResult($sql_future_games_modded);
	$count = dbGetResultRowCount($res);
	$future_modded[] = "Future Modded Games ($count)";
	$future_modded_date[] = "";
	while($row = mysqli_fetch_array($res)){
		#$future_modded[] = "<a href='$game".$row['thread_id']."'>".$row['title']."</a>";
		$future_modded[] = get_game($row['id'],'complex, title');
		$future_modded_date[] = $row['start'];
	}
	mysqli_free_result($res);

	#
	# get last games modded
	#
	$res = dbGetResult($sql_last_games_modded);
	$last_games_modded[] = "Last 5 Games Modded";
	while($row = mysqli_fetch_array($res)){
	    $sql = "select count(*) from Posts, Users where Posts.user_id=Users.id and game_id='".$row['id']."' and name='$player'";
		$posts = mysqli_query($mysql, $sql);
		$num_post = mysqli_result($posts,0,0);
		mysqli_free_result($posts);
		#$last_games_modded[] = "<a href='$game".$row['thread_id']."'>".$row['number'].") ".$row['title']."</a> <a href='$game".$row['thread_id']."/$player'>($num_post posts)</a>";
		$last_games_modded[] = get_game($row['id'],'num, title')."<a href='$game".$row['thread_id']."/$player'>($num_post posts)</a>";
	}
	mysqli_free_result($res);

	$top_attrs = array(
		'border' => '0',
		'cellpadding' => '4',
		'class' => 'forum_table',
	);
	$attrs = array(
		'border' => '0',
		'cellpadding' => '4',
		'class' => 'forum_table',
		'width' => '100%'
	);

	$table =new HTML_Table($top_attrs);
	$table->addRow(array("", "Total", "Rank")); 
	$table->addRow(array("Games Played", "<a href='" . $player_link . "'>" . $games_played . "</a>", $games_played_rank)); 
	$table->addRow(array("Games Modded", "<a href='" . $mod_link . "'>" . $games_modded . "</a>", $games_modded_rank)); 
	$table->addRow(array("Played with x other players", "<a href='".$with_link."'>".$played_with."</a>", ""));
	$table->setRowType(0,"TH");
	print "<table><tr><td valign='top'>\n";
	echo $table->toHTML();
	print "</td>\n";
    print "<td valign='top'>\n";
	# Show any Wolfy awards the game has won.
	$sql_award_p = sprintf("select * from Wolfy_players, Wolfy_awards, Users where Wolfy_players.award_id=Wolfy_awards.id and Wolfy_players.user_id=Users.id and name=%s order by Wolfy_awards.id, year",quote_smart($player));
	$result_award_p = mysqli_query($mysql, $sql_award_p);
	$sql_award_g =sprintf("select * from Wolfy_games, Wolfy_awards, Games, Moderators, Users where Wolfy_games.game_id=Games.id and Wolfy_games.award_id=Wolfy_awards.id and Games.id=Moderators.game_id and Users.id=Moderators.user_id and Users.name=%s",quote_smart($player));
	$result_award_g = mysqli_query($mysql, $sql_award_g);
	$num_awards_p = mysqli_num_rows($result_award_p) ;
	$num_awards_g = mysqli_num_rows($result_award_g) ;
	$num_awards = $num_awards_p + $num_awards_g;
	if ( $num_awards > 0 ) {
	  print "<table class='forum_table'><tr><th><a href='/wolfy_awards.php'>Wolfy Awards</a></th></tr>\n";
	  }
	  while ( $award = mysqli_fetch_array($result_award_p) ) {
	    print "<tr><td><a href='http://www.boardgamegeek.com/article/".$award['award_post']."#".$award['award_post']."'>".$award['award']." (".$award['year'].")</a></td></tr>\n";
		}
	  while ( $award = mysqli_fetch_array($result_award_g) ) {
	    print "<tr><td><a href='http://www.boardgamegeek.com/article/".$award['award_post']."#".$award['award_post']."'>".$award['award']." (".$award['year'].")</a> - For <a href='/game/".$award['thread_id']."'>".$award['title']."</a></td></tr>\n";
		}
		if ( $num_awards > 0 ) {
		  print "</table>\n";
		  }
		  print "</td></tr></table>\n";


	
	print "\n<br></br>\n";
	#if ( isset($username) ) {
	#	if ( $username == $player ) {
	#		print "<a href='${here}mystuff.php'>My Stuff</a><br />";
	#	}
	#}	
	print "<a href='${here}profile/$player'>Cassandra Profile</a><br />";
	print "<a href='http://boardgamegeek.com/user/".$player."'>BGG Profile</a><br />";
    print "<a href='${here}social/user/$player'>Social Sites</a></br />";
	$wotw_sql = sprintf("select thread_id from Wotw where user_id=%s",quote_smart($user_id));
	$mysql = dbConnect();
	$wotw_result = mysqli_query($mysql, $wotw_sql);
	$wotw_c = 0;
 	while ( $wotw_c < mysqli_num_rows($wotw_result) ) {	
	  $wotw_thread = mysqli_result($wotw_result,$wotw_c,0);
      print "<a href='http://boardgamegeek.com/thread/".$wotw_thread."'>Wolf of the Week Thread</a><br />";
	  $wotw_c++;
	}
	print "<a href='${here}send_geekmail.php?to=$player'>Send GeekMail</a>";

	$attrs_main = array(
		'border' => '0',
		'cellpadding' => '1'
	);


	$table_main =new HTML_Table($attrs_main);

	$table =new HTML_Table($attrs);
	$table->addCol($current_games_signup);
	$table->addCol($games_signup_date);
	$table->setRowType(0,"TH");
	$table->setCellAttributes(0,0,"colspan='2'");
	$table_main->setCellContents(0,0,$table->toHTML());

	$table =new HTML_Table($attrs);
	$table->addCol($future_modded);
	$table->addCol($future_modded_date);
	$table->setRowType(0,"TH");
	$table->setCellAttributes(0,0,"colspan='2'");
	$table_main->setCellContents(1,0,$table->toHTML());

	$table =new HTML_Table($attrs);
	$table->addCol($current_games_played);
	$table->setRowType(0,"TH");
	$table_main->setCellContents(0,2,$table->toHTML());

	$table =new HTML_Table($attrs);
	$table->addCol($last_games_played);
	$table->setRowType(0,"TH");
	$table_main->setCellContents(1,2,$table->toHTML());

	$table =new HTML_Table($attrs);
	$table->addCol($current_games_modded);
	$table->setRowType(0,"TH");
	$table_main->setCellContents(0,4,$table->toHTML());

	$table =new HTML_Table($attrs);
	$table->addCol($last_games_modded);
	$table->setRowType(0,"TH");
	$table_main->setCellContents(1,4,$table->toHTML());

	$table_main->setRowAttributes(0, array('valign' => 'top'));
	$table_main->setRowAttributes(1, array('valign' => 'top'));
	#$table_main->setCellAttributes(0,0,"valign='top' rowspan='2'");
	echo $table_main->toHTML();

  $sql = "select * from Misc_users where user_id=$user_id";
  $result = mysqli_query($mysql, $sql);
  $count = mysqli_num_rows($result);
  if ( $count == 1 ) {
    $misc = mysqli_fetch_array($result);
	print $misc['google_calendar'];
	if ( $user_id == $uid ) {
	print "<br /><a href='${here}gCal_guide.php'>Edit Calendar</a>";
	}
  } elseif ( $user_id == $uid ) {
    print "<p><a href='${here}gCal_guide.php' >Add My Google Werewolf Calendar</a></p>";
  }
?>
</body>
</html>
