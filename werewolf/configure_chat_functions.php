<?php

include_once("php/db.php");
$mysql = dbConnect();


function get_chat_room_info($room_id) {
  $sql = sprintf("select name, max_post, created from Chat_rooms where id=%s",quote_smart($room_id));
  $result = mysqli_query($mysql, $sql);
  $output['name'] = mysqli_result($result,0,0);
  $output['max_post'] = mysqli_result($result,0,1);
  $output['created'] = mysqli_result($result,0,2);
  $sql = sprintf("select user_id, color, max_post, alias, secret, open from Chat_users where room_id=%s",quote_smart($room_id));
  $result = mysqli_query($mysql, $sql);
  $output['player_list'] = "";
  while ( $row = mysqli_fetch_array($result) ) {
    if ( $output['plyaer_list'] != "" ) { $output['player_list'] .= ", "; }
    $id = sprintf("%0d",$row['user_id']);
	$output['player_list'] .= $id;
    $output['color_'.$id] = $row['color'];
    $output['max_'.$id] = $row['max_post'];
    $output['alias_'.$id] = $row['alias'];
    $output['secret_'.$id] = $row['secret'];
	$output['open_'.$id] = $row['open'];
  }
                                                                                
  return $output;
}

function change_dawn_reset($game_id) {
  $sql = sprintf("select dawn_chat_reset from Games where id=%s",quote_smart($game_id));
  $result = mysqli_query($mysql, $sql);
  $reset = mysqli_result($result,0,0);
  if ( $reset == "Yes" ) {
    $reset = "No";
  } else {
    $reset = "Yes";
  }
  $sql = sprintf("update Games set dawn_chat_reset=%s where id=%s",quote_smart($reset),quote_smart($game_id));
  $result = mysqli_query($mysql, $sql);
}

function lock_room($room_id) {
  $sql = sprintf("select `lock` from Chat_rooms where id=%s",quote_smart($room_id));
  $result = mysqli_query($mysql, $sql);
  $lock = mysqli_result($result,0,0);
  if ( $lock == "Off" ) {
    $lock = "On";
  } elseif ( $lock == "On") {
    $lock = "Secure";
  } else {
    $lock = "Off";
  }
  $sql = sprintf("update Chat_rooms set `lock`=%s where id=%s",quote_smart($lock),quote_smart($room_id));
  $result = mysqli_query($mysql, $sql);
                                                                                
  return $lock;
}

function lock_player($room_id,$user_id) {
  $sql = sprintf("select `lock` from Chat_users where room_id=%s and user_id=%s",quote_smart($room_id),quote_smart($user_id));
  $result = mysqli_query($mysql, $sql);
  $lock = mysqli_result($result,0,0);
  if ( $lock == "Off" ) {
    $lock = "On";
  } elseif ( $lock == "On" ) {
    $lock = "Secure";
  } else {
    $lock = "Off";
  }
  $sql = sprintf("update Chat_users set `lock`=%s where room_id=%s and user_id=%s",quote_smart($lock),quote_smart($room_id),quote_smart($user_id));
  $result = mysqli_query($mysql, $sql);
                                                                                
  return $lock;
}

function eye_player($room_id,$user_id) {
  $sql = sprintf("select open, close, now() from Chat_users where room_id=%s and user_id=%s",quote_smart($room_id),quote_smart($user_id));
  $result = mysqli_query($mysql, $sql);
  $open = mysqli_result($result,0,0);
  $close = mysqli_result($result,0,1);
  $now = mysqli_result($result,0,2);
  if ( $close == "" ) {
	$open = quote_smart($open);
    $close = quote_smart($now);
	$return = "Close";
  } else {
    $open = quote_smart($now);
	$close = 'NULL';
	$return = "Open";
  }
  $sql = sprintf("update Chat_users set open=%s, close=%s where room_id=%s and user_id=%s",$open,$close,quote_smart($room_id),quote_smart($user_id));
  $result = mysqli_query($mysql, $sql);
                                                                                
  return $return;
}


function reset_room($room_id) {
  $sql = sprintf("select max_post from Chat_rooms where id=%s",quote_smart($room_id));
  $result = mysqli_query($mysql, $sql);
  $max_post = mysqli_result($result,0,0);
  update_chat_room($room_id,'remaining_post',$max_post);
}

