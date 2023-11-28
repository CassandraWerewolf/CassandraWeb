<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "configure_chat_functions.php";
include_once "configure_physics_functions.php";

$mysql = dbConnect();

# Global Variable with the list of the tabs
$tab_list[1] = "Chat Room List";
$tab_list[2] = "Game Orders";
$tab_list[3] = "Format Options";
$tab_list[4] = "Moderator Controls";

# Displays the desired chat room.
function display_chat($room_id,$status,$uid) {
  $output = "<table class='forum_table' width='500px'>\n";
  $sql = sprintf("select name from Chat_rooms where id=%s", quote_smart($room_id));
  $mysql = dbConnect();
  $result = mysqli_query($mysql, $sql);
  $room_name = mysqli_result($result,0,0);
  $output .= "<tr><th>Room: $room_name</th></tr>\n";
  $format = '%b %e, %h:%i %p';
  if ( $status == "In Progress" ) {
    $sql = sprintf("select date_sub(last_view, interval 1 second) as last_view, open, if(close is null, now(), close) as close, if(close is null, 'open', if(close < now(), 'close', 'open')) as eye, now() from Chat_users where room_id=%s and user_id=%s",quote_smart($room_id),quote_smart($uid));
    $result = mysqli_query($mysql, $sql); $last_view = mysqli_result($result,0,0); $open =
 mysqli_result($result,0,1);
    $close = mysqli_result($result,0,2);
    $eye_status = mysqli_result($result,0,3);
    $sql = sprintf("select coalesce(alias,name) as name, message, color, date_format(post_time,%s) as post_time from Chat_messages inner join Users on Chat_messages.user_id=Users.id left join Chat_users on Chat_users.user_id=Users.id and Chat_messages.room_id = Chat_users.room_id where Chat_messages.room_id=%s and (post_time > %s and post_time < %s) order by Chat_messages.id",quote_smart($format),quote_smart($room_id),quote_smart($open),quote_smart($close));
  } else {
    $sql = sprintf("select coalesce(alias,name) as name, message, color, date_format(post_time,%s) as post_time from Chat_messages inner join Users on Chat_messages.user_id=Users.id left join Chat_users on Chat_users.user_id=Users.id and Chat_messages.room_id = Chat_users.room_id where Chat_messages.room_id=%s order by Chat_messages.id",quote_smart($format),quote_smart($room_id),quote_smart($room_id));
  }
  $result = mysqli_query($mysql, $sql);
  $output .= "<tr><td>";
  while ( $row = mysqli_fetch_array($result) ) {
    $output .= "<span style='font-weight:bold; color:".$row['color']."'>".$row['name']." ".$row['post_time'].":</span> ".$row['message']."<br />\n";
  }
  $output .= "</td></tr>\n";
  $output .= "</table><br />\n";
  return $output;
}

# Displays all the chat messages by time.
function display_all($game_id) {
  $output = "<table class='forum_table' width='500px'>\n";
  $sql = sprintf("select title from Games where id=%s",quote_smart($game_id));
  $result = mysqli_query($mysql, $sql);
  $game_title = mysqli_result($result,0,0);
  $output .= "<tr><th>All post for $game_title</th></tr>\n";
  $format = '%b %e, %h:%i %p';
  $sql = sprintf("select Chat_rooms.name as room_name, coalesce(alias,Users.name) as name, message, color, date_format(post_time,%s) as post_time from Chat_rooms, Chat_messages, Chat_users, Users where Chat_rooms.id=Chat_messages.room_id and Chat_rooms.id=Chat_users.room_id and Chat_messages.user_id=Users.id and Chat_users.user_id=Users.id and Chat_rooms.game_id=%s order by Chat_messages.id",quote_smart($format),quote_smart($game_id));
  $result = mysqli_query($mysql, $sql);
  $output .= "<tr><td>";
  while ( $row = mysqli_fetch_array($result) ) {
    $output .= "<span style='font-style:italic;'>".$row['room_name']."</span><br />\n";
    $output .= "<span style='font-weight:bold; color:".$row['color']."'>".$row['name']." ".$row['post_time'].":</span> ".$row['message']."<br />\n";
  }
  $output .= "</td></tr>\n";
  $output .= "</table><br />\n";
  return $output;
}

# Displays the tab navigation.
function display_tabs($active,$mod) {
  global $tab_list ;
  $output = "";
  foreach ( $tab_list as $tab_num => $tab_name ) {
    if ( !$mod && $tab_num == 4 ) { continue; }
    #$bgcolor = '#F5F5FF';
    $bgcolor = '#D1D1E1';
    if ( $tab_num == $active ) {
      #$bgcolor = '#D1D1E1';
      $bgcolor = '#F5F5FF';
    }
    $output .= "<span onClick='switch_tab(\"$tab_num\")' style='background-color:$bgcolor; border:1px solid #8888FF; padding:3px;'>$tab_name</span>";
  }

  return $output;
}

