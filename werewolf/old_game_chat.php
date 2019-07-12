<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "menu.php";
include_once "php/common.php";
//include_once "chat_function.php";

dbConnect();

error("Due to a large number of games with a large number of rooms, and the fact that this chat page puts more strain on the server we have disabled it.  Sorry for any inconvenience.");


$thread_id = $_GET['thread_id'];
$start_room_id = "null";
$start_room_name = "Please Choose a Room";
if ( $thread_id == "" ) {
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
  exit;
} # if ( $thread_id == "" )

$sql = sprintf("select * from Games where thread_id=%s",quote_smart($thread_id));
$result = mysql_query($sql);
$game = mysql_fetch_array($result);
$game_id = $game['id'];
$title = $game['title'];
$status = $game['status'];

// Get Main Game Status if it is a Sub-Thread
$gid = $game_id;
while ( $status == "Sub-Thread" ) {
  $sql = sprintf("select `status`, parent_game_id from Games where id=%s",quote_smart($gid));
  $result = mysql_query($sql);
  $status = mysql_query($result,0,0);
  if ( $status == "Sub-Thread" ) { $gid = mysql_result($result,0,1); }
} # while ( $status == "Sub-Thread" )

//Find out if user is moderator of this game.
$mod = is_moderator($uid,$game_id);

if ( $game_id == "0622" && $mod ) { $status = "In Progress"; }

//Find out if user has Game Actions.
$sql = sprintf("select game_action, ga_desc, ga_text, ga_group, ga_lock, death_day from Players, Players_all where Players.game_id = Players_all.game_id and Players.user_id=Players_all.original_id and Players_all.user_id=%s and Players.game_id=%s ",quote_smart($uid),quote_smart($game_id));
$result = mysql_query($sql);
if ( mysql_num_rows($result) == 0 ) {
  $actions['ga_desc'] = '';
  $actions['ga_lock'] = "";
} else {
  $actions = mysql_fetch_array($result);
}