function reset_user($room_id,$user_id) {
  $sql = sprintf("select max_post from Chat_users where room_id=%s and user_id=%s",quote_smart($room_id),quote_smart($user_id));
  $result = mysqli_query($mysql, $sql);
  $max_post = mysqli_result($result,0,0);
  update_chat_user($room_id,$user_id,'remaining_post',$max_post);
}


function get_mod_names_by_ids($game_id) {
  $sql = sprintf("select user_id, name from Moderators, Users where Moderators.user_id=Users.id and game_id=%s order by name",quote_smart($game_id));
  $result = mysqli_query($mysql, $sql);
  while ( $row = mysqli_fetch_array($result) ) {
    $mod_names[$row['user_id']] = $row['name'];
  }
  return $mod_names;
}

function get_player_names_by_ids($game_id) {
  $sql = sprintf("select user_id, name from Players_all, Users where Players_all.user_id=Users.id and game_id=%s order by name",quote_smart($game_id));
  $result = mysqli_query($mysql, $sql);
  while ( $row = mysqli_fetch_array($result) ) {
    $player_names[$row['user_id']] = $row['name'];
  }
  return $player_names;
}

function get_all_names_by_ids($game_id) {
  $sql = sprintf("select user_id, name from Users_game_all, Users where Users_game_all.user_id=Users.id and game_id=%s",quote_smart($game_id));
  $result = mysqli_query($mysql, $sql);
  while ( $row = mysqli_fetch_array($result) ) {
    $player_names[$row['user_id']] = $row['name'];
  }
  return $player_names;
}

function get_all_aliases_by_ids($game_id) {
  $name = '';
  $sql_game = sprintf("select phys_by_alias from Games where id = %s", quote_smart($game_id));
  $result_game  = mysqli_query($mysql, $sql_game);
  if (mysqli_num_rows($result_game) > 0 && mysqli_result($result_game,0,0) == 'Yes')  
  {    
	$sql_alias = sprintf("select p.user_id, IFNULL(p.player_alias, u.name) name_to_use from Players_all p, Users u where game_id=%s and p.user_id=u.id ", 
      quote_smart($game_id));
	$result_alias  = mysqli_query($mysql, $sql_alias);
    while ( $row = mysqli_fetch_array($result_alias) ) {
      $player_names[$row['user_id']] = $row['name_to_use'];
    }	
	return $player_names;
  }
  return get_all_names_by_ids($game_id);
}

function get_player_alias($room_id, $user_id) {
  $sql = sprintf("select alias from Chat_users where room_id=%s and user_id=%s", quote_smart($room_id),quote_smart($user_id));
  $result = mysqli_query($mysql, $sql);
  $row = mysqli_fetch_array($result);

  return $row['alias'];
}

function get_player_secret($room_id, $user_id) {
  $sql = sprintf("select secret from Chat_users where room_id=%s and user_id=%s", quote_smart($room_id),quote_smart($user_id));
  $result = mysqli_query($mysql, $sql);
  $row = mysqli_fetch_array($result);

  return $row['secret'];
}

function insert_chat_user($room_id,$player_id,$color,$max_post) {
  if ( $max_post == "N/A" || $max_post == "" ) { 
    $max_post = "NULL";
  } else {
    $max_post = quote_smart($max_post);
  }
  $sql = sprintf("insert into Chat_users (room_id, user_id, last_view, color, max_post, remaining_post, open) values ( %s, %s, now(), %s, %s, %s, now() )",quote_smart($room_id),quote_smart($player_id),quote_smart($color),$max_post,$max_post);
  $result = mysqli_query($mysql, $sql);
}

function update_chat_user($room_id,$player_id,$field,$value) {
  if ( $value == "N/A" ) {
    $value = "NULL";
  } else {
    $value = quote_smart($value);
  }
  $sql = sprintf("update Chat_users set %s=%s where user_id=%s and room_id=%s",$field,$value,quote_smart($player_id),quote_smart($room_id));
  $result = mysqli_query($mysql, $sql);
}


function delete_chat_user($room_id,$player_id) {
  $sql = sprintf("delete from Chat_users where user_id=%s and room_id=%s",quote_smart($player_id),quote_smart($room_id));
  $result = mysqli_query($mysql, $sql);
}