function show_tab($active,$user_id,$game_id) {
  switch ($active) {
  case 4:
    # Moderator Controls
    return mod_controls();
    break;
  case 2:
    # Game Orders
    return game_orders($user_id,$game_id);
    break;
  case 1:
    # Chat Room List
    return chat_room_list($user_id,$game_id);
    break;
  case 3:
    #Format Options
    return format_options();
    break;
  }
}

function mod_controls() {
  $output = "<a href='javascript:show_broadcast()'>[Broadcast Message]</a> ";
  $output .= "<a href='javascript:show_room_edit()'>[Edit Room]</a> ";
  $output .= "<a href='javascript:show_room_add()'>[Add A Room]</a> ";
  $output .= "<br /><br />";
  $output .= "<div id='mod_control_div'></div>";

  return $output;
}

function broadcast_form($game_id) {
  $output = "<form id='broadcast_form' name='brodcast' method='post' onSubmit=' return sendBroadcastText()'; >";
  $output .= "<input type='hidden' id='game_id' name='game_id' value='$game_id;' />";
  $output .= "<table border='0'>";
  $output .= "<tr><th colspan='2'>Broadcast message:</th></tr>";
  $output .= "<tr><td><textarea id='broad_text' name='broad_text' style='width:200px; height:48px;' onKeyPress='return enter_broadcast(event)'></textarea><td>";
  $output .= "<td rowspan='2'>";
  $output .= "<input type='checkbox' id='post_to_all' />All Rooms<br />";
  $sql = sprintf("select id, name from Chat_rooms where game_id=%s",quote_smart($game_id));
  $result = mysqli_query($mysql, $sql);
  while ( $room = mysqli_fetch_array($result) ) {
    $output .= "<input type='checkbox' id='post_to_".$room['id']."'>".$room['name']."</br>";
  }
  $output .= "</td></tr>";
  $output .= "<tr><td><input type='submit' id='broadcast' name='broadcast' value='Broadcast to Selected Rooms' />";
  $output .= "<br />";
  $output .= "</table>";
  $output .= "</form>";


  return $output;
}

function room_edit_form($game_id,$room_id) {
  $output .= "<table border='1'>";
  $output .= "<tr><th valign='top' >Room Info:</th>";
  $output .= "<td>".room_info($room_id,'chat')."</td></tr>";
  $output .= "<tr><th valign ='top' >Player Info:</th>";
  $output .= player_info($game_id,$room_id)."</tr>";
  $output .= "<tr><td colspan='2'><div id='dialog_div'></div></td></tr>";
  $output .= "</table>";

  return $output;
}

function game_orders($user_id,$game_id) {
  $output = "";
  $mod = is_moderator($user_id,$game_id);
  $sql = sprintf("select day from Games where id=%s",quote_smart($game_id));
  $result = mysqli_query($mysql, $sql);
  $day = mysqli_result($result,0,0);
  $output .= "<b>Game Orders for Day $day:</b> (Please refresh this tab before performing the actions to make sure they are up to date.)<br />";
  if ( !$mod ) {
    $output .= game_order_input($user_id,$game_id,$day);
    $output .= phys_order_input($user_id,$game_id);
  }
  $output .= "<br /><table><tr>";
  $output .= "<th>Game Actions</th>";
  if ( $mod ) { $output .= "<th>Locked Actions</th>"; }
  $output .= "</tr><tr>";
  $output .= "<td valign='top'>".game_order_sumary($user_id,$game_id,$day)."</td>";
  if ( $mod ) { $output .= "<td valign='top'>".locked_list($game_id)."</td>"; }
  $output .= "</tr></table><br />";

  $output .= "<b>Game Orders Log</b><br />";
  $output .= game_order_logx($user_id,$game_id,$mod);

  return $output;
}