//Show chats based on Game Status
if ( $status == "Finished" ) {
  $sql = sprintf("select * from Chat_rooms where game_id=%s order by name",quote_smart($game_id));
  $result = mysql_query($sql);
  ?>
  <html>
  <head>
  <title>Communications for <?=$title;?></title>
  <link rel='stylesheet' type='text/css' href='/bgg.css'>
  <script language='javascript'>
  <!--
  function change_rooms() {
    room_id = document.getElementById('room_select').value
    if ( room_id == "all" ) {
      game_id = document.getElementById('game_id').value
      agent.call('','display_all','update_div',game_id)
    } else {
      agent.call('','display_chat','update_div',room_id)
    }
  }
  
  function update_div(obj) {
    rm_div = document.getElementById('room_div')
    rm_div.innerHTML = obj
  }

  //-->
  </script>
  </head>
  <body>
  <?php print display_menu(); ?>
  <div style='padding-left:10px;'>
  <h1>Communications for <?=$title;?></h1>
  <?php 
  $menu_output = "<form name='room_view'>\n";
  $menu_output .= "<input type='hidden' id='game_id' name='game_id' value='$game_id' />\n";
  $menu_output .= "<select id='room_select' name='room_select' onChange='change_rooms()' >\n";
  $first_room = "";
  while ( $room = mysql_fetch_array($result) ) {
    if ( $first_room == "" ) { $first_room = $room['id']; }
    $menu_output .= "<option value='".$room['id']."' />".$room['name']."\n";
  }
  $menu_output .= "<option value='all' />All post by time\n";
  $menu_output .= "</select></form><br />\n";
  print $menu_output;
  print "<div id='room_div'>\n";
  print display_chat($first_room); 
  print "</div>\n";
  ?>
  </div>
  </body>
  </html>
  <?php
} # if ( $status == "Finished" )
elseif ( $status != "In Progress")  {
  error("A game must be In Progress or Finished for viewing Game Communications");
}  # ( $status != "In Progress")
else {
  ?>
  <html>
  <head>
  <title>Communications for <?=$title;?></title>
  <link rel='stylesheet' type='text/css' href='/bgg.css'>
  <script src='/old_game_chat.js' ></script>
  <script language='javascript'>
  game_id = '<?=$game_id;?>';
  user_id = '<?=$uid;?>';

  function lock_ga() {
    sure = confirm("Are you sure you want to lock your game action?  You will not be able to change it if you do.  The Order form will be locked until the next Dawn is posted and read by cassy.")  
	if ( sure ) {
	  location.href="/game_action.php?user_id="+user_id+"&game_id="+game_id+"&action=lock"
	}
  }
  </script>
  </head>
  <?php
  if ( isset($_REQUEST['full']) ) {
    ?>
    <body>
    <?php print display_menu(); ?>
    <div style='padding-left:10px;'>
    <h1>Communications for <?=$username;?> in <?=$title;?></h1>
    <?php print display_chat($_REQUEST['room_id']); ?>
    </div>
    </body>
    </html>
    <?php
  } # ( isset($_REQUEST['full']) )
  else {
    ?>
    <body onload="javascript:startChat();"> 
    <?php print display_menu(); ?>
    <div style='padding-left:10px;'>
    <h1>Communications for <?=$username;?> in <?=$title;?></h1>
    <a href='/game/<?=$thread_id;?>'>Go to Game Page</a><br />
    <?php
    if( $mod ) {
      print "<a href='/configure_chat.php?game_id=$game_id'>Configure Game Communications System</a><br />\n";
    }
    ?>
    <table border='0' cellpadding='2' class='forum_table'>
    <tr><th>Your Chat Rooms</th><th>Current Room:</th>
    <?php
    if ( $mod ) {
      print "<th>Moderator Panel</th>";
    } else {
      print "<th>Game Action Panel</th>";
    }
    ?>
    </tr>
    <tr><td valign='top' id='rooms_td'>
    <?php
    print show_chatRooms($game_id,$uid);
    ?>
    </td>
    <td valign='top'>
    <div>
    <a name='<?=$start_room_id;?>'></a><h3 id='room_name'><?=$start_room_name;?></h3>
    <div id='room_created'></div>
    <div id='user_view'></div>
    <div id='div_chat' style='height: 300px; width: 500px; overflow: auto; background-color: #dddddd; border: 1px solid #555555;'>
    </div>
    <form id='room' name='room' onSubmit="return blockSubmit()" method='post' >
    <input type='hidden' id='room_id' name='room_id' value='<?=$start_room_id;?>' />
    <input type='hidden' id='message' name='message' value='' />
    <input type='hidden' id='to' name='to' value='' />
    <textarea id='text' name='text' style='width: 500px; height: 80px;' onKeyPress='return enter_submit(event)'></textarea>
    <br />
    <input type='submit' id='send' name='send' value='Send' />
    <input type='button' id='pm' name='pm' value='GeekMail Transcript' onClick='javascript:geekMail();' /> 
    <input type='submit' id='full' name='full' value='View Entire Chat' onClick='javascript:entireChat();' />
    </form>
    </div>
    </td>
    <?php
    if ( ! $mod ) {
      print "<td valign='top' >";
	  if ( $actions['death_day'] == "" && $actions['ga_lock'] == "" ) {
        print "<form id='game_action' method='post' action='/game_action.php'>";
        print "<input type='hidden' id='ga_user_id' name='user_id' value='$uid' />";
        print "<input type='hidden' id='ga_game_id' name='game_id' value='$game_id' />";
		if ( $actions['ga_desc'] != "" ) {
          print "<input type='hidden' name='day' value='".$game['day']."' />";
          print "Game Action for Day/Night ".$game['day'].":<br />";
          $descs = preg_split("/,/",$actions['ga_desc']);
          print "<select name='desc'>";
          foreach ( $descs as $desc ) {
            print "<option value='$desc'>$desc</option>";
          }
          print "</select><br />";
		  if ( $actions['game_action'] != "none" ) {
            print "<select name='target_id'>";
            $sql = sprintf("select Users.id, Users.name, if(death_phase='' or death_phase is null or death_phase='Alive','Living','Dead') as status from Players_r, Users where Players_r.user_id = Users.id and Players_r.game_id=%s order by name", quote_smart($game_id));
            $result = mysql_query($sql);
			$count = 0;
            while ( $user = mysql_fetch_array($result) ) {
              if ( $actions['game_action'] == "alive" && $user['status'] == "Dead" ) { continue; }
              if ( $actions['game_action'] == "dead" && $user['status'] == "Living" ) { continue; }
              print "<option value=".$user['id'].">".$user['name']." (".$user['status'].")</option>";
			  $count++;
            }
		    if ( $count == 0 ) {
              if ( $actions['game_action'] == "alive" ) {
			    print "<option value=''>No Living players</option>";
			  } else {
			    print "<option value=''>No Dead players</option>";
			  }
		    }
            print "</select><br />";
		  }
		  if ( $actions['ga_text'] == "on" ) {
            print "<input type='text' id='user_text' name='user_text' /><br />";
		  }
          print "Post to: <br />";
          $sql = sprintf("select id, name from Chat_rooms, Chat_users where Chat_rooms.id=Chat_users.room_id and Chat_rooms.`lock`='Off' and Chat_users.`lock`='Off' and user_id=%s and game_id=%s order by name",quote_smart($uid),quote_smart($game_id));
          $result = mysql_query($sql);
          while ( $room = mysql_fetch_array($result) ) {
            $special = "";
            if ( preg_match("/Mod chat -/", $room['name']) ) {
              $special = "checked='checked' readonly='readonly'";
            }
            print "<input type='checkbox' name='room_".$room['id']."' $special />".$room['name']."<br />";
          }
          print "<input type='submit' name='submit' value='Submit' />";
		  print "<input type='submit' name='cancel' value='Cancel Previous Order' />";
		} 
		print "<p>To bring dawn early, all players must 'lock game actions' even if they don't have any.</p>";
		print "<input type='button' name='lock' value='Lock Game Action' onClick='lock_ga()' />";
        print "</form>";
	  }
    } # ( ! $mod)
    if ( $mod ) {
      ?>
      <td valign='top'>
      <div>
      <span style='font-weight:bold;'>Broadcast message:</span><br />
      <form id='broadcast_form' name='brodcast' method='post' onSubmit=" return sendBroadcastText()"; >
      <input type='hidden' id='game_id' name='game_id' value='<?=$game_id;?>' />
      <textarea id='broad_text' name='broad_text' style='width:200px; height:48px;' onKeyPress='return enter_broadcast(event)'></textarea>
      <br />
      <input type='submit' id='broadcast' name='broadcast' value='Broadcast to All Rooms' />
	  <br />
	  <input type='button' id='read_all' name='read_all' value='Mark all rooms as read' onClick='mark_as_read()' />
      </form>
      </div>
      <?php
    }

    if ( $game['game_order'] == "on" ) {
      if ( $actions['ga_desc'] != "" || $mod) {
        ?>
        <div>
        <span style='font-weight:bold;'>Game Orders for Day <?=$game['day'];?>:</span> (Please refresh this page before performing the actions to make sure they are up to date.)<br />
		<table border='0'>
		<tr><th>Game Actions</th>
		<?php
		if ( $mod ) {
		  print "<th>Locked Actions</th>";
		}
		?>
		</tr>
		<tr>
        <td valign='top'><ul>
        <?php
		# Show Order Summary
        $sql = sprintf("select distinct ga_group as name from Players where game_id=%s",quote_smart($game_id));
        $result = mysql_query($sql);
        while ( $group = mysql_fetch_array($result) ) {
          if ( $group['name'] != "" ){
            if ( $mod || $actions['ga_group'] == $group['name'] ) {
              $sql2 = sprintf("select distinct `desc` from Game_orders, Players, Players_all where Game_orders.user_id=Players_all.user_id and Game_orders.game_id=Players_all.game_id and Players_all.original_id=Players.user_id and Players_all.game_id=Players.game_id and Game_orders.game_id=%s and Players.ga_group=%s",quote_smart($game_id),quote_smart($group['name']));
	          $result2 = mysql_query($sql2);
              while ( $action = mysql_fetch_array($result2) ) {
                $sql3 = sprintf("select u.name as player, concat(coalesce(concat(t.name,' '),''),user_text) as target, cancel from Game_orders left join Users t on t.id=Game_orders.target_id , Users u, Players, Players_all where u.id=Game_orders.user_id and Players.user_id=Players_all.original_id and Players_all.user_id=Game_orders.user_id and Players.game_id=Players_all.game_id and Players.game_id=Game_orders.game_id and Game_orders.game_id=%s and `desc`=%s and day=%s and ga_group=%s order by last_updated desc limit 0, 1",quote_smart($game_id),quote_smart($action['desc']),quote_smart($game['day']),quote_smart($group['name']));
                $result3 = mysql_query($sql3);
                while ( $order = mysql_fetch_array($result3) ) {
				  if ( $order['cancel'] == "" ) {
                    print "<li>".$group['name']." (".$order['player']."): ".$action['desc']." ".$order['target']."</li>";
			      }
                } # while ( $order = mysql_fetch_array($result3)
              } # while ( $action = mysql_fetch_array($result2)
            } # ( $mod || $actions['ga_group'] == $group['name'] )
          } # ( $group['name'] != "" )
		  else {
            $sql2 = sprintf("select name, id from Users, Players, Players_all where Players.user_id=Players_all.original_id and Players.game_id=Players_all.game_id and Users.id=Players_all.user_id and Players.game_id=%s and (ga_group='' or ga_group is null) order by name",quote_smart($game_id));
            $result2 = mysql_query($sql2);
            while ( $player = mysql_fetch_array($result2) ) {
              if ( $mod || ( $player['id']==$uid && $actions['ga_group'] == "") ) {
                $sql3 = sprintf("select distinct `desc` from Game_orders where game_id=%s and user_id=%s",quote_smart($game_id),quote_smart($player['id']));
                $result3 = mysql_query($sql3);
                while ( $action = mysql_fetch_array($result3) ) {
                  $sql4 = sprintf("select concat(coalesce(concat(t.name,' '),''),user_text) as target, cancel from Game_orders left join Users t on t.id=Game_orders.target_id where Game_orders.game_id=%s and Game_orders.user_id=%s and `desc`=%s and day=%s order by last_updated desc limit 0, 1",quote_smart($game_id),quote_smart($player['id']),quote_smart($action['desc']),quote_smart($game['day']));
                  $result4 = mysql_query($sql4);
                  while ( $order = mysql_fetch_array($result4) ) {
				    if ( $order['cancel'] == "" ) {
                      print "<li>".$player['name'].": ".$action['desc']." ".$order['target']."</li>";
					}
                  } # while ( $order = mysql_fetch_array($result4) )
                } # ( $action = mysql_fetch_array($result3) )
              } # ( $mod || $player['id']=$uid )
            } # while ( $player = mysql_fetch_array($result2)
          } # else after ( $group['name'] != "" )
        } # while ( $group = mysql_fetch_array($result) )
        print "</ul></td>";
		if ( $mod ) {
		  print "<td valign='top'><ul>";
		  $sql = sprintf("select name from Players, Players_all, Users where Players.user_id=Players_all.original_id and Players.game_id=Players_all.game_id and Users.id=Players.user_id and Players.ga_lock is not null and Players.game_id=%s",quote_smart($game_id));
		  $result = mysql_query($sql);
		  while ( $locked = mysql_fetch_array($result) ) {
            print "<li>".$locked['name']."</li>";
		  }
		  print "</ul></td>";
		}
		print "</tr></table>";
        print "<br /><br /><span style='font-weight:bold;'>Game Orders Full Log</span><br />";
        for ( $day=$game['day']; $day>=0; $day-- ) {
          print "<span>Day $day</span><br />";
          if ( $mod ) {
            $sql = sprintf("select p.name as user, ga_group, Roles.`type`, `desc`, if(cancel is null, concat(coalesce(concat(t.name,' '),''),user_text), 'canceled') as target, last_updated from Game_orders left join Users t on t.id=Game_orders.target_id, Users p, Roles, Players_all, Players where Game_orders.user_id=p.id and Players_all.original_id=Players.user_id and Players_all.user_id=Game_orders.user_id and Players_all.game_id=Game_orders.game_id and Players.game_id=Players_all.game_id and Roles.id=Players.role_id and Game_orders.game_id=%s and Game_orders.day=%s order by `desc`, last_updated desc",quote_smart($game_id),quote_smart($day));
          } # if ( $mod )
		  elseif ( $actions['ga_group'] != "" ) {
            $sql = sprintf("select p.name as user, ga_group, Roles.`type`, `desc`, if(cancel is null, concat(coalesce(concat(t.name,' '),''),user_text), 'canceled') as target, last_updated from Game_orders left join Users t on t.id=Game_orders.target_id, Users p, Roles, Players_all, Players where Game_orders.user_id=p.id and Players_all.original_id=Players.user_id and Players_all.user_id=Game_orders.user_id and Players_all.game_id=Game_orders.game_id and Players.game_id=Players_all.game_id and Roles.id=Players.role_id and Game_orders.game_id=%s and Game_orders.day=%s and ga_group=%s order by `desc`, last_updated desc",quote_smart($game_id),quote_smart($day),quote_smart($actions['ga_group']));
          } # elseif ( $actions['ga_group'] != "" )
		  else {
            $sql = sprintf("select p.name as user, ga_group, Roles.`type`, `desc`, if(cancel is null, concat(coalesce(concat(t.name,' '),''),user_text), 'canceled') as target, last_updated from Game_orders left join Users t on t.id=Game_orders.target_id, Users p, Roles, Players_all, Players where Game_orders.user_id=p.id and Players_all.original_id=Players.user_id and Players_all.user_id=Game_orders.user_id and Players_all.game_id=Game_orders.game_id and Players.game_id=Players_all.game_id and Roles.id=Players.role_id and Game_orders.game_id=%s and Game_orders.day=%s and Game_orders.user_id=%s order by `desc`, last_updated desc",quote_smart($game_id),quote_smart($day),quote_smart($uid));
          } # else after elseif ( $actions['ga_group'] != "" )
		 #print $sql;
          $result=mysql_query($sql);
          if ( mysql_num_rows($result) > 0 ) {
            print "\n<table border='1'>";
            print "<tr>";
            print "<th>Player</th><th>Role</th><th>Action</th><th>Target</th><th>Time Stamp</th>";
            print "</tr>";
            while ( $row = mysql_fetch_array($result) ) {
              print "<tr>";
              print "<td>".$row['user'];
              if ( $row['ga_group'] != "" ) { print " (".$row['ga_group'].")"; }
              print "</td>";
              print "<td>".$row['type']."</td>";
              print "<td>".$row['desc']."</td>";
              print "<td>".$row['target']."</td>";
              print "<td>".$row['last_updated']."</td>";
              print "</tr>";
            } # while ( $row = mysql_fetch_array($result) )
            print "</table>";
		  } # if ( mysql_num_rows($result) > 0 )
		} # for ( $day=$game['day']; $day>0; $day-- )
        print "</div>\n";
      } # if ( $actions['ga_desc'] != "" || $mod)
    } # if ( $game['game_order'] == "on" )
    ?>
    </td>
    </tr></table>
    </body>
    </html>
    <?php
  } # else after  # ( isset($_REQUEST['full']) )
} # else  after ( $status = "In Progress")