function insert_chat_room($game_id,$room_name,$max_post) {
  if ($max_post == "N/A" || $max_post == "") {
    $max_post = "NULL";
  } else {
    $max_post = quote_smart($max_post);
  }
  $sql = sprintf("insert into Chat_rooms (id, game_id, name, max_post, remaining_post, created) values ( null, %s, %s ,%s, %s, now())",quote_smart($game_id),quote_smart($room_name),$max_post,$max_post);
    $result = mysqli_query($mysql, $sql);
    return  mysqli_insert_id();
}

function update_chat_room($room_id,$field,$value) {
  if ( $value == "N/A" ) {
    $value = "NULL";
  } else {
    $value = quote_smart($value);
  }
  $sql = sprintf("update Chat_rooms set %s=%s where id=%s",$field,$value,quote_smart($room_id));
  $result = mysqli_query($mysql, $sql);
}

function delete_chat_room($room_id) {
  $sql = sprintf("delete from Chat_rooms where id=%s",quote_smart($room_id));
  $result = mysqli_query($mysql, $sql);
}

function set_player_modchat($game_id,$uid,$chat_id)
{
  if ($chat_id == "" || $chat_id == "n/a") { $chat_id = "NULL"; }

  $update_player_sql = sprintf("update Players set modchat_id=%s where game_id=%s and user_id=%s", quote_smart($chat_id), quote_smart($game_id), quote_smart($uid));
  mysqli_query($update_player_sql);  
}


function submit_all($game_id,$chat_name = "Mod chat") {
  global $_POST;
  $mod_names = get_mod_names_by_ids($game_id);
  $mods = array_keys($mod_names);
  $player_names = get_player_names_by_ids($game_id);
  $players = array_keys($player_names);
  $play_id = sprintf("%0d",$players[0]);  
  $color = ( $_POST['color_'.$play_id] == "" ) ? "#000000" : $_POST['color_'.$play_id];
  $defcol = $_POST['defcol_'.$play_id];
  $max_post = $_POST['player_max_'.$play_id];
  foreach ( $player_names as $player => $name ) {
    $room_id = insert_chat_room($game_id,"$chat_name - ".$name,$_POST['room_max']);
    set_player_modchat($game_id,$player,$room_id);
    insert_chat_user($room_id,$player,get_profile_color($player,$color,$defcol),$max_post);
    foreach ( $mods as $mod ) {
      $mod_id = sprintf("%0d",$mod);
      insert_chat_user($room_id,$mod,get_profile_color($mod_id,$_POST['color_'.$mod_id],$_POST['defcol_'.$mod_id]),"N/A");
    }
  }
}

function submit_comb($game_id) {
  global $_POST;
  $player_names = get_player_names_by_ids($game_id);
  $players = array_keys($player_names);
  $play_id = sprintf("%0d",$players[0]);
  $color1 = ( $_POST['color_'.$play_id] == "" ) ? "#000000" : $_POST['color_'.$play_id];
  $defcol1 = $_POST['defcol_'.$play_id];
  $max_post1 = $_POST['player_max_'.$play_id];
  $play_id = sprintf("%0d",$players[1]);
  $color2 = ( $_POST['color_'.$play_id] == "" ) ? "#000000" : $_POST['color_'.$play_id];  
  $defcol2 = $_POST['defcol_'.$play_id];
  $max_post2 = $_POST['player_max_'.$play_id];
  $mod_names = get_mod_names_by_ids($game_id);
  $mods = array_keys($mod_names);
  while ( count($players) > 1 ) {
    $player = array_shift($players);
    for ( $i=0; $i<count($players); $i++ ) {
      $room_name = "Player chat - ".$player_names[$player].", ".$player_names[$players[$i]]; 
      $room_id = insert_chat_room($game_id,$room_name,$_POST['room_max']);
      insert_chat_user($room_id,$player,get_profile_color($player,$color1,$defcol1),$max_post1);
      insert_chat_user($room_id,$players[$i],get_profile_color($players[$i],$color2,$defcol2),$max_post2);
      foreach ( $mods as $mod ) {
	    $mod_id = sprintf("%0d",$mod);
        insert_chat_user($room_id,$mod,get_profile_color($mod_id,$_POST['color_'.$mod_id],$_POST['defcol_'.$mod_id]),"N/A");
      }
    }
  }
}