function game_order_logx($user_id,$game_id,$mod) {
  $group = "";
  $output = "";
  if ( ! $mod ) {
    $sql = sprintf("select ga_group from Players, Players_all where Players.user_id=Players_all.original_id and Players.game_id=Players_all.game_id and Players.game_id=%s and Players_all.user_id=%s",quote_smart($game_id),quote_smart($user_id));
	  $result = mysqli_query($mysql, $sql);
    if ( mysqli_num_rows($result) == 0 ) {
	     $group = "";
	  } else {
	     $group = mysqli_result($result,0,0);
	  }
  }
  if ($mod)
  {
    $sql = sprintf("select p.id as user_id, p.name as user, ga_group, Roles.`type`, `desc`, if(cancel is null, concat(coalesce(concat(if(target_id=0,'Daykill Victim',t.name),' '),''),user_text),'canceled') as target, last_updated, Game_orders.day as day from Game_orders left join Users t on t.id=Game_orders.target_id, Users p, Roles, Players_all, Players where Game_orders.user_id=p.id and Players_all.original_id=Players.user_id and Players_all.user_id=Game_orders.user_id and Players_all.game_id=Game_orders.game_id and Players.game_id=Players_all.game_id and Roles.id=Players.role_id and Game_orders.game_id=%s order by last_updated desc",quote_smart($game_id));
  } else {
    if ($group != "")
    {
      $sql = sprintf("select p.id as user_id, p.name as user, ga_group, Roles.`type`, `desc`, if(cancel is null, concat(coalesce(concat(if(target_id=0,'Daykill Victim',t.name),' '),''),user_text),'canceled') as target, last_updated, Game_orders.day as day from Game_orders left join Users t on t.id=Game_orders.target_id, Users p, Roles, Players_all, Players where Game_orders.user_id=p.id and Players_all.original_id=Players.user_id and Players_all.user_id=Game_orders.user_id and Players_all.game_id=Game_orders.game_id and Players.game_id=Players_all.game_id and Roles.id=Players.role_id and Game_orders.game_id=%s and (p.id=%s or ga_group=%s) order by last_updated desc",quote_smart($game_id), quote_smart($user_id), quote_smart($group));
    } else {
      $sql = sprintf("select p.id as user_id, p.name as user, ga_group, Roles.`type`, `desc`, if(cancel is null, concat(coalesce(concat(if(target_id=0,'Daykill Victim',t.name),' '),''),user_text),'canceled') as target, last_updated, Game_orders.day as day from Game_orders left join Users t on t.id=Game_orders.target_id, Users p, Roles, Players_all, Players where Game_orders.user_id=p.id and Players_all.original_id=Players.user_id and Players_all.user_id=Game_orders.user_id and Players_all.game_id=Game_orders.game_id and Players.game_id=Players_all.game_id and Roles.id=Players.role_id and Game_orders.game_id=%s and p.id=%s order by last_updated desc",quote_smart($game_id), quote_smart($user_id));
    }
  }
  $result = mysqli_query($mysql, $sql);
  $count = mysqli_num_rows($result);
  if ( $count == 0 ) { return; }
  $day = -1;
  while ( $row = mysqli_fetch_array($result) ) {
    if ($day != $row['day'])
    {
      if ($day != -1)
      {
        $output .= "</table>";
      }
      $day = $row['day'];
      $output .= "<span>Day $day</span><br />";
      $output .= "<table border='1'><tr><th>Player</th><th>Role</th><th>Action</th><th>Target</th><th>Time Stamp</th></tr>";
    }
    if ( $mod || $row['user_id'] == $user_id || ( $group != "" && $group == $row['ga_group']) ) {
      $output .= "<tr><td>".$row['user']."</td><td>".$row['type']."</td><td>".$row['desc']."</td><td>".$row['target']."</td><td>".$row['last_updated']."</td></tr>\n";
	  }
  }
  $output .= "</table>";

  return $output;
}