function show_chatRooms($game_id,$user_id) {
//Find out if user is moderator of this game.
$mod = is_moderator($user_id,$game_id);
//Get room id's for displaying.
  $output = "<div>";
  $js = "<script language='javascript'>\n";
  $js .= "<!--\n";
  global $start_room_id, $start_room_name, $uid;
  $sql = sprintf("select id, name, Chat_rooms.max_post, Chat_rooms.remaining_post, if(Chat_users.`lock`='Off' ,if(close<now(),'On',Chat_rooms.`lock`), Chat_users.`lock` ) as `lock`, created, open, if(close is null, now(), close) as close, if(close is null, concat('View post after: ',open), concat('View post between: ',open,' and ',close)) as user_view  from Chat_rooms, Chat_users where Chat_rooms.id=Chat_users.room_id and game_id=%s and user_id=%s order by `lock`, name",quote_smart($game_id),quote_smart($user_id));
  $result = mysql_query($sql);
  while ( $row = mysql_fetch_array($result) ) {
    $room_ids[] = $row['id'];
    $room_names[$row['id']] = $row['name'];
	$room_created[$row['id']] = $row['created'];
	$user_view[$row['id']] = $row['user_view'];
    $sql2 = sprintf("select count(*) from Chat_messages where post_time > %s and post_time < %s and room_id=%s",quote_smart($row['open']),quote_smart($row['close']),quote_smart($row['id']));
    $result2 = mysql_query($sql2);
    $num_messages[$row['id']] = mysql_result($result2,0,0);
    $sql2 = sprintf("select count(*) from Chat_messages, Chat_users where Chat_messages.room_id=Chat_users.room_id and Chat_messages.room_id=%s and Chat_users.user_id=%s and post_time > last_view and post_time > %s and post_time < %s",quote_smart($row['id']),quote_smart($uid),quote_smart($row['open']),quote_smart($row['close']));
	$result2 = mysql_query($sql2);
	if ( mysql_num_rows($result2) != 0 ) {
      $new_messages[$row['id']] = mysql_result($result2,0,0);
	} else {
      $new_messages[$row['id']] = 0;
	}
	$lock[$row['id']] = $row['lock'];
	$max_post[$row['id']] = $row['max_post'];
	$remaining_post[$row['id']] = $row['remaining_post'];
  }
  if ( count($room_names) == 0 ) {
    $output .= "<p>Either you are not a player of this game,<br /> or the Moderator has not set up any chat<br />rooms for you.  Please wait until the game<br />is finished to view all the game's<br />communications.</p>";
  } else {
    foreach ( $room_names as $room_id => $room_name ) {
      $output .= "<a href='javascript:change_rooms(\"$room_id\",\"$room_name\",\"".$room_created[$room_id]."\",\"".$user_view[$room_id]."\")'>$room_name</a> (".$num_messages[$room_id].")";
	  if ( $lock[$room_id] == "On" ) {
        $output .= "<img id='lock_img_$room_id' src='/images/lock_green.gif' />";
	  } elseif ( $lock[$room_id] == "Secure" ) {
        $output .= "<img id='lock_img_$room_id' src='/images/lock_red.gif' />";
	  }
      if ( $new_messages[$room_id] != 0 ) {
	    $count = $new_messages[$room_id];
        $output .= "<span>&nbsp;&nbsp;&nbsp;</span>";
		$output .= "<span onMouseOver='javascript:{document.getElementById(\"${room_id}_nm\").style.visibility=\"visible\";}' ";
	    $output .= "onMouseOut='javascript:{document.getElementById(\"${room_id}_nm\").style.visibility=\"hidden\"}'>";
        $output .= "<img src='/images/new_message.png' border='0'/></span>";
        $output .= "<span id='${room_id}_nm' style='position:absolute;border:solid black 1px; background-color:white; visibility:hidden;'>$count new messages</span> ";
      }
	  $output .= "<br />\n";
	  if ( $max_post[$room_id] != "" ) {
        $output .= "<span>&nbsp;&nbsp;&nbsp;</span>(RPL: ".$max_post[$room_id].", Remaining: ".$remaining_post[$room_id].")<br />\n";
	  }
      $sql2 = sprintf("select if(last_view>(date_sub(now(), interval 3 second)),'(online)','') as available from Chat_users where user_id=%s and room_id=%s ",quote_smart($uid),quote_smart($room_id));
	  $result2 = mysql_query($sql2);
	  $online = false;
	  if ( mysql_result($result2,0,0) == "(online)" ) { $online = true; }
      $sql2 = sprintf("select coalesce(alias,name) as name, color, max_post, remaining_post, if(last_view>(date_sub(now(), interval 3 second)),'(online)','') as available from Chat_users, Users where Chat_users.user_id=Users.id and room_id=%s order by available desc, name asc",quote_smart($room_id));
      $result2 = mysql_query($sql2);
	  $user_list = "";
      while ( $row = mysql_fetch_array($result2) ) {
	    #if ( $online || $row['available'] == "(online)" ) {
	    #if ( $online ) {
	    if ( $online || ( $mod && $row['available'] == "(online)") ) {
          $output .= "<span>&nbsp;&nbsp;&nbsp;</span><span style='color: ".$row['color'].";'>";
	      $output .= $row['name'];
	      $output .= "</span> ".$row['available']."<br />\n";
		  if ( $row['max_post'] != "" ) {
		    $output .= "<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>(PPL: ".$row['max_post'].", Remaining: ".$row['remaining_post'].")<br />";
		  }
	      $user_list .= $row['name'].",";
	    }
      }
      $js .= "userlist['$room_id'] = '$user_list';\n";;
    }
  }
  if ( count($room_ids) == 1 ) {
    $start_room_id = $room_ids[0];
    $start_room_name = $room_names[$start_room_id];
  }

  $js .= "//-->\n";
  $js .= "</script>";

  $output .= "</div>";
  $output .= $js;
                                                                                    
  return $output;
}