function get_profile_color($user_id, $color, $defcol) {
  if ($defcol == "on") {
    $color = "#000000";
    $sql_color = sprintf("select chat_color from Bio where user_id=%s",quote_smart($user_id));
    $result_color = mysqli_query($mysql, $sql_color);
    if (mysqli_num_rows($result_color)) {$color = mysqli_result($result_color, 0); }
  }
  return $color;
}

function submit_new_chat($game_id) {
  global $_POST;
  if ( $_POST['chat_name'] == "" ) { $_POST['chat_name'] = "Unnamed Chat Room"; }
  print $_POST['room_max']."<br />";
  $room_id = insert_chat_room($game_id,$_POST['chat_name'],$_POST['room_max']);
  $sql_user = sprintf("select user_id from Users_game_all where game_id=%s",quote_smart($game_id));
  $result_user = mysqli_query($mysql, $sql_user);

  while ( $user = mysqli_fetch_array($result_user) ) {
    $id = $user['user_id'];

    if ( $_POST['player_'.$id] == "on" ) {
      $color = get_profile_color($id, ( $_POST['color_'.$id] == "" ) ? "#000000" : $_POST['color_'.$id], $_POST['defcol_'.$id]);
	  $max_post = $_POST['player_max_'.$id];
	  if ( $max_post == "" ) { $max_post = "N/A"; }
      insert_chat_user($room_id,$id,$color,$max_post);

		$alias = $_POST['player_alias_'.$id];
	  if($alias != "")
	  {
	  	update_chat_user($room_id,$id,'alias',$alias);
	  }	

		$secret = $_POST['player_secret_'.$id];
	  if($secret == "on")
	  {
	  	update_chat_user($room_id,$id,'secret',$secret);
	  }	
    }
  }

}


function submit_edit_chat($game_id){
  global $_POST;
  $room_id = $_POST['room_id'];
  $old_data = get_chat_room_info($room_id);
  if ( $old_data['name'] != $_POST['chat_name'] ) {
    update_chat_room($room_id,'name',$_POST['chat_name']);
  }
  if ( $old_data['max_post'] != $_POST['room_max'] ) {
    if ( $_POST['room_max'] == "" ) { $_POST['room_max'] = "N/A"; }
    update_chat_room($room_id,'max_post',$_POST['room_max']);
  }
  $all_names = get_all_names_by_ids($game_id);
  foreach ( $all_names as $id => $name ) {
    if ( $_POST['player_'.$id] == "on" ) {
      $color = get_profile_color($id, ( $_POST['color_'.$id] == "" ) ? "#000000" : $_POST['color_'.$id], $_POST['defcol_'.$id]);
	  $max_post = $_POST['player_max_'.$id];
	  $alias = $_POST['player_alias_'.$id];
	  $secret = $_POST['player_secret_'.$id];
	  $open = $_POST['player_open_'.$id];
	  if ($max_post == "" ) { $max_post = "N/A"; }
      if ( !isset($old_data['color_'.$id]) ) {
        insert_chat_user($room_id,$id,$color,$max_post);
      } elseif ( $old_data['color_'.$id] != $color ) {
        update_chat_user($room_id,$id,'color',$color);
      }
	  if ( $old_data['max_'.$id] != $max_post ) {
	    update_chat_user($room_id,$id,'max_post',$max_post);
	  }
	  if ( $old_data['alias_'.$id] != $alias ) {
	    update_chat_user($room_id,$id,'alias',$alias);
	  }
	  if ( $old_data['secret_'.$id] != $secret ) {
	    update_chat_user($room_id,$id,'secret',$secret);
	  }
	  if ( $old_data['open_'.$id] != $open ) {
        update_chat_user($room_id,$id,'open',$open);
	  }
    } else {
      if ( isset($old_data['color_'.$id]) ) {
        delete_chat_user($room_id,$id);
      }
    }
  }
}