function phys_order_input($user_id,$game_id) {
  $output = "";
  $orig_user = $user_id;
  $sql = sprintf("select p.user_id as uid, p.loc_id as loc_id FROM Players p where p.game_id = %s and p.user_id = (select pa.original_id from Players_all pa where pa.game_id=%s and pa.user_id=%s limit 0,1)", quote_smart($game_id), quote_smart($game_id), quote_smart($user_id));
  $result = mysqli_query($mysql, $sql);
  if (mysqli_num_rows($result) > 0) {
    $orig_user = mysqli_result($result,0,0);
    $loc_id = mysqli_result($result,0,1);
  }

  if ($loc_id) {
    #movements
    $sql = sprintf("select e.id as id, e.name as name FROM Loc_exits l, Exits e where e.id=l.exit_id and l.loc_from_id = %s and (e.template_id is null or e.template_id in (select distinct template_id from Items i where i.game_id=%s and i.owner_type='user' and i.owner_ref_id=%s))", quote_smart($loc_id), quote_smart($game_id), quote_smart($orig_user));
    $result = mysqli_query($mysql, $sql);
    if ( mysqli_num_rows($result) > 0 ) {
      $sql_current = sprintf("select exit_id from Move_orders where game_id=%s and user_id=%s and status='active'", quote_smart($game_id), quote_smart($orig_user));

      $result_current = mysqli_query($mysql, $sql_current);
      if (mysqli_num_rows($result_current) > 0 ) {
        $current = mysqli_result($result_current,0,0);
      }
      $sql_limit = sprintf("SELECT IFNULL( p.phys_move_limit, (SELECT g.phys_move_limit FROM Games g WHERE g.id = %s) ) - p.phys_moves FROM ( SELECT pp.phys_moves, pp.phys_move_limit FROM Players pp WHERE pp.game_id = %s AND user_id = %s) p",quote_smart($game_id),quote_smart($game_id),quote_smart($orig_user));
      $result_limit = mysqli_query($mysql, $sql_limit);
      if (mysqli_num_rows($result_limit) > 0 ) {
        $opt_limit = "(";
        $opt_limit .= mysqli_result($result_limit,0,0);
        $opt_limit .= " moves left)";
      }
      $output .= "<br /><form id='move_action' method='post' action ='/game_action.php'>";
      $output .= "<input type='hidden' id='ga_user_id' name='user_id' value='$user_id' />";
      $output .= "<input type='hidden' id='ga_game_id' name='game_id' value='$game_id' />";
      $output .= "Movement Options: $opt_limit<br /><select name='exit'>";
      while ($exit = mysqli_fetch_array($result)) {
        $output .= "<option value='". $exit['id'] ."' ".(($current==$exit['id'])? "selected=1" : "")." >". $exit['name'] ."</option>";
      }
      $output .= "</select>";
      $output .= "<input type='submit' name='submit_move' value='Submit' /><input type='submit' name='cancel_move' value='Cancel Previous Order' /></td>";
      $output .= "</form>";
    }

    #items in loc
    $sql = sprintf("select id, name FROM Items where game_id=%s and owner_type='loc' and owner_ref_id=%s and visibility in ('conceal','obvious')",quote_smart($game_id),quote_smart($loc_id));
    $result = mysqli_query($mysql, $sql);
    if ( mysqli_num_rows($result) > 0 ) {
      $output .= "<br /><form id='remote_item_action' method='post' action ='/game_action.php'>";
      $output .= "<input type='hidden' id='ga_user_id' name='user_id' value='$user_id' />";
      $output .= "<input type='hidden' id='ga_game_id' name='game_id' value='$game_id' />";
      $output .= "Visible Items:<br /><select name='rem_item'>";
      while ($item = mysqli_fetch_array($result)) {
        $output .= "<option value='". $item['id'] ."' >". $item['name'] ."</option>";
      }
      $output .= "</select>";
      $output .= "<input type='submit' name='submit_pickup' value='Pick Up' /><input type='submit' name='cancel_pickup' value='Cancel Pickup' />";
      $output .= "</form>";
    }
  }

  # items in inv
  $sql_inv = sprintf("select id, name, mobility FROM Items where game_id=%s and owner_type='user' and owner_ref_id=%s", quote_smart($game_id), quote_smart($orig_user));
  $result_inv = mysqli_query($mysql, $sql_inv);
  if (mysqli_num_rows($result_inv) > 0) {
      $output .= "<br /><form id='inv_item_action' method='post' action ='/game_action.php'>";
      $output .= "<input type='hidden' id='ga_user_id' name='user_id' value='$user_id' />";
      $output .= "<input type='hidden' id='ga_game_id' name='game_id' value='$game_id' />";
      $output .= "<input type='hidden' id='ga_loc_id' name='loc_id' value='$loc_id' />";
      $output .= "Your Items:<br />";
      $p_loc = get_player_loc_info($game_id);
      if ($loc_id) {
        foreach ($p_loc as $pid=>$player) {
          if ($player['loc_id'] == $loc_id) {
            $p_in_loc[$pid] = $player;
      }}
      }
      $p_names = get_all_aliases_by_ids($game_id);
      while ($item = mysqli_fetch_array($result_inv)) {
        $item_name = $item['name'];
        $item_id = $item['id'];
        $output .= "$item_name: ";
        $output .= "<input type='radio' name='item_$item_id' value='keep'> Keep ";
        if ($item['mobility'] != 'fixed' && ($item['mobility'] == 'nonphys' || count($p_in_loc) > 0)) {
          $p_to_use = $item['mobility'] == 'nonphys' ? $p_loc : $p_in_loc;
          $output .= "<input type='radio' name='item_$item_id' value='pass'>Pass to: <select name='target_$item_id'>";
          foreach ($p_to_use as $pid => $player) {
            $output .= "<option value=$pid>" . $p_names[intval($player['real_id'])] . "</option>";
          }
          $output .= "</select> ";
        }
        if ($loc_id) {
          $output .= "<input type='radio' name='item_$item_id' value='drop'> Drop ";
        }
        $output .= "<br />";
      }
      $output .= "<input type='submit' name='submit_inv' value='Submit' /><input type='submit' name='cancel_inv' value='Cancel' />";
      $output .= "</form>";
  }

  return $output;
}

function get_current_item_orders($user_id,$game_id) {
#  $sql = sprintf()
}