function display_chat($room_id) {
  global $status, $uid;
  $output = "<table class='forum_table' width='500px'>\n";
  $sql = sprintf("select name from Chat_rooms where id=%s",quote_smart($room_id));
  $result = mysql_query($sql);
  $room_name = mysql_result($result,0,0);
  $output .= "<tr><th>Room: $room_name</th></tr>\n";
  $format = '%b %e, %h:%i %p';
  if ( $status == "In Progress" ) {
    $sql = sprintf("select date_sub(last_view, interval 1 second) as last_view, open, if(close is null, now(), close) as close, if(close is null, 'open', if(close < now(), 'close', 'open')) as eye, now() from Chat_users where room_id=%s and user_id=%s",quote_smart($room_id),quote_smart($uid));
	$result = mysql_query($sql); $last_view = mysql_result($result,0,0); $open = mysql_result($result,0,1);
	$close = mysql_result($result,0,2);
	$eye_status = mysql_result($result,0,3);
    $sql = sprintf("select coalesce(alias,name) as name, message, color, date_format(post_time,%s) as post_time from Chat_messages, Chat_users, Users where Chat_messages.user_id=Users.id and Chat_users.user_id=Users.id and Chat_users.room_id=%s and Chat_messages.room_id=%s and (post_time > %s and post_time < %s) order by Chat_messages.id",quote_smart($format),quote_smart($room_id),quote_smart($room_id),quote_smart($open),quote_smart($close));
  } else {
    $sql = sprintf("select coalesce(alias,name) as name, message, color, date_format(post_time,%s) as post_time from Chat_messages, Chat_users, Users where Chat_messages.user_id=Users.id and Chat_users.user_id=Users.id and Chat_users.room_id=%s and Chat_messages.room_id=%s order by Chat_messages.id",quote_smart($format),quote_smart($room_id),quote_smart($room_id));
  }
  $result = mysql_query($sql);
  $output .= "<tr><td>";
  while ( $row = mysql_fetch_array($result) ) {
    $output .= "<span style='font-weight:bold; color:".$row['color']."'>".$row['name']." ".$row['post_time'].":</span> ".$row['message']."<br />\n";
  }
  $output .= "</td></tr>\n";
  $output .= "</table><br />\n";
  return $output;
}