# Creates the Table to manage all the chat rooms
function list_chat_rooms($game_id) {
  $sql = sprintf("select * from Chat_rooms where game_id=%s order by name",quote_smart($game_id));
  $result =  mysqli_query($mysql, $sql);
  $num_chat_rooms = mysqli_num_rows($result);
  $output .= "<form id='all_rooms' name='all_rooms'>\n";
  $output .= "<table class='forum_table'><tr><th><a href='javascript:add_room_dialog()'><img src='/images/add.png'  border='0' /></a></th><th colspan='2'>Current Chat Rooms ($num_chat_rooms)</th></tr>\n";

  while ( $room = mysqli_fetch_array($result) ) {
    $output .= "<tr>";
    $output .= room_info($room['id']);
	$output .= player_info($game_id,$room['id']);
	$output .= "</tr>\n";
  }
  $output .= "</table>\n";
  $output .= "<input type='button' name='delete' value='Delete Selected Rooms' onClick='delete_selected()' >";
  $output .= "</form>\n";

  return $output;
}

function room_info($room_id,$page='config') {
  if ( $room_id == 0 ) { return "No Room Selected"; }
  $sql_room = sprintf("select * from Chat_rooms where id=%s",quote_smart($room_id));
  $result_room = mysqli_query($mysql, $sql_room);
  $room = mysqli_fetch_array($result_room);
  $sql_messages = "select count(*) from Chat_messages where room_id=".$room['id'];
  $result_messages = mysqli_query($mysql, $sql_messages);
  $room['message_count'] = mysqli_result($result_messages,0,0);
  if ( $page == "config" ) {
    $output .= "<td valign='top'>";
  }
  $output .= "<a href='javascript:edit_room_dialog(\"".$room['id']."\")'><img src='/images/edit.png' border='0' /></a>";
  if ( $page == "config" ) {$output .= "<br />";}
  if ( $room['lock'] == "On" ) {
    $output .= "<a href='javascript:lock_room(\"".$room['id']."\")'><img id='lock_img_".$room['id']."' src='/images/lock_green.gif' border='0' /></a>";
  } elseif ( $room['lock'] == "Secure" ) {
    $output .= "<a href='javascript:lock_room(\"".$room['id']."\")'><img id='lock_img_".$room['id']."' src='/images/lock_red.gif' border='0' /></a>";
  } else {
    $output .= "<a href='javascript:lock_room(\"".$room['id']."\")'><img id='lock_img_".$room['id']."' src='/images/unlock.gif' border='0' /></a>";
  }
  $output .= "<br />";
  if ( $page == "config") {
    $output .= "<input type='checkbox' name='selected_".$room['id']."' />\n";
    $output .= "</td>";
    $output .= "<td valign='top'>";
  }
  $output .= "<b>".$room['name']."</b><br />(".$room['message_count']." messages)";
  $output .= "<br />Created: ".$room['created'];
  if ( $room['max_post'] == "" ) {
    $output .= "<br />RPL: N/A";
  } else {
    $output .= "<br />RPL: ".$room['max_post']."<br />Post Remaining: ".$room['remaining_post']."<br /><input type='button' onClick='reset_room(\"".$room['id']."\")' name='reset' value='Reset Post Count' />";
  }
  if ( $page == "config" ) {
    $output .= "</td>";
  }

  return $output;
}