function game_order_input($user_id,$game_id,$day) {
  $output = "";
  $sql = sprintf("select phase from Games where id=%s",quote_smart($game_id));
  $result = mysqli_query($mysql, $sql);
  $game_phase = mysqli_result($result,0,0);
  $sql = sprintf("select game_action, ga_desc, ga_text, ga_group, ga_lock, death_day from Players, Players_all where Players.game_id = Players_all.game_id and Players.user_id=Players_all.original_id and Players_all.user_id=%s and Players.game_id=%s ",quote_smart($user_id),quote_smart($game_id));
  $result = mysqli_query($mysql, $sql);
  if ( mysqli_num_rows($result) == 0 ) {
    $actions['ga_desc'] = '';
    $actions['ga_lock'] = "";
  } else {
    $actions = mysqli_fetch_array($result);
  }
  #If dead, or actions are locked don't show the order form
  if ( $actions['death_day'] == "" && $actions['ga_lock'] == "" ) {
    $output .= "<br /><form id='game_action' method='post' action='/game_action.php'>";
	$output .= "<input type='hidden' id='ga_user_id' name='user_id' value='$user_id' />";
    $output .= "<input type='hidden' id='ga_game_id' name='game_id' value='$game_id' />";
	$output .= "<table border='0'><tr>";
    #If the player has specific orders to input show form
    if ( $actions['ga_desc'] != "" ) {
      $output .= "<td width='50%'><input type='hidden' name='day' value='$day' />";
  	  $output .=  "Game Action for Day/Night $day:<br />";
      $descs = preg_split("/,/",$actions['ga_desc']);
      $output .=  "<select name='desc'>";
	  foreach ( $descs as $desc ) {
	    $output .=  "<option value='$desc'>$desc</option>";
	  }
	  $output .= "</select><br />";
	  # Show drop down list of players names if needed
	  if ( $actions['game_action'] != "none" ) {
	    $output .= "<select name='target_id'>";
	    $sql = sprintf("select Users.id, Users.name, if(death_phase='' or death_phase is null or death_phase='Alive','Living','Dead') as status from Players_r, Users where Players_r.user_id = Users.id and Players_r.game_id=%s order by name", quote_smart($game_id));
	    $result = mysqli_query($mysql, $sql);
        $count = 0;
		if ( $actions['game_action'] == "dead" && $game_phase == "day") {
          $output .= "<option value='0'>Today's Daykill Victim</option>";
		  $count++;
		}
	    while ( $user = mysqli_fetch_array($result) ) {
	      if ( $actions['game_action'] == "alive" && $user['status'] == "Dead" ) { continue; }
	      if ( $actions['game_action'] == "dead" && $user['status'] == "Living" ) { continue; }
	      $output .= "<option value=".$user['id'].">".$user['name']." (".$user['status'].")</option>";
	      $count++;
	    }
	    if ( $count == 0 ) {
	      if ( $actions['game_action'] == "alive" ) {
	        $output .=  "<option value=''>No Living players</option>";
	      } else {
	        $output .=  "<option value=''>No Dead players</option>";
	      }
		}
	    $output .= "</select><br />";
	  } # end player name dropdown list
      if ( $actions['ga_text'] == "on" ) {
        $output .=  "<input type='text' id='user_text' name='user_text' /><br />";
      }
      $output .=  "Post to: <br />";
      $sql = sprintf("select id, name from Chat_rooms, Chat_users where Chat_rooms.id=Chat_users.room_id and Chat_rooms.`lock`='Off' and Chat_users.`lock`='Off' and user_id=%s and game_id=%s order by name",quote_smart($user_id),quote_smart($game_id));
      $result = mysqli_query($mysql, $sql);
      while ( $room = mysqli_fetch_array($result) ) {
        $special = "";
        if ( preg_match("/Mod chat -/", $room['name']) ) { $special = "checked='checked'"; }
        $output .=  "<input type='checkbox' name='room_".$room['id']."' $special />".$room['name']."<br />";
      }
      $output .= "<input type='submit' name='submit' value='Submit' /><input type='submit' name='cancel' value='Cancel Previous Order' /></td>";
    }
    $output .= "<td valign='top'><p>To bring dawn early, all players must 'lock game actions' even if they don't have any.</p>";
    $output .= "<input type='button' name='lock' value='Lock Game Action' onClick='lock_ga()' /></td>";
	$output .= "</tr></table>";
    $output .= "</form>";
  } else {
    if ( $actions['ga_lock'] != "" ) {
      $output .= "<span style='color:red;'>You have locked your game actions for Day $day.</span>";
	  $output .= "<input type='button' name='unlock' value='Unlock Game Action' onClick='unlock_ga()' /></td>";
    }
  }

  return $output;
}