function display_all($game_id) {
  $output = "<table class='forum_table' width='500px'>\n";
  $sql = sprintf("select title from Games where id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  $game_title = mysql_result($result,0,0);
  $output .= "<tr><th>All post for $game_title</th></tr>\n";
  $format = '%b %e, %h:%i %p';
  $sql = sprintf("select Chat_rooms.name as room_name, coalesce(alias,Users.name) as name, message, color, date_format(post_time,%s) as post_time from Chat_rooms, Chat_messages, Chat_users, Users where Chat_rooms.id=Chat_messages.room_id and Chat_rooms.id=Chat_users.room_id and Chat_messages.user_id=Users.id and Chat_users.user_id=Users.id and Chat_rooms.game_id=%s order by Chat_messages.id",quote_smart($format),quote_smart($game_id));
  $result = mysql_query($sql);
  $output .= "<tr><td>";
  while ( $row = mysql_fetch_array($result) ) {
    $output .= "<span style='font-style:italic;'>".$row['room_name']."</span><br />\n";
    $output .= "<span style='font-weight:bold; color:".$row['color']."'>".$row['name']." ".$row['post_time'].":</span> ".$row['message']."<br />\n";
  }
  $output .= "</td></tr>\n";
  $output .= "</table><br />\n";
  return $output;
}

?>