function player_info($game_id,$room_id) {
  $sql_users = sprintf("select id, name, color, `lock`, `type`, max_post, remaining_post, open, close from Chat_users, Users, Users_game_all where Chat_users.user_id=Users.id and Chat_users.user_id=Users_game_all.user_id and Users.id=Users_game_all.user_id and game_id =%s and room_id=%s order by type, name",quote_smart($game_id),quote_smart($room_id));
  $result_users = mysqli_query($mysql, $sql_users);
  $output .= "<td>";
  $output .= "<table border='0'>";
  while ( $user = mysqli_fetch_array($result_users) ) {
    $output .= "<tr>";
    $output .= "<td>";
    if ( $user['type'] != "moderator" ) {
      if ( $user['lock'] == "On" ) {
        $output .= "<a href='javascript:lock_player(\"".$room_id."\",\"".$user['id']."\")'><img id='lock_img_".$room_id."_".$user['id']."' src='/images/lock_green.gif' border='0' /></a>";
      } elseif ( $user['lock'] == "Secure" ) {
        $output .= "<a href='javascript:lock_player(\"".$room_id."\",\"".$user['id']."\")'><img id='lock_img_".$room_id."_".$user['id']."' src='/images/lock_red.gif' border='0' /></a>";
  	  } else {
        $output .= "<a href='javascript:lock_player(\"".$room_id."\",\"".$user['id']."\")'><img id='lock_img_".$room_id."_".$user['id']."' src='/images/unlock.gif' border='0' /></a>";
      }
	  $output .= "</td><td>";
	  if ( $user['close'] == "" ) {
        $output .= "<a href='javascript:eye_player(\"".$room_id."\",\"".$user['id']."\")'><img id='eye_img_".$room_id."_".$user['id']."' src='/images/open_eye.png' border='0' /></a>";
	  } else {
        $output .= "<a href='javascript:eye_player(\"".$room_id."\",\"".$user['id']."\")'><img id='eye_img_".$room_id."_".$user['id']."' src='/images/close_eye.png' border='0' /></a>";
	  }
    } else {
	  $output .= "</td><td>";
	}
	$output .= "</td>";
	$output .= "<td>";
    $output .= "<span style='color:".$user['color'].";'>".$user['name']."</span>";
	if ( $user['type'] != "moderator" ) {
	  if ( $user['close'] == "" ) {
        $output .= " View post after: ".$user['open'];
	  } else {
        $output .= " View post between: ".$user['open']." and ".$user['close'];
	  }
	  $output .= " <a href='javascript:edit_eye_dialog(\"".$room_id."\",\"".$user['id']."\")'>[edit]</a>";
	  $output .= "</td>";
	  if ( $user['max_post'] == "" ) {
	  } else {
	    $output .= "</tr><td></td><td></td><td>&nbsp;&nbsp;&nbsp;";
        $output .= " (PPL: ".$user['max_post'].", Remaining: ".$user['remaining_post'].") <input type='button' onClick='reset_player(\"".$room_id."\",\"".$user_id."\")' name='reset' value='Reset Player Post Count' />";
	    $output .= "</td>";
	  }
	} else {
	  $output .= "</td>";
	}
	$output .= "</tr>";
  }
  $output .= "</table>";
  $output .= "</td>";

  return $output;
}

function display_add_dialog($game_id) {
  global $_SERVER, $uid;
  $output = "<form name='add_chat_form' method='post' action='".$_SERVER['PHP_SELF']."' onSubmit='return check_template()' >";
  $output .= "<table class='forum_table'>\n";
  $output .= "<input type='hidden' name='game_id' value='$game_id' />\n";
  $output .= "<tr><th colspan='2'>Add a Chat Room for this Game</th></tr>\n";
  $output .= "<tr><td><b>Chat Room Name:</b></td><td><input type='text' name='chat_name' size='30' /></td></tr>\n";
  $output .= "<tr><td><b>Room Post Limit (RPL):</b></td><td><input type='text' name='room_max' value='N/A' size='3' /></td></tr>\n";
  $output .= "<tr><td valign='top' colspan='2'>";
  $output .= "<table border='0' cellpadding='0' cellspacing='1' >\n";
  $output .= "<tr><th align='left'>Permited Players:</th><th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Color&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th><th>Def</th><th>Alias</th><th>Sec</th><th></th><th>PPL</th></tr>";
  $mod_names = get_mod_names_by_ids($game_id);
  foreach ( $mod_names as $mod_id => $mod_name ) {
    $mod_id = sprintf("%0d",$mod_id);
	$output .= "<tr><td><input type='hidden' name='player_$mod_id' value='on' />$mod_name </td>";
	$output .= "<td><input type='text' size='8' name='color_$mod_id' value='#000000' disabled='disabled' /><a href='#' onClick='cp.select(document.add_chat_form.color_$mod_id,\"pick\"); return false;' name='pick' id='pick'><img src='/images/color_pick.gif' border='0' /></a></td><td><input type='checkbox' checked='checked' name='defcol_$mod_id' onClick='document.add_chat_form.color_$mod_id.disabled = !document.add_chat_form.color_$mod_id.disabled;' /></td><td> </td>";
	$output .= "</tr>\n";
  }
  $player_names = get_player_names_by_ids($game_id);
  foreach ( $player_names as $p_id => $p_name ) {
    $p_id = sprintf("%0d",$p_id);
	$output .= "<tr><td><input type='checkbox' name='player_$p_id' />$p_name</td>";
	$output .= "<td><input type='text' size='8' name='color_$p_id' value='#000000' disabled='disabled' /><a href='#' onClick='cp.select(document.add_chat_form.color_$p_id,\"pick\"); check_this_box($p_id); return false;' name='pick' id='pick'><img src='/images/color_pick.gif' border='0' /></a></td>";
    $output .= "<td><input type='checkbox' name='defcol_$p_id' checked='checked' onClick='document.add_chat_form.color_$p_id.disabled = !document.add_chat_form.color_$p_id.disabled;' /></td>";
	$output .= "<td><input type='text' id='player_alias_$p_id' name='player_alias_$p_id' value='' size='10' onClick='document.add_chat_form.color_$p_id.disabled = false; document.add_chat_form.defcol_$p_id.checked = false;' /></td>";
	$output .= "<td><input type='checkbox' id='player_secret_$p_id' name='player_secret_$p_id' /></td><td></td>";
	$output .= "<td><input type='text' id='player_max_$p_id' name='player_max_$p_id' value='N/A' size='3' /></td>";
	$output .= "</tr>\n";
  }
  $output .= "</table>\n";
  $output .= "</td></tr>\n";
  $output .= "<tr><td colspan='2' align='center'><input type='submit' name='submit_newchat' value='Submit'></td></tr>\n";
  $output .= "<tr><td colspan='2' align='center'><input type='submit' name='submit_all' value='Make Mod to Player Chat for Each Player' onClick='template_notice(\"$uid\")' /></td></tr>\n";
  $output .= "<tr><td colspan='2' align='center'><input type='submit' name='submit_phys' value='Make Physics Chat for Each Player' onClick='template_notice(\"$uid\")' /></td></tr>\n";
  $output .= "<tr><td colspan='2' align='center'><input type='submit' name='submit_comb' value='Make Chat Rooms for All Pairs of Players' onClick='template_notice(\"$uid\")' /></td></tr>\n";
  $output .= "</table>\n";
  $output .= "</form>\n";
  
  return $output;
} 