function game_order_sumary($user_id,$game_id,$day) {
  $mod = is_moderator($user_id,$game_id);
  if ( $mod ) {
    $sql = sprintf("select * from Players, Players_all, Roles, Users where Players.user_id=Players_all.original_id and Players.game_id=Players_all.game_id and Players_all.user_id=Users.id and Roles.id=Players.role_id and Players.game_id=%s order by Players.ga_group desc, Users.name",quote_smart($game_id));
  } else {
    $sql = sprintf("select * from Players, Players_all, Roles, Users where Players.user_id=Players_all.original_id and Players.game_id=Players_all.game_id and Players_all.user_id=Users.id and Roles.id=Players.role_id and Players.game_id=%s and Users.id=%s",quote_smart($game_id),quote_smart($user_id));
  }
  $result = mysqli_query($mysql, $sql);
  $done_groups[] = "";
  while ( $player = mysqli_fetch_array($result) ) {
    #If the player is part of a group
    if ( $player['ga_group'] != "" ) {
      if ( ! array_search($player['ga_group'],$done_groups) ) {
        # Group needs to be processed.
		$done_groups[] = $player['ga_group'];
		$sql2 = sprintf("select distinct `desc` from Game_orders, Players, Players_all where Game_orders.user_id=Players_all.user_id and Game_orders.game_id=Players_all.game_id and Players_all.original_id=Players.user_id and Players_all.game_id=Players.game_id and Game_orders.game_id=%s and Players.ga_group=%s order by `desc`",quote_smart($game_id),quote_smart($player['ga_group']));
		$result2 = mysqli_query($mysql, $sql2);
		while ( $action = mysqli_fetch_array($result2) ) {
		  $sql3 = sprintf("select u.name as player, concat(coalesce(concat(if(target_id=0,'Daykill Victim',t.name),' '),''),user_text) as target, cancel from Game_orders left join Users t on t.id=Game_orders.target_id , Users u, Players, Players_all where u.id=Game_orders.user_id and Players.user_id=Players_all.original_id and Players_all.user_id=Game_orders.user_id and Players.game_id=Players_all.game_id and Players.game_id=Game_orders.game_id and Game_orders.game_id=%s and `desc`=%s and day=%s and ga_group=%s order by last_updated desc limit 0, 1",quote_smart($game_id),quote_smart($action['desc']),quote_smart($day),quote_smart($player['ga_group']));
	      $result3 = mysqli_query($mysql, $sql3);
	      while ( $order = mysqli_fetch_array($result3) ) {
		    if ( $order['cancel'] == "" ) {
	          $output .=  "<li>".$order['player']." (".$player['ga_group']."): ".$action['desc']." ".$order['target']."</li>";
			}
	      } # while ( $order = mysqli_fetch_array($result3)
	    } # while ( $action = mysqli_fetch_array($result2)
	  }
	} else {
	  # For player not in a group
	  $sql2 = sprintf("select distinct `desc` from Game_orders where game_id=%s and user_id=%s",quote_smart($game_id),quote_smart($player['id']));
	  $result2 = mysqli_query($mysql, $sql2);
	  while ( $action = mysqli_fetch_array($result2) ) {
	    $sql3 = sprintf("select concat(coalesce(concat(if(target_id=0,'Daykill Victim',t.name),' '),''),user_text) as target, cancel from Game_orders left join Users t on t.id=Game_orders.target_id where Game_orders.game_id=%s and Game_orders.user_id=%s and `desc`=%s and day=%s order by last_updated desc limit 0, 1",quote_smart($game_id),quote_smart($player['id']),quote_smart($action['desc']),quote_smart($day));
        $result3 = mysqli_query($mysql, $sql3);
        while ( $order = mysqli_fetch_array($result3) ) {
		  if ( $order['cancel'] == "" ) {
            $output .=  "<li>".$player['name']." (".$player['type']."): ".$action['desc']." ".$order['target']."</li>";
          }
        } # while ( $order = mysqli_fetch_array($result3) )
      } # ( $action = mysqli_fetch_array($result2) )
	}
  }
  return $output;
}

function game_order_log($user_id,$game_id,$day) {
  $mod = is_moderator($user_id,$game_id);
  $group = "";
  $output = "";
  if ( ! $mod ) {
    $sql = sprintf("select ga_group from Players, Players_all where Players.user_id=Players_all.original_id and Players.game_id=Players_all.game_id and Players.game_id=%s and Players_all.user_id=%s",quote_smart($game_id),quote_smart($user_id));
	$result = mysqli_query($mysql, $sql);
    if ( mysqli_num_rows($result) == 0 ) {
	  $group = "";
	} else {
	  $group = mysqli_result($result,0,0);
	}
  }
  $sql = sprintf("select p.id as user_id, p.name as user, ga_group, Roles.`type`, `desc`, if(cancel is null, concat(coalesce(concat(if(target_id=0,'Daykill Victim',t.name),' '),''),user_text),'canceled') as target, last_updated from Game_orders left join Users t on t.id=Game_orders.target_id, Users p, Roles, Players_all, Players where Game_orders.user_id=p.id and Players_all.original_id=Players.user_id and Players_all.user_id=Game_orders.user_id and Players_all.game_id=Game_orders.game_id and Players.game_id=Players_all.game_id and Roles.id=Players.role_id and Game_orders.game_id=%s and Game_orders.day=%s order by last_updated desc",quote_smart($game_id),quote_smart($day));
  $result = mysqli_query($mysql, $sql);
  $count = mysqli_num_rows($result);
  if ( $count == 0 ) { return; }
  $output .= "<table border='1'><tr><th>Player</th><th>Role</th><th>Action</th><th>Target</th><th>Time Stamp</th></tr>";
  while ( $row = mysqli_fetch_array($result) ) {
    if ( $mod || $row['user_id'] == $user_id || ( $group != "" && $group == $row['ga_group']) ) {
      $output .= "<tr><td>".$row['user']."</td><td>".$row['type']."</td><td>".$row['desc']."</td><td>".$row['target']."</td><td>".$row['last_updated']."</td></tr>\n";
	}
  }
  $output .= "</table>";

  return $output;
}

function locked_list($game_id) {
  $output = "";
  $sql = sprintf("select name, Roles.`type` from Players, Players_all, Users, Roles where Players.user_id=Players_all.original_id and Players.game_id=Players_all.game_id and Users.id=Players_all.user_id and Roles.id=Players.role_id and Players.ga_lock is not null and Players.game_id=%s order by name",quote_smart($game_id));
  $result = mysqli_query($mysql, $sql);
  $output .= "<ul>";
  while ( $locked = mysqli_fetch_array($result) ) {
    $output .=  "<li>".$locked['name']."(".$locked['type'].")</li>";
  }
  $output .=  "</ul>";

  return $output;
}

function chat_room_list($user_id,$game_id) {
  $output = "";
  $mysql = dbConnect();
  $sql = sprintf("select id, name, Chat_rooms.max_post, Chat_rooms.remaining_post, if(Chat_users.`lock`='Off' ,if(close < now(),'On',Chat_rooms.`lock`), Chat_users.`lock` ) as `lock`, created, open, if(close is null, now(), close) as close, if(close is null, concat('View post after: ',open), concat('View post between: ',open,' and ',close)) as user_view  from Chat_rooms, Chat_users where Chat_rooms. id=Chat_users.room_id and game_id=%s and user_id=%s order by `lock`, name",quote_smart($game_id),quote_smart($user_id));
  $result = mysqli_query($mysql, $sql);
  while ( $room = mysqli_fetch_array($result) ) {
    $sql2 = sprintf("select count(*) from Chat_messages where post_time >= %s and post_time <= %s and room_id=%s",quote_smart($room['open']),quote_smart($room['close']),quote_smart($room['id']));
	$result2 = mysqli_query($mysql, $sql2);
	$num_messages = mysqli_result($result2,0,0);
    $sql2 = sprintf("select count(*) from Chat_messages, Chat_users where Chat_messages.room_id=Chat_users.room_id and Chat_messages.room_id=%s and Chat_users.user_id=%s and post_time > last_view and post_time >= %s and post_time <= %s",quote_smart($room['id']),quote_smart($user_id),quote_smart($room['open']),quote_smart($room['close']));
    $result2 = mysqli_query($mysql, $sql2);
	$new_messages = mysqli_result($result2,0,0);
    $output .= "<a href='javascript:change_room(\"".$room['id']."\")'>".$room['name']."</a> ($num_messages)";
	if ( $room['lock'] == "On" ) {
	  $output .= "<img src='/images/lock_green.gif' />";
	} elseif ( $room['lock'] == "Secure" ) {
	  $output .= "<img src='/images/lock_red.gif' />";
    }
	if ( $new_messages != 0 ) {
	  $output .= "<span>&nbsp;&nbsp;&nbsp;</span>";
	  $output .= "<span onMouseOver='javascript:{document.getElementById(\"".$room['id']."_nm\").style.visibility=\"visible\";}' ";
      $output .= "onMouseOut='javascript:{document.getElementById(\"".$room['id']."_nm\").style.visibility=\"hidden\"}'>";
      $output .= "<img src='/images/new_message.png' border='0'/></span>";
      $output .= "<span id='".$room['id']."_nm' style='position:absolute;border:solid black 1px; background-color:white; visibility:hidden;'>$new_messages new messages</span> ";
    }
    $output .= "<br />\n";
	if ( $room['max_post'] != "" ) {
	  $output .= "<span>&nbsp;&nbsp;&nbsp;</span>(RPL: ".$room['max_post'].", Remaining: ".$room['remaining_post'].")<br />\n";
   }
  }
  $output .= "<input type='button' id='read_all' name='read_all' value='Mark all rooms as read' onClick='mark_as_read()' /></td></tr>";

  return $output;
}
function format_options() {
  $output = "";
  $output .= "<a href='javascript:format_around(\"<b>\",\"</b>\")'>Bold</a><br />\n";
  $output .= "<a href='javascript:format_around(\"<i>\",\"</i>\")'>Italics</a><br />\n";
  $output .= "<a href='javascript:format_around(\"<u>\",\"</u>\")'>Underline</a><br />\n";
  $output .= "<a href='javascript:format_around(\"\",\"<br />\")'>New Line</a><br />\n";

  return $output;
}