function display_edit_dialog($game_id, $room_id) {
  global $_SERVER;
  $output = "<form id='edit_form' name='edit_form' method='post' action='".$_SERVER['PHP_SELF']."'>\n";
  $output .= "<table id='mytable' class='forum_table' width='100%'>\n";
  $output .= "<input type='hidden' id='room_id' name='room_id' value='$room_id' />\n";
  $output .= "<input type='hidden' id='game_id' name='game_id' value='$game_id' />\n";
  $output .= "<tr>\n\t<th name='edit_head' colspan='2'>Edit Chat Room</th>\n</tr>\n";
  $room = get_chat_room_info($room_id);
  $output .= "<tr>\n\t<td><b>Chat Room Name:</b></td>\n\t<td><input type='text' id='chat_name' name='chat_name' size='30' value=\"".$room['name']."\" /></td>\n</tr>\n";
  $output .= "<input type='hidden' name='created' value='".$room['created']."' />\n";
  if ( $room['max_post'] == "" ) { $room['max_post'] = "N/A"; }
  $output .= "<tr><td><b>Room Post Limits (RPL):</b></td><td><input type='text' name='room_max' size='3' value='".$room['max_post']."' /></td></tr>\n";
  $output .= "<tr><td colspan='2' valign='top'>";
  $output .= "<table>\n";
  $output .= "<tr><th align='left'>Permited Players:</th><th>Color</th><th>Def</th><th>View Post After</th><th>Alias</th><th>Sec</th><th></th><th>PPL</th></tr>\n";
  $mod_names = get_mod_names_by_ids($game_id);
  foreach ( $mod_names as $id => $name) {
    $id = sprintf("%0d",$id);
	$color = $room['color_'.$id];
	$output .= "\t<tr>\n\t\t<td><input type='hidden' id='player_$id' name='player_$id' value='on' />$name</td>\n";
	$output .= "\t\t<td><input type='text' size='8' id='color_$id' name='color_$id' value='$color' /> <a href='#' onClick='cp.select(document.edit_form.color_$id,\"pick\");return false;' name='pick' id='pick'><img src='/images/color_pick.gif' border='0' /></a></td><td><input type='checkbox' name='defcol_$id' onClick='document.edit_form.color_$id.disabled = !document.edit_form.color_$id.disabled;' /></td><td> </td>\n";
	$output .= "\t</tr>\n";
  }
  $player_names = get_player_names_by_ids($game_id);
  foreach ($player_names as $id => $name ) {
  	$alias = get_player_alias($room_id,$id); 
  	$secret = get_player_secret($room_id,$id); 
    $id = sprintf("%0d",$id);
	$color = $room['color_'.$id];
	$max_post = $room['max_'.$id];
	if ( $max_post == "" ) { $max_post = "N/A"; }
	$cb_value = "off";
	$cb_checked = "";
	if ( $color != "" ) { 
	  $cb_value = "on"; 
	  $cb_checked = "checked='checked'";
	}
	$cb_secret = "";
	if ( $secret == "on" ) { 
	  $cb_secret = "checked='checked'";
	}
	$open = $room['open_'.$id];
	$o_value = $open;
	if ( $open == "" ) { $o_value = $room['created']; }

	$output .= "\t<tr>\n\t\t<td><input type='checkbox' id='player_$id' name='player_$id' onClick='javascript:warn_delete(this,\"$id\",\"$o_value\");' $cb_checked />$name</td>\n";
	$output .= "\t\t<td><input type='text' size='8' id='color_$id' name='color_$id' value='$color' /> <a href='#' onClick='cp.select(document.edit_form.color_$id,\"pick\");return false;' name='pick' id='pick'><img src='/images/color_pick.gif' border='0' /></a></td>";
    $output .= "<td><input type='checkbox' name='defcol_$id' onClick='document.edit_form.color_$id.disabled = !document.edit_form.color_$id.disabled;' /></td>";
	$output .= "<td><input type='text' name='player_open_$id' id='player_open_$id' value='$open' size='18' /></td>";
	$output .= "<td><input type='text' name='player_alias_$id' value='$alias' size='10' onClick='document.edit_form.color_$id.disabled = false; document.edit_form.defcol_$id.checked = false;'  />";
	$output .= "<td><input type='checkbox' id='player_secret_$id' name='player_secret_$id' $cb_secret /></td><td></td>\n";
	$output .= "<td><input type='text' name='player_max_$id' value='$max_post' size='2' />";
	$output .= "</tr>\n";
  }
  $output .= "\t</table></td>\n";
  $output .= "</tr>\n";
  $output .= "<tr>\n\t<td colspan='2' align='center'><input type='submit' name='submit_editchat' value='Submit' /> <input type='submit' name='delete_chat' value='Delete' onClick='warn_delete_submit(this)' /></td>\n</tr>\n";
  $output .= "</table>\n";
  $output .= "</form>\n";

  return $output;
}