function display_chat_room($room_id,$user_id) {
  $mysql = dbConnect();
  $sql = sprintf("select * from Chat_rooms where id=%s",quote_smart($room_id));
  $result = mysqli_query($mysql, $sql);
  $room = mysqli_fetch_array($result, MYSQLI_ASSOC);
  $sql = sprintf("select * from Chat_users where room_id=%s and user_id=%s",quote_smart($room_id),quote_smart($user_id));
  $result = mysqli_query($mysql, $sql);
  $user = mysqli_fetch_array($result, MYSQLI_ASSOC);
  $end_post = "";
  if ( $user['close'] != "" ) { $end_post = "and post_time < '".$user['close']."'"; }
  $sql = sprintf("select count(*) from Chat_messages where post_time > %s $end_post and room_id=%s",quote_smart($user['open']),quote_smart($room['id']));
  $result = mysqli_query($mysql, $sql);
  $num_messages = mysqli_result($result,0,0);

  $output = "";
  $output .= "<table><tr>";
  $read_only = false;
  if ( $room_id == 0 ) {
    $output .= "<td>Please Select a Room</td>";
  } else {
    $output .= "<td valign='top' style='width:250px;'><b style='font-size:12pt;'>".$room['name']."</b> ($num_messages)<br />\n";
    if ( $room['lock'] != 'Off' || $user['lock'] != 'Off' ) {
      $output .= "(read only)<br />\n";
      $read_only = true;
    }
    $output .= "Created: ".$room['created']."<br />\n";
    $output .= "View Post after: ".$user['open']."<br /></td>\n";
  }
  $output .= "<td valign='top'>";
  $output .= "<div id='player_list' style='visibility:visible; height:100px; width:150px; overflow:auto;' >";
  if ($user['close'] == "" || $user['lock'] == 'Off') {
    $output .= list_players($room_id, false, $user_id);
  }
  $output .= "</div></td>";
  $output .= "</tr></table>";
  $output .= "<div id='div_chat' style='height: 300px; width: 500px; overflow: auto; background-color: #dddddd; border: 1px solid #555555;'> </div>";
  $output .= "<form id='room' name='room' onSubmit=\"return blockSubmit()\" method='post' >\n";
  $output .= "  <input type='hidden' id='room_id' name='room_id' value='$room_id' />\n";
  $output .= "<input type='hidden' id='message' name='message' value='' />\n";
  $output .= "<input type='hidden' id='to' name='to' value='' />\n";
  $output .= "<input type='hidden' id='read_only' name='read_only' value='$read_only' />\n";
  $output .= "<textarea id='text' name='text' style='width: 500px; height: 80px;' onKeyPress='return enter_submit(event)'></textarea>\n";
  $output .= "<br />\n";
  $output .= "<input type='submit' id='send' name='send' value='Send' />\n";
  $output .= "<input type='button' id='pm' name='pm' value='GeekMail Transcript' onClick='javascript:geekMail();' />\n";
  $output .= "<input type='submit' id='full' name='full' value='View Entire Chat' onClick='javascript:entireChat();' />\n";
  $output .= "</form>\n";

  return $output;
}

function list_players($room_id,$show_online=false,$user=0) {
  $mysql = dbConnect();
  $sql = sprintf("select * from Chat_users where room_id=%s and user_id=%s",quote_smart($room_id),quote_smart($user));
  $result = mysqli_query($mysql, $sql);
  $user_result = mysqli_fetch_array($result);
  if ($user_result && $user_result['close'] != "" && $user_result['lock'] == 'On') {
    return "";
  }
  $sql = sprintf("select coalesce(alias,name) as name, color, max_post, remaining_post, if(last_view>(date_sub(now(), interval 3 second)),'(online)','') as available, Chat_users.lock, close from Chat_users, Users where Chat_users.user_id=Users.id and room_id=%s order by name asc",quote_smart($room_id),quote_smart($room_id));
  $result = mysqli_query($mysql, $sql);
  $output = "";
  while ( $player = mysqli_fetch_array($result) ) {
    if ($player['name'] == '<!-- invis -->' || ($player['lock'] != 'Off' && $player['close'] != '')) {
      continue;
    }
    $output .= "<span style='color:".$player['color'].";'>".$player['name']."</span> ";
	if ( $show_online ) {
	  $output .= $player['available'];
	}
	$output .= " <br />";
  }

  return $output;
}

function room_list($game_id,$user_id) {
  $sql = sprintf("select * from Chat_rooms, Chat_users where Chat_rooms.id=Chat_users.room_id and user_id=%s and game_id=%s order by name",quote_smart($user_id),quote_smart($game_id));
  $result = mysqli_query($mysql, $sql);
  $output = "";
  while ( $room = mysqli_fetch_array($result) ) {
    $output .= "<a href='javascript:change_room(\"".$room['id']."\")'>".$room['name']."</a><br />";
  }
  $output .= "<a href='javascript:close_div(\"room_nav\")'>[close]</a><br />";

  return $output;
}
?>