function display_eye_dialog($game_id,$room_id,$user_id) {
  $sql = sprintf("select name, open, close from Chat_users, Users where Chat_users.user_id=Users.id and room_id=%s and user_id=%s",quote_smart($room_id),quote_smart($user_id));
  $result = mysqli_query($mysql, $sql);
  $user = mysqli_fetch_array($result);
  $output = "<form id='edit_eye' name='edit_eye' method='post' action='".$_SERVER['PHP_SELF']."'>\n";
  $output .= "<table class='forum_table'>\n";
  $output .= "<tr><th colspan='2'>Edit Viewing Times for ".$user['name']."</th></tr>\n";
  $output .= "<tr>";
  $output .= "<td>Open:</td>";
  $output .= "<td><input type='text' name='open' value='".$user['open']."' /></td>";
  $output .= "</tr>\n";
  if ( $user['close'] != "" ) {
    $output .= "<tr>";
    $output .= "<td>Close:</td>";
    $output .= "<td><input type='text' name='close' value='".$user['close']."' /></td>";
    $output .= "</tr>\n";
  }
  $output .= "<tr><td align='center' colspan='2'>";
  $output .= "<input type='submit' name='eye_submit' value='Submit' />";
  $output .= "</td></tr>\n";
  $output .= "</table>";
  $output .= "<input type='hidden' name='game_id' value='$game_id' />";
  $output .= "<input type='hidden' name='room_id' value='$room_id' />";
  $output .= "<input type='hidden' name='user_id' value='$user_id' />";
  $output .= "</form>";

  return $output;
}
?>
