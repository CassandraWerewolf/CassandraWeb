<?php
include_once "php/db.php";
include_once "configure_chat_functions.php";

#dbConnect();

# Global Variable with the list of the tabs
$ptab_list[1] = "Admin";
$ptab_list[2] = "Locations";
$ptab_list[3] = "Exits";
$ptab_list[4] = "Players";
$ptab_list[5] = "Items";
$ptab_list[6] = "Templates";

$reverse_tab["Admin"] = 1;
$reverse_tab["Locations"] = 2;
$reverse_tab["Exits"] = 3;
$reverse_tab["Players"] = 4;
$reverse_tab["Items"] = 5;
$reverse_tab["Templates"] = 6;

# Displays the tab navigation.
function display_physics_tabs($active) {
  global $ptab_list ;
  $output = "";
  foreach ( $ptab_list as $tab_num => $tab_name ) {
    #if ( !$mod && $tab_num == 4 ) { continue; }
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

function show_physics_tab($active,$user_id,$game_id) {
  $output = "";
  $output .= "
    <table border = '0'>
    <tr><td valign='top' width=60%>
    <div id = 'list_div'>";
  switch ($active) {
  case 1:
    $output .= list_phys_settings($game_id, $user_id);
    break;
  case 6:
    # Item Templates
   $output .= list_item_temps($game_id);
    break;  
  case 5:
    # Items
   $output .= list_items($game_id);
    break;
  case 3:
    # Exits
    $output .= list_exits($game_id);
    break;
  case 2:
    # Locations
    $output .= list_locs($game_id);
    break;
  case 4:
    # Players
    $output .= list_players_disp($game_id);
    break;
  }
  $output .= "
    </div>
    </td>
    <td valign='top'>
    <div id='dialog_div'>";
  switch ($active) {
  case 1:
    # Processing (Main)
    $output .= list_phys_processing($game_id);
    break;
  case 5:
    # Items
   $output .= display_add_items($game_id);
    break;
  case 6:
    # Item Templates
   $output .= display_add_item_temps($game_id);
    break;    
  case 3:
    # Exits
    $output .= display_add_exits($game_id);
    break;
  case 2:
    # Locations
    $output .= display_add_locs($game_id);
    break;
  case 4:
    # Players
    $output .= display_add_players($game_id);
    break;
  }
  
  return $output;
}


function lock_loc($loc_id) {
  $sql = sprintf("select `lock` from Locations where id=%s",quote_smart($loc_id));
  $result = mysql_query($sql);
  $lock = mysql_result($result,0,0);
  if ( $lock == "Off" ) {
    $lock = "On";
  } elseif ( $lock == "On") {
    $lock = "Secure";
  } else {
    $lock = "Off";
  }
  $sql = sprintf("update Locations  set `lock`=%s where id=%s",quote_smart($lock),quote_smart($loc_id));
  $result = mysql_query($sql);
                                                                                
  return $lock;
}

function opt_string($str) {
  if ($str == "N/A" || $str == "") {
    $str = "NULL";
  } else {
    $str = quote_smart($str);
  }
  return $str;
}


function insert_loc($game_id, $loc_name, $loc_desc, $loc_comment, $sub_id, $room_id, $visibility) {
  $sql = sprintf("insert into Locations (id, game_id, name, description, comment, subgame_id, room_id, visibility, created) values ( null, %s, %s ,%s, %s, %s, %s, %s, now())",quote_smart($game_id),quote_smart($loc_name),opt_string($loc_desc),opt_string($loc_comment),opt_string($sub_id),opt_string($room_id),quote_smart($visibility));
    $result = mysql_query($sql);
    return  mysql_insert_id();  
}

function insert_item($game_id, $temp_id, $name, $desc, $visibility, $mobility, $type, $owner, $room_id, $room_alias, $room_color) {
  $sql = sprintf("insert into Items (id, template_id, game_id, name, description, visibility, mobility, owner_type, room_id, room_alias, room_color, created) values (null, %s, %s, %s, %s, %s, %s, 'loc', %s, %s, %s, now())", quote_smart($temp_id), quote_smart($game_id),quote_smart($name),opt_string($desc),quote_smart($visibility), quote_smart($mobility),opt_string($room_id), opt_string($room_alias), opt_string($room_color));  
  $result = mysql_query($sql);
  $val =  mysql_insert_id();    
  if ($type == 'loc') { give_item_to_loc($game_id, $owner, $val); }
  else { give_item_to_player($game_id, $owner, $val); }
  return $val;
}

function insert_item_temp($game_id, $name, $desc, $visibility, $mobility, $room_id, $room_alias, $room_color) {
  $sql = sprintf("insert into Item_templates (id, game_id, name, description, visibility, mobility, room_id, room_alias, room_color) values (null, %s, %s, %s, %s, %s, %s, %s, %s)", quote_smart($game_id),quote_smart($name),opt_string($desc),quote_smart($visibility), quote_smart($mobility), opt_string($room_id), opt_string($room_alias), opt_string($room_color));  
  $result = mysql_query($sql);
  return  mysql_insert_id();    
}

function insert_exit($game_id, $exit_name, $exit_travel_text, $exit_comment, $temp_id) {
  $sql = sprintf("insert into Exits (id, game_id, name, travel_text, comment, template_id, created) values ( null, %s, %s ,%s, %s, %s, now())",quote_smart($game_id),quote_smart($exit_name),opt_string($exit_travel_text),opt_string($exit_comment),opt_string($temp_id));
    $result = mysql_query($sql);
    return  mysql_insert_id();  
}

function insert_exit_loc_map($exit_id, $loc_from, $loc_to) {
  $sql = sprintf("insert into Loc_exits (exit_id, loc_from_id, loc_to_id) values (%s, %s, %s)", quote_smart($exit_id), quote_smart($loc_from), quote_smart($loc_to));
  $result = mysql_query($sql);
  return mysql_insert_id();
}

function delete_exit_loc_map($exit_id, $loc_from) {
  $sql = sprintf("delete from Loc_exits where exit_id=%s and loc_from_id=%s", $exit_id, $loc_from);
  $result = mysql_query($sql);
}

function conditional_update($id, $table, $field, $oldval, $newval) {
  if ( $oldval != $newval ) {
    $sql = sprintf("update %s set %s=%s where id=%s", $table, $field, opt_string($newval), quote_smart($id));
    return mysql_query($sql);
  }
}

function item_update($id, $field, $newval) {
  $sql = sprintf("update Items set %s=%s where template_id=%s", $field, opt_string($newval), quote_smart($id));
  return mysql_query($sql);  
}

function delete_loc($loc_id) {
  $sql = sprintf("delete from Loc_exits where loc_from_id=%s", $loc_id);
  $result = mysql_query($sql);
  $sql = sprintf("delete from Loc_exits where loc_to_id=%s", $loc_id);
  $result = mysql_query($sql);  
  $sql = sprintf("delete from Locations where id=%s",quote_smart($loc_id));
  $result = mysql_query($sql);
}

function delete_exit($exit_id) {
  $sql = sprintf("delete from Loc_exits where exit_id=%s", $exit_id);
  $result = mysql_query($sql);
  $sql = sprintf("delete from Exits where id=%s",quote_smart($exit_id));
  $result = mysql_query($sql);
}

function delete_item($item_id) {
  $sql = sprintf("delete from Items where id=%s",quote_smart($item_id));
  $result = mysql_query($sql);
}

function delete_item_temp($temp_id) {
  $sql = sprintf("delete from Items where template_id=%s",quote_smart($temp_id));
  $result = mysql_query($sql);
  $sql = sprintf("update Exits set template_id=null where template_id=%s", quote_smart($temp_id));
  $result = mysql_query($sql);
  $sql = sprintf("delete from Item_templates where id=%s",quote_smart($temp_id));
  $result = mysql_query($sql);  
}

function create_room_for_loc($game_id,$name) {
  $mod_names = get_mod_names_by_ids($game_id);
  $mods = array_keys($mod_names);
  $room_id = insert_chat_room($game_id,$name,"");
  foreach ( $mods as $mod ) {
    $mod_id = sprintf("%0d",$mod);
    insert_chat_user($room_id,$mod,get_profile_color($mod_id,'#000000','on'),"N/A");
  }
  return $room_id;
}

function process_item_order_ex($game_id, $order) {
  $sql = sprintf("select phys_item_limit from Games where id = %s",quote_smart($game_id));
  $result = mysql_query($sql);
  if (mysql_num_rows($result) > 0) {
    $game_item_limit = mysql_result($result,0,0);
  }
  $p_loc = get_player_loc_info($game_id);
  process_item_order($game_id, $p_loc, $order, $game_item_limit);
}

function process_item_order($game_id, $p_loc, $order, $game_item_limit) {
    $item = get_item_info($order['item_id']);  
    if ($item['mobility'] == 'fixed') { return; }     	
    if ($order['target_type'] == 'user' 
          && $p_loc[intval($order['target_id'])]['death_phase'] == '') {

      $sql_limit = sprintf("select (select count(1) from Items where owner_type='user' and owner_ref_id=Players.user_id and game_id=Players.game_id) items, phys_item_limit from Players where game_id=%s and user_id=%s",quote_smart($game_id),quote_smart($order['target_id']));
      $result_limit = mysql_query($sql_limit);
      $player_items = mysql_result($result_limit,0,0);
      if ($player_items == "") {
        $player_items = 0;
      }
      $player_limit = mysql_result($result_limit,0,1);
      
      # If player limit is set, check that. If not and game limit is set, check that
      $can_hold = ($player_limit != "" ? ($player_items < $player_limit)  
           : ($game_item_limit == "" || $player_items < $game_item_limit));
            
      if ($item['owner_type'] == 'loc' 
            && $p_loc[intval($order['target_id'])]['loc_id'] == $item['owner_ref_id']) {
        if ($can_hold) {
          give_item_to_player($game_id, intval($order['target_id']), $order['item_id']);            
        } else {
          sys_message_to_modchat($game_id, intval($order['target_id']),"You cannot hold any more items.");
        }        
      } elseif ($item['owner_type'] == 'user' 
            && intval($item['owner_ref_id']) == intval($order['user_id'])
            && ($item['mobility'] == 'nonphys' 
              || ($p_loc[intval($order['target_id'])]['loc_id'] 
                    == $p_loc[intval($order['user_id'])]['loc_id']) ) ) {
        if ($can_hold) {
          give_item_to_player($game_id, intval($order['target_id']), $order['item_id']);            
        } else {
          sys_message_to_modchat($game_id,$order['user_id'],"You were unable to pass an item.");
        }                                    
      }                                                         
    } elseif ($order['target_type'] == 'loc' 
          && $item['owner_type'] == 'user' 
          && intval($item['owner_ref_id']) == intval($order['user_id'])) {
        give_item_to_loc($game_id, $p_loc[intval($order['user_id'])]['loc_id'], $order['item_id']);
    } 
}

function process_items($game_id) {
  $sql = sprintf("select phys_item_limit from Games where id = %s",quote_smart($game_id));
  $result = mysql_query($sql);
  if (mysql_num_rows($result) > 0) {
    $game_item_limit = mysql_result($result,0,0);
  }

  $sql = sprintf("select * from Item_orders where game_id = %s and status='active' order by last_updated ASC",quote_smart($game_id));
  $result = mysql_query($sql);
  $p_loc = get_player_loc_info($game_id);
  while ($order = mysql_fetch_array($result)) {
    conditional_update($order['id'], "Item_orders", "status", "active", "processed");
	process_item_order($game_id, $p_loc, $order, $game_item_limit);     
  }  
}

function process_movement_order_ex($game_id, $order) {
  $sql = sprintf("select phys_move_limit from Games where id = %s",quote_smart($game_id));
  $result = mysql_query($sql);
  if (mysql_num_rows($result) > 0) {
    $game_move_limit = mysql_result($result,0,0);
  }
  process_movement_order($game_id, $order, $game_move_limit);
}

function process_movement_order($game_id, $order, $game_move_limit) {
    $user_id = $order['user_id'];
    $sql_hvy = sprintf("select count(1) from Items where game_id = %s and owner_type='user' and owner_ref_id=%s and mobility='heavy'",quote_smart($game_id),quote_smart($user_id));
    $result_hvy = mysql_query($sql_hvy);        
    if (mysql_result($result_hvy,0,0) > 0) {
      sys_message_to_modchat($game_id,$user_id,"You try to move, but you are holding too much!");
    } else {
      $sql_limit = sprintf("select phys_moves, phys_move_limit from Players where game_id=%s and user_id=%s",quote_smart($game_id),quote_smart($user_id));
      $result_limit = mysql_query($sql_limit);
      $player_moves = mysql_result($result_limit,0,0);
      if ($player_moves == "") {
        $player_moves = 0;
      }
      $player_limit = mysql_result($result_limit,0,1);
      # If player limit is set, check that. If not and game limit is set, check that
      if ($player_limit != "" ? ($player_moves < $player_limit)  
           : ($game_move_limit == "" || $player_moves < $game_move_limit)) {
        $sql_moves = sprintf("update Players set phys_moves=%s where game_id=%s and user_id=%s",quote_smart($player_moves+1),quote_smart($game_id),quote_smart($user_id));
        mysql_query($sql_moves);
        travel_through_exit($game_id,$user_id,$order['exit_id']);          
      } else {
        sys_message_to_modchat($game_id,$user_id,"You are too exhausted to travel any further for now.");      
      }      
    }
}

function process_movements($game_id) {
  $sql = sprintf("select phys_move_limit from Games where id = %s",quote_smart($game_id));
  $result = mysql_query($sql);
  if (mysql_num_rows($result) > 0) {
    $game_move_limit = mysql_result($result,0,0);
  }
  $sql = sprintf("select * from Move_orders where game_id = %s and status='active'",quote_smart($game_id));
  $result = mysql_query($sql);
  while ($order = mysql_fetch_array($result)) {
    conditional_update($order['id'], "Move_orders", "status", "active", "processed");
	process_movement_order($game_id,$order, $game_move_limit);
  }
}

function submit_phys_settings($game_id) {
  global $_POST;
  $sql = sprintf("update Games set phys_move_limit=%s, phys_item_limit=%s, phys_reset_moves=%s where id=%s", opt_string($_POST['move_limit']), opt_string($_POST['item_limit']), quote_smart($_POST['phys_reset_moves']), quote_smart($game_id));
  $result = mysql_query($sql);  
}

function assocToXML ($theArray, $defaultTag, $tabCount=1) {    
    $tabSpace = ""; 
	$realtag = "";
     for ($i = 0; $i<$tabCount; $i++) { 
        $tabSpace .= "\t"; 
     }      
     if ($theArray) {
     foreach($theArray as $tag => $val) { 
        if (!is_array($val)) { 
            $theXML .= PHP_EOL.$tabSpace.'<'.$tag.'>'.htmlentities($val).'</'.$tag.'>'; 
        } else { 
			$realtag = $tag;
			if (is_numeric($tag)) { $realtag = $defaultTag; }
            $theXML .= PHP_EOL.$tabSpace.'<'.$realtag.'>'.assocToXML($val, $defaultTag, $tabCount+1); 
            $theXML .= PHP_EOL.$tabSpace.'</'.$realtag.'>'; 
        } 
    }}     
	return $theXML; 
} 

function xml_export_exits($game_id) {
  $sql = sprintf("select * from Exits where game_id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  while ($exit = mysql_fetch_assoc ($result)) {
	unset($exit['created']);
	unset($exit['game_id']);
	if ($exit['comment'] == "") { unset($exit['comment']); }
	if ($exit['travel_text'] == "") { unset($exit['travel_text']); }
	if ($exit['template_id'] == "") { unset($exit['template_id']); }
    $exits[] = $exit;
  }  
  return '<exits>'.assocToXML($exits, "exit").PHP_EOL.'</exits>'.PHP_EOL;
}

function xml_export_locs($game_id) {
  global $room_names;
  $sql = sprintf("select * from Locations where game_id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  while ($loc = mysql_fetch_assoc ($result)) {
	if ($room_names["{$loc['room_id']}"]) { $loc['room_id'] = $room_names["{$loc['room_id']}"];   }
	if ($loc['description'] == "") { unset($loc['description']); }
	if ($loc['comment'] == "") { unset($loc['comment']); }
	if ($loc['subgame_id'] == "") { unset($loc['subgame_id']); }
	if ($loc['room_id'] == "") { unset($loc['room_id']); }
	unset($loc['created']);
	unset($loc['game_id']);
    $locs[] = $loc;
  }  
  return '<locations>'.assocToXML($locs, "location").PHP_EOL.'</locations>'.PHP_EOL;
}

function xml_export_connections($game_id) {
  $sql = sprintf("select * from Loc_exits where exit_id in (select id from Exits where game_id=%s)",quote_smart($game_id));
  $result = mysql_query($sql);
  while ($conn = mysql_fetch_assoc ($result)) {
    $conns[] = $conn;
  }  
  return '<connections>'.assocToXML($conns, "connection").PHP_EOL.'</connections>'.PHP_EOL;
}

function xml_export_templates($game_id) {
  global $room_names;
  $sql = sprintf("select * from Item_templates where game_id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  while ($temp = mysql_fetch_assoc ($result)) {
	if ($room_names["{$temp['room_id']}"]) { $temp['room_id'] = $room_names["{$temp['room_id']}"];  }
	unset($temp['game_id']);	
	if ($temp['description'] == "") { unset($temp['description']); }
	if ($temp['room_id'] == "") { unset($temp['room_id']); }
	if ($temp['room_alias'] == "") { unset($temp['room_color']); }
	if ($temp['room_alias'] == "") { unset($temp['room_alias']); }
    $temps[] = $temp;
  }  
  return '<templates>'.assocToXML($temps, "template").PHP_EOL.'</templates>'.PHP_EOL;
}

function xml_export_items($game_id) {
  global $room_names;
  $sql = sprintf("select * from Items where game_id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  while ($item = mysql_fetch_assoc ($result)) {
	unset($item['game_id']);
	unset($item['created']);
	if ($room_names["{$item['room_id']}"]) { $item['room_id'] = $room_names["{$item['room_id']}"]; }
	if ($item['room_id'] == "") { unset($item['room_id']); }
	if ($item['room_alias'] == "") { unset($item['room_color']); }
	if ($item['room_alias'] == "") { unset($item['room_alias']); }		
	if ($item['description'] == "") { unset($item['description']); }	
    $items[] = $item;
  }  
  return '<items>'.assocToXML($items, "item").PHP_EOL.'</items>'.PHP_EOL;
}

function xml_export_triggers($game_id) {
  return '<triggers>'.PHP_EOL.'</triggers>'.PHP_EOL;
}

function xml_import_physics($game_id, $data) {
  $sql = sprintf("select * from Exits where game_id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  while ($exit = mysql_fetch_assoc ($result)) { $exits[$exit['id']] = $exit; }
  $sql = sprintf("select * from Item_templates where game_id=%s",quote_smart($game_id));
  $result = mysql_query($sql);
  while ($temp = mysql_fetch_assoc ($result)) { $temps[$temp['id']] = $temp; }
  $room_names = get_room_names_by_id($game_id);
  $game_names = get_subgame_names_by_id($game_id);
  $loc_names = get_loc_names_by_id($game_id);
  $item_names = get_item_names_by_id($game_id);
  $temp_names = get_item_temp_names_by_id($game_id);
  $exit_names = get_exit_names_by_id($game_id);
  echo "Importing Data <br />";
  
  if ($data->config) {
    $delete_extra = $data->config->delete_extra == "true";
	if ($delete_extra) { echo "Configured to delete old data.<br />"; }
	
  }
  
  if ($data->locations) {
	echo "Importing Locations <br />";
    foreach($data->locations->location as $newloc)
	{
	  $sub_id=find_id_for_string($game_names, "{$newloc->subgame_id}");
	  $room_id=find_id_for_string($room_names, "{$newloc->room_id}");	
	  if ($newloc->room_id && ($room_id == "")) { 
		$room_id = create_room_for_loc($game_id, $newloc->room_id);
		$room_names[$room_id] = $newloc->room_id;
		echo "Created chatroom ".$room_id.":".$newloc->room_id."<br />";
	  }
	  if ("{$newloc->id}" && isset($loc_names["{$newloc->id}"]) && $loc_names["{$newloc->id}"]) {		
	    echo "Editing Location ".$newloc->id. ":".$newloc->name."<br />";
	    edit_loc($newloc->id, $newloc->name, $room_id, $newloc->description, $newloc->comment, $sub_id, $newloc->visibility);
		$locs["{$newloc->id}"] = 1;
	  } else {	  
		$name = $newloc->name ? $newloc->name : "Unnamed Location";		
		$newid = insert_loc($game_id, $name, $newloc->description, $newloc->comment, $sub_id, $room_id, $newloc->visibility);
		echo "Added Location ".$newid.":".$name."<br />";
		$loc_names[$newid] = $name;
		$locs[$newid] = 1;
	  }
	}
	if ($delete_extra) { foreach($loc_names as $id=>$loc) {
	  if (!$locs[$id]) {
		echo "Deleting old location: $id<br />";
		delete_loc($id);
	  }
	}}
  }
}

function import_physics($game_id) {
  global $_POST;
  $from_id = $_POST['import_physics'];
  
  $mod_names = get_mod_names_by_ids($game_id);
  $mods = array_keys($mod_names);  
  
  #location-specific chatrooms
  $sql = sprintf("select * from Chat_rooms where id in (select distinct room_id from Locations where game_id = %s)",quote_smart($from_id));
  $result = mysql_query($sql);
  while ($chat = mysql_fetch_array($result)) {
    $chats_by_orig_id[$chat['id']] = $chat;
    $room_id = insert_chat_room($game_id, $chat['name'], $chat['max_post']);
    $chats_by_orig_id[$chat['id']]['id'] = $room_id; 
    foreach ( $mods as $mod ) {
      $mod_id = sprintf("%0d",$mod);
      insert_chat_user($room_id,$mod,get_profile_color($mod_id,"#000000",'on'),"N/A");
    }    
  }
    
  #locations
  $sql = sprintf("select * from Locations where game_id=%s",quote_smart($from_id));
  $result = mysql_query($sql);
  while ($loc = mysql_fetch_array($result)) {
    $locs_by_orig_id[$loc['id']] = $loc;
    $loc['room_id'] = $chats_by_orig_id[$loc['room_id']]['id'];
    $locs_by_orig_id[$loc['id']]['id'] = insert_loc($game_id, $loc['name'], $loc['description'], $loc['comment'], "", $loc['room_id'], $loc['visibility']);
  }
  
  #templates
  $sql = sprintf("select * from Item_templates where game_id=%s",quote_smart($from_id));
  $result = mysql_query($sql);
  while ($temp = mysql_fetch_array($result)) {
    $temps_by_orig_id[$temp['id']] = $temp;
    $temps_by_orig_id[$temp['id']]['id'] = insert_item_temp($game_id, $temp['name'], $temp['description'], $temp['visibility'], $temp['mobility'], $temp['room_id']);
  }   
  
  #items
  $sql = sprintf("select * from Items where game_id=%s",quote_smart($from_id));
  $result = mysql_query($sql);
  while ($item = mysql_fetch_array($result)) {
    $items_by_orig_id[$item['id']] = $item;
    $item['owner_ref_id'] = $item['owner_type'] == 'user' ? "" :
      $locs_by_orig_id[$item['owner_ref_id']]['id'];
	if ($temps_by_orig_id) {
		$item['template_id'] = $temps_by_orig_id[$item['template_id']]['id'];
	}
    $items_by_orig_id[$item['id']]['id'] = insert_item($game_id, $item['template_id'], $item['name'], $item['description'], $item['visibility'], $item['mobility'], $item['owner_type'], $item['owner_ref_id'], $item['room_id']);
  }   
  
  #exits
  $sql = sprintf("select * from Exits where game_id=%s",quote_smart($from_id));
  $result = mysql_query($sql);
  while ($exit = mysql_fetch_array($result)) {
    $exits_by_orig_id[$exit['id']] = $exit;
    $exit['item_id'] = $items_by_orig_id[$exit['item_id']]['id'];
    $exits_by_orig_id[$exit['id']]['id'] = insert_exit($game_id, $exit['name'], $exit['travel_text'], $exit['comment'],$exit['item_id']);
  }    
  
  #location-exit mappings
  $sql = sprintf("select * from Loc_exits where loc_from_id in (select id from Locations where game_id = %s)",quote_smart($from_id));
  $result = mysql_query($sql);
  while ($loc_map = mysql_fetch_array($result)) {
    insert_exit_loc_map(
      $exits_by_orig_id[$loc_map['exit_id']]['id'],
      $locs_by_orig_id[$loc_map['loc_from_id']]['id'],
      $locs_by_orig_id[$loc_map['loc_to_id']]['id']
    );
  }
  
}

function submit_schedule_physics($game_id) {
  global $_POST;
  if ($_POST['item_set'] == 'on') {
    $sql = sprintf("select * from Physics_processing where game_id=%s and type='item'",quote_smart($game_id));
    $result = mysql_query($sql);
    if ($current = mysql_fetch_array($result)) {
      conditional_update($current['id'],"Physics_processing", "frequency", $current['frequency'],$_POST['item_frequency']);
      conditional_update($current['id'],"Physics_processing", "minute", $current['minute'],$_POST['item_minute']);
      conditional_update($current['id'],"Physics_processing", "hour", $current['hour'],$_POST['item_hour']);      
    } else {
      $sql = sprintf("insert into Physics_processing (id, game_id, type, frequency, minute, hour, last_run) values (NULL, %s, 'item', %s, %s, %s, now())",quote_smart($game_id), quote_smart($_POST['item_frequency']), quote_smart($_POST['item_minute']), opt_string($_POST['item_hour']));
      mysql_query($sql);
    }       
  } else {
    $sql = sprintf("delete from Physics_processing where game_id=%s and type='item'",quote_smart($game_id));
    mysql_query($sql);
  }    
  if ($_POST['movement_set'] == 'on') {
    $sql = sprintf("select * from Physics_processing where game_id=%s and type='movement'",quote_smart($game_id));
    $result = mysql_query($sql);
    if ($current = mysql_fetch_array($result)) {
      conditional_update($current['id'],"Physics_processing", "frequency", $current['frequency'],$_POST['move_frequency']);
      conditional_update($current['id'],"Physics_processing", "minute", $current['minute'],$_POST['move_minute']);
      conditional_update($current['id'],"Physics_processing", "hour", $current['hour'],$_POST['move_hour']);      
    } else {
      $sql = sprintf("insert into Physics_processing (id, game_id, type, frequency, minute, hour, last_run) values (NULL, %s, 'movement', %s, %s, %s, now())",quote_smart($game_id), quote_smart($_POST['move_frequency']), quote_smart($_POST['move_minute']), opt_string($_POST['move_hour']));
      mysql_query($sql);
    }       
  } else {
    $sql = sprintf("delete from Physics_processing where game_id=%s and type='movement'",quote_smart($game_id));
    mysql_query($sql);
  }
}

function submit_new_exit($game_id) {
  global $_POST;
  if ( $_POST['exit_name'] == "" ) { $_POST['exit_name'] = "Out"; }

  $exit_id = insert_exit($game_id,$_POST['exit_name'],$_POST['exit_travel_text'],$_POST['exit_comment'],$_POST['exit_temp_id']);
  $sql_loc = sprintf("select id from Locations where game_id=%s",quote_smart($game_id));
  $result_loc = mysql_query($sql_loc);
  while ( $loc = mysql_fetch_array($result_loc) ) {
    $from = $loc['id'];
    if ( $_POST["loc_$from"] == "on" ) {
      insert_exit_loc_map($exit_id,$from,$_POST["dest_$from"]);
    }
  }  
}

function submit_comb_exit($game_id) {
  $locs = get_loc_names_by_id($game_id);
  foreach ($locs as $dest_id => $dest_name) {
    $exit_id = insert_exit($game_id,$dest_name,"","","");
    foreach ($locs as $src_id => $src_name) {
      insert_exit_loc_map($exit_id,$src_id,$dest_id);
    }
  }
}

function submit_new_item($game_id) {
  global $_POST;
  if ( $_POST['item_name'] == "" ) { $_POST['item_name'] = "Unnamed Item"; }
  if ( $_POST['item_temp_id'] == '' ) { $_POST['item_temp_id'] = insert_item_temp($game_id,$_POST['item_name'],$_POST['item_description'],$_POST['item_visibility'],$_POST['item_mobility'], $_POST['item_room_id'], $_POST['item_room_alias'], $_POST['item_room_color']); }
  
  $item_id = insert_item($game_id,$_POST['item_temp_id'], $_POST['item_name'],$_POST['item_description'],$_POST['item_visibility'],$_POST['item_mobility'],$_POST['item_owner_type'],$_POST['item_owner_type'] == 'user' ? $_POST['item_player'] : $_POST['item_loc'], $_POST['item_room_id'], $_POST['item_room_alias'], $_POST['item_room_color']);
} 

function submit_new_item_temp($game_id) {
  global $_POST;
  if ( $_POST['temp_name'] == "" ) { $_POST['temp_name'] = "Unnamed Item"; }
  
  $temp_id = insert_item_temp($game_id,$_POST['temp_name'],$_POST['temp_description'],$_POST['temp_visibility'],$_POST['temp_mobility'], $_POST['temp_room_id'], $_POST['temp_room_alias'], $_POST['temp_room_color']);
} 

function submit_new_loc($game_id) {
  global $_POST;
  if ( $_POST['loc_name'] == "" ) { $_POST['loc_name'] = "Unnamed Location"; }
  if ( $_POST['loc_room_id'] == 'new' ) { $_POST['loc_room_id'] = create_room_for_loc($game_id,$_POST['loc_name']); }

  $loc_id = insert_loc($game_id,$_POST['loc_name'],$_POST['loc_description'],$_POST['loc_comment'],$_POST['loc_subgame_id'],$_POST['loc_room_id'],$_POST['loc_visibility']);
}

function get_item_info($item_id) {
  $sql = sprintf("select name, description, visibility, mobility, owner_ref_id, owner_type, template_id, room_id, room_alias, room_color from Items where id=%s", quote_smart($item_id));
  $result = mysql_query($sql);
  $output['name'] = mysql_result($result,0,0);
  $output['description'] = mysql_result($result,0,1);
  $output['visibility'] = mysql_result($result,0,2);
  $output['mobility'] = mysql_result($result,0,3);
  $output['owner_ref_id'] = mysql_result($result,0,4);
  $output['owner_type'] = mysql_result($result,0,5);
  $output['template_id'] = mysql_result($result,0,6);
  $output['room_id'] = mysql_result($result,0,7);
  $output['room_alias'] = mysql_result($result,0,8);
  $output['room_color'] = mysql_result($result,0,9);
  return $output;
}

function get_all_item_names_by_player($game_id) {
  $sql = sprintf("select id, name, owner_ref_id, owner_type from Items where owner_type='user' and game_id=%s", quote_smart($game_id));
  $result = mysql_query($sql);
  while ($map_row = mysql_fetch_array($result)) {
    $output[intval($map_row['owner_ref_id'])][] = $map_row['name'];
  }
  return $output;

}

function get_item_temp_info($temp_id) {
  $sql = sprintf("select name, description, visibility, mobility, room_id, room_alias, room_color from Item_templates where id=%s", quote_smart($temp_id));
  $result = mysql_query($sql);
  $output['name'] = mysql_result($result,0,0);
  $output['description'] = mysql_result($result,0,1);
  $output['visibility'] = mysql_result($result,0,2);
  $output['mobility'] = mysql_result($result,0,3);
  $output['room_id'] = mysql_result($result,0,4);
  $output['room_alias'] = mysql_result($result,0,5);
  $output['room_color'] = mysql_result($result,0,6);
  return $output;
}

function get_all_item_temp_info($game_id) {
  $sql = sprintf("select id, name, description, visibility, mobility, room_id, room_alias, room_color from Item_templates where game_id=%s", quote_smart($game_id));
  $result = mysql_query($sql);
  while ($map_row = mysql_fetch_array($result)) {
  	$output[$map_row['id']]['name'] = $map_row['name'];
	$output[$map_row['id']]['description'] = $map_row['description'];
	$output[$map_row['id']]['visibility'] = $map_row['visibility'];
	$output[$map_row['id']]['mobility'] = $map_row['mobility'];
	$output[$map_row['id']]['room_id'] = $map_row['room_id']; 
	$output[$map_row['id']]['room_alias'] = $map_row['room_alias']; 
	$output[$map_row['id']]['room_color'] = $map_row['room_color']; 
  }
  return $output;
}

function get_loc_info($loc_id) {
  $sql = sprintf("select name, description, comment, subgame_id, room_id, visibility from Locations where id=%s", quote_smart($loc_id));
  $result = mysql_query($sql);
  $output['name'] = mysql_result($result,0,0);
  $output['description'] = mysql_result($result,0,1);
  $output['comment'] = mysql_result($result,0,2);
  $output['subgame_id'] = mysql_result($result,0,3);
  $output['room_id'] = mysql_result($result,0,4);
  $output['visibility'] = mysql_result($result,0,5);
  return $output;
}

function get_exit_info($exit_id) {
  $sql = sprintf("select name, travel_text, comment, template_id from Exits where id=%s", quote_smart($exit_id));
  $result = mysql_query($sql);
  $output['name'] = mysql_result($result,0,0);
  $output['travel_text'] = mysql_result($result,0,1);
  $output['comment'] = mysql_result($result,0,2);
  $output['template_id'] = mysql_result($result,0,3);
  return $output;
}

function get_exit_loc_info($game_id) {
  $sql_map = sprintf("select * from Loc_exits where exit_id in (select id from Exits where game_id=%s)",quote_smart($game_id));
  $result_map = mysql_query($sql_map);
  while ($map_row = mysql_fetch_array($result_map) ) {
    $map['by_exit'][$map_row['exit_id']][$map_row['loc_from_id']] = $map_row['loc_to_id'];
    $map['by_from'][$map_row['loc_from_id']][$map_row['exit_id']] = $map_row['loc_to_id'];
  }
  return $map;
}

function get_exit_loc_info_by_loc($loc_id) {
  $sql_map = sprintf("select * from Loc_exits where loc_from_id = %s",quote_smart($loc_id));
  $result_map = mysql_query($sql_map);
  while ($map_row = mysql_fetch_array($result_map) ) {
    $map[$map_row['exit_id']] = $map_row['loc_to_id'];
  }
  return $map;
}

function get_exit_loc_info_by_exit($exit_id) {
  $sql_map = sprintf("select * from Loc_exits where exit_id = %s",quote_smart($exit_id));
  $result_map = mysql_query($sql_map);
  while ($map_row = mysql_fetch_array($result_map) ) {
    $map[$map_row['loc_from_id']] = $map_row['loc_to_id'];
  }
  return $map;
}

function get_player_loc_info($game_id) {
  $sql = sprintf("select user_id,loc_id,modchat_id,user_id real_id, death_phase, phys_moves, phys_move_limit, phys_item_limit from Players p, Users u where p.user_id = u.id and game_id=%s order by u.name", quote_smart($game_id));
  $result = mysql_query($sql);
  while ( $p = mysql_fetch_array($result) ) {
   $players[$p['user_id']] = $p;
  }
  $sql_rep = sprintf("select user_id, replace_id from Replacements where game_id=%s order by number, period ASC",quote_smart($game_id));
  $result_rep = mysql_query($sql_rep);
  while ( $rep = mysql_fetch_array($result_rep) ) {
   $players[$rep['user_id']]['real_id'] = $rep['replace_id'];
  }   
  return $players;
}

function remove_player_from_loc($game_id, $uid, $dont_remove_room) {
  $sql = sprintf("select loc_id, subgame_id, room_id from Players p, Locations l where p.loc_id=l.id and p.game_id=%s and p.user_id=%s",quote_smart($game_id), quote_smart($uid));
  $result_id  = mysql_query($sql);
  if (mysql_num_rows($result_id) > 0) {
    $loc = mysql_result($result_id,0,0);
    $subgame_id = mysql_result($result_id,0,1);
    $room_id = mysql_result($result_id,0,2);

    $update_player_sql = sprintf("update Players set loc_id=NULL where game_id=%s and user_id=%s", quote_smart($game_id), quote_smart($uid));
    mysql_query($update_player_sql);

    $uid = get_current_player($game_id,$uid);

    if ($subgame_id != "" && $subgame_id != "NULL") {
      $update_sub_sql = sprintf("delete from Players where game_id=%s and user_id=%s", quote_smart($subgame_id), quote_smart($uid));
      mysql_query($update_sub_sql);
    }

    if (!($dont_remove_room) && $room_id != "" && $room_id != "NULL") {
	  $sql = sprintf("select 1 from Items where owner_type='user' and owner_ref_id=%s and room_id=%s", quote_smart($uid), quote_smart($room_id));
	  $result_access = mysql_query($sql);
	  if (mysql_num_rows($result_access) == 0) {	 
		$sql = sprintf("update Chat_users set close=now(), Chat_users.lock='On' where room_id=%s and user_id=%s",quote_smart($room_id), quote_smart($uid));
		mysql_query($sql);
		$sql = sprintf("select name from Locations where id=%s",quote_smart($loc));
		$result = mysql_query($sql);
		$name = mysql_result($result,0,0);
		$msg = sprintf("%s has left %s",get_alias($game_id, $uid),$name);
		sys_message_to_chat($room_id,$msg);
    }}  
  }
}

function sys_message_to_modchat($game_id, $uid, $message) {
  $sql = sprintf("select modchat_id from Players where user_id=%s and game_id=%s",quote_smart($uid),quote_smart($game_id));
  $result = mysql_query($sql);
  if (mysql_num_rows($result) > 0) {
    $modchat = mysql_result($result,0,0);
    sys_message_to_chat($modchat, $message);
  }  
}

function sys_message_to_chat($room_id, $message) {
  $sql = sprintf("insert into Chat_messages (id, room_id, user_id, message, post_time) values (NULL, %s,%s,%s, now() )",quote_smart($room_id),quote_smart(306),quote_smart($message));
  $result = mysql_query($sql);
}

function are_locs_in_diff_chatrooms($from_id, $to_id) {
  $sql = sprintf("select 1 from dual where (SELECT room_id FROM `Locations` where id=%s) = (SELECT room_id FROM `Locations` where id=%s)",quote_smart($to_id),quote_smart($from_id));
  $result = mysql_query($sql);
  return mysql_num_rows($result);    
}

function travel_through_exit($game_id, $uid, $exit_id) {
  $sql = sprintf("select e.loc_to_id as to_id, e.loc_from_id as from_id from Loc_exits e where e.exit_id=%s and e.loc_from_id = (select loc_id from Players where game_id=%s and user_id=%s)",quote_smart($exit_id),quote_smart($game_id), quote_smart($uid));
  $result = mysql_query($sql);
  if (mysql_num_rows($result) > 0) {
    $to_id = mysql_result($result,0,0);    
    $from_id = mysql_result($result,0,1);
    
    $diff_rooms = are_locs_in_diff_chatrooms($from_id, $to_id);
        
    remove_player_from_loc($game_id, $uid, $diff_rooms);
    $sql = sprintf("select travel_text from Exits where id=%s",quote_smart($exit_id));
    $result = mysql_query($sql);
    $message = mysql_result($result,0,0);
    if ($message != "") {      
      sys_message_to_modchat($game_id,$uid,$message); 
    }
    
    add_player_to_loc($game_id,$uid,$to_id, $diff_rooms);
  }  
}

function get_alias($game_id, $uid) {
  $name = '';
  $sql_game = sprintf("select phys_by_alias from Games where id = %s", quote_smart($game_id));
  $result_game  = mysql_query($sql_game);
  if (mysql_num_rows($result_game) > 0 && mysql_result($result_game,0,0) == 'Yes')  
  {    
	$sql_alias = sprintf("select player_alias from Players_all where game_id=%s and user_id=%s", quote_smart($game_id), quote_smart($uid));
	$result_alias  = mysql_query($sql_alias);
	if (mysql_num_rows($result_alias) > 0)
	{
	  $name = mysql_result($result_alias,0,0);
	}
  }
  if ($name == '')
  {
    $sql_name = sprintf("select name from Users where id = %s", quote_smart($uid));
	$result_name = mysql_query($sql_name);
	$name = mysql_result($result_name,0,0);
  }
  return $name;
}

function update_alias_in_chat($game_id, $uid, $room_id) {
  $name = '';
  $sql_game = sprintf("select phys_by_alias from Games where id = %s", quote_smart($game_id));
  $result_game  = mysql_query($sql_game);
  if (mysql_num_rows($result_game) > 0 && mysql_result($result_game,0,0) == 'Yes')
  {    
	$sql_alias = sprintf("select player_alias, alias_color from Players_all where game_id=%s and user_id=%s", quote_smart($game_id), quote_smart($uid));
	$result_alias  = mysql_query($sql_alias);
	if (mysql_num_rows($result_alias) > 0)
	{
	  $name = mysql_result($result_alias,0,0);
	  $color = mysql_result($result_alias,0,1);
	}
	if ($name != '')
	{ update_chat_user($room_id, $uid, 'alias', $name);	}
	if ($color != '')
	{ update_chat_user($room_id, $uid, 'color', $color);	}
  }
}

function update_alias_for_item($game_id, $uid, $room_id, $alias, $color) {
  $name = '';
  if ($alias != '') { 
	update_chat_user($room_id, $uid, 'alias', $alias);	
		if ($color != '')
		{ update_chat_user($room_id, $uid, 'color', $color);	}
  }
  else { update_alias_in_chat($game_id, $uid, $room_id); }
}

function add_player_to_loc($game_id, $uid, $loc_id, $dont_add_to_room) {
  $sql = sprintf("select name, description, subgame_id, room_id from Locations l where id=%s",quote_smart($loc_id));
  $result_id  = mysql_query($sql);
  if (mysql_num_rows($result_id) > 0) {
    $name = mysql_result($result_id,0,0);
    $description = mysql_result($result_id,0,1);
    $subgame_id = mysql_result($result_id,0,2);
    $room_id = mysql_result($result_id,0,3);

    $update_player_sql = sprintf("update Players set loc_id=%s where game_id=%s and user_id=%s", quote_smart($loc_id), quote_smart($game_id), quote_smart($uid));
    mysql_query($update_player_sql);
   
    $msg = sprintf("Current Location: <b>%s</b> %s %s",$name,($description == "" ? "" : "<br />"),$description); 
    sys_message_to_modchat($game_id,$uid,$msg);

    $uid = get_current_player($game_id,$uid);

    if ($subgame_id != "" && $subgame_id != "NULL") {
      $update_sub_sql = sprintf("insert into Players (user_id, game_id) values (%s, %s)", quote_smart($subgame_id), quote_smart($uid));
      mysql_query($update_sub_sql);
    }

    if (!$dont_add_to_room && $room_id != "" && $room_id != "NULL") {
      $sql = sprintf("select Chat_users.lock, Chat_users.close from Chat_users where room_id=%s and user_id=%s",quote_smart($room_id), quote_smart($uid));
      $result_lock = mysql_query($sql);
      if (mysql_num_rows($result_lock) > 0) {
        $lock = mysql_result($result_lock,0,0);        
		$close = mysql_result($result_lock,0,1);
		if ($close) {
          if ($lock != "On") {
			$sql_chat = sprintf("update Chat_users set open=now(), close=NULL where room_id=%s and user_id=%s",quote_smart($room_id), quote_smart($uid));
			mysql_query($sql_chat);
          } else {
			$sql_chat = sprintf("update Chat_users set open=now(), close=NULL, Chat_users.lock='Off' where room_id=%s and user_id=%s",quote_smart($room_id), quote_smart($uid));
			mysql_query($sql_chat);
			$msg = sprintf("%s has entered %s",get_alias($game_id, $uid),$name);
			sys_message_to_chat($room_id,$msg);		
          }		  
		  update_alias_in_chat($game_id, $uid, $room_id);
		}	
	  } else {
	    $sql_chat = sprintf("insert into Chat_users (room_id, user_id, color, open) values (%s, %s, %s, now())",quote_smart($room_id), quote_smart($uid), quote_smart(get_profile_color($uid,"#000000","on")));
		mysql_query($sql_chat);
		update_alias_in_chat($game_id, $uid, $room_id);
		$msg = sprintf("%s has entered %s",get_alias($game_id, $uid),$name);
		sys_message_to_chat($room_id,$msg);		
	  }
    }  
  }
}

function submit_edit_exit($game_id) {
  global $_POST;
  $exit_id = $_POST['exit_id'];
  $old_data = get_exit_info($exit_id);

  if ( $_POST['exit_name'] == "" ) { $_POST['exit_name'] = "Out"; }  
  
  conditional_update($exit_id, 'Exits', 'name', $old_data['name'], $_POST['exit_name']);
  conditional_update($exit_id, 'Exits', 'travel_text', $old_data['travel_text'], $_POST['exit_travel_text']);
  conditional_update($exit_id, 'Exits', 'comment', $old_data['comment'], $_POST['exit_comment']);
  conditional_update($exit_id, 'Exits', 'template_id', $old_data['template_id'], $_POST['exit_temp_id']);  

  $old_map = get_exit_loc_info_by_exit($exit_id);
  $locs = get_loc_names_by_id($game_id);
  if (count($locs) > 0) {
    foreach ( $locs as $src_id => $src_name ) {
      delete_exit_loc_map($exit_id,$src_id);
      if ( $_POST["loc_$src_id"] == "on" ) {
          insert_exit_loc_map($exit_id,$src_id,$_POST["dest_$src_id"]);
      }
  }}
}

function edit_loc($loc_id, $loc_name, $loc_room_id, $loc_description, $loc_comment, $loc_subgame_id, $loc_visibility) {
  conditional_update($loc_id, 'Locations', 'name', $old_data['name'], $loc_name);
  conditional_update($loc_id, 'Locations', 'description', $old_data['description'], $loc_description);
  conditional_update($loc_id, 'Locations', 'comment', $old_data['comment'], $loc_comment);
  conditional_update($loc_id, 'Locations', 'subgame_id', $old_data['subgame_id'], $loc_subgame_id);
  conditional_update($loc_id, 'Locations', 'room_id', $old_data['room_id'], $loc_room_id);
  conditional_update($loc_id, 'Locations', 'visibility', $old_data['visibility'], $loc_visibility);
}

function submit_edit_loc($game_id) {
  global $_POST;
  $loc_id = $_POST['loc_id'];
  $old_data = get_loc_info($loc_id);
  
  if ( $_POST['loc_name'] == "" ) { $_POST['loc_name'] = "Unnamed Location"; }
  if ( $_POST['loc_room_id'] == 'new' ) { $_POST['loc_room_id'] = create_room_for_loc($game_id,$_POST['loc_name']); }

  edit_loc($loc_id, $_POST['loc_name'], $_POST['loc_room_id'], $_POST['loc_description'], $_POST['loc_comment'], $_POST['loc_subgame_id'], $_POST['loc_visibility']);
  
  $old_map = get_exit_loc_info_by_loc($loc_id);
  $exits = get_exit_names_by_id($game_id);
  if (count($exits) > 0) {
    foreach ($exits as $exit_id => $exit_name ) {
      delete_exit_loc_map($exit_id,$loc_id);
      if ( $_POST["exit_$exit_id"] == "on" ) {
          insert_exit_loc_map($exit_id,$loc_id,$_POST["dest_$exit_id"]);
      }
  }}  
}

function submit_edit_player($game_id) {
  global $_POST;
  $old_data = get_player_loc_info($game_id);

  foreach ($old_data as $old_id => $old) {
    if ( $old['loc_id'] != $_POST["ploc_$old_id"] ) {
      if ($old['loc_id'] != "" && $_POST["ploc_$old_id"] != "") {
        $diff_room = are_locs_in_diff_chatrooms($old['loc_id'],$_POST["ploc_$old_id"]); 
      } else {
        $diff_room = 1;
      }      
      remove_player_from_loc($game_id,$old_id,$diff_room);
      if ($_POST["ploc_$old_id"] != "") {
        add_player_to_loc($game_id,$old_id,$_POST["ploc_$old_id"], $diff_room);
      }
    }
    if ( $old['modchat_id'] != $_POST["pchat_$old_id"] ) {
	     set_player_modchat($game_id,$old_id,$_POST["pchat_$old_id"]);      
    }
    $sql = sprintf("Update Players set phys_moves=%s, phys_move_limit=%s, phys_item_limit=%s where user_id=%s and game_id=%s",quote_smart($_POST["moves_$old_id"]),opt_string($_POST["move_limit_$old_id"]),opt_string($_POST["item_limit_$old_id"]),quote_smart($old_id),quote_smart($game_id));
    mysql_query($sql);
  }
}

function on_item_change_owner($game_id, $item_id, $new_owner="") {
  $sql = sprintf("select owner_ref_id, owner_type, room_id, room_alias, room_color from Items where id=%s",quote_smart($item_id));
  $result_item = mysql_query($sql);
  if (($iteminfo = mysql_fetch_assoc ($result_item)) && $iteminfo['room_id']) {
    $room_id=$iteminfo['room_id'];
	$alias=$iteminfo['room_alias'];
	$color=$iteminfo['room_color'];
    $sql = sprintf("select name from Items where id=%s",quote_smart($item_id));
    $result = mysql_query($sql);
    $name = mysql_result($result,0,0);  
	if ($iteminfo['owner_type'] == 'user') {
	  $uid = get_current_player($game_id,$iteminfo['owner_ref_id']);
	  $sql = sprintf("select 1 from Items where owner_type='user' and owner_ref_id=%s and room_id=%s and id<>%s union select 1 from Locations l, Players p  where p.loc_id=l.id and p.game_id=%s and p.user_id=%s and l.room_id=%s", quote_smart($iteminfo['owner_ref_id']), quote_smart($room_id), quote_smart($item_id), quote_smart($game_id), quote_smart($iteminfo['owner_ref_id']), quote_smart($room_id));
	  $result = mysql_query($sql);
	  if (mysql_num_rows($result) == 0) {	  		
		$sql = sprintf("update Chat_users set close=now(), Chat_users.lock='On' where room_id=%s and user_id=%s",quote_smart($room_id), quote_smart($uid));
		mysql_query($sql);
		$msg = sprintf("%s no longer has %s",$alias ? $alias : get_alias($game_id, $uid),$name);
		sys_message_to_chat($room_id,$msg);	  
	}}
	if ($new_owner) {
	  $uid = get_current_player($game_id,$new_owner);  	
      $sql = sprintf("select Chat_users.lock, Chat_users.close from Chat_users where room_id=%s and user_id=%s",quote_smart($room_id), quote_smart($uid));
      $result_lock = mysql_query($sql);
      if (mysql_num_rows($result_lock) > 0) {
        $lock = mysql_result($result_lock,0,0);        
		$close = mysql_result($result_lock,0,1);
		if ($close) {
          if ($lock != "On") {
			$sql_chat = sprintf("update Chat_users set open=now(), close=NULL where room_id=%s and user_id=%s",quote_smart($room_id), quote_smart($uid));
			mysql_query($sql_chat);
          } else {
			$sql_chat = sprintf("update Chat_users set open=now(), close=NULL, Chat_users.lock='Off' where room_id=%s and user_id=%s",quote_smart($room_id), quote_smart($uid));
			mysql_query($sql_chat);
			$msg = sprintf("%s has acquired %s",$alias ? $alias : get_alias($game_id, $uid),$name);
			sys_message_to_chat($room_id,$msg);	  			
          }		  
		  update_alias_for_item($game_id, $uid, $room_id, $alias, $color);
		}		        
      } else {
        insert_chat_user($room_id,$uid,get_profile_color($uid,"#000000","on"),"");
		update_alias_for_item($game_id, $uid, $room_id, $alias, $color);
		$msg = sprintf("%s has acquired %s",$alias ? $alias : get_alias($game_id, $uid),$name);
		sys_message_to_chat($room_id,$msg);	  
      }
	}
  }
}

function give_item_to_player($game_id, $user_id, $item_id) {
  on_item_change_owner($game_id, $item_id, $user_id);
  $sql = sprintf("update Items set owner_type='user', owner_ref_id=%s where id=%s", quote_smart($user_id), quote_smart($item_id));
  mysql_query($sql);
  $sql = sprintf("select name,description from Items where id=%s",quote_smart($item_id));
  $result = mysql_query($sql);
  $name = mysql_result($result,0,0);
  if ($desc = mysql_result($result,0,1)) {
    sys_message_to_modchat($game_id, $user_id, "<b>$name</b>: $desc");
  } 
}

function give_item_to_loc($game_id, $loc_id, $item_id) {
  on_item_change_owner($game_id, $item_id);
  $sql = sprintf("update Items set owner_type='loc', owner_ref_id=%s where id=%s", quote_smart($loc_id), quote_smart($item_id));
  mysql_query($sql);
#  $sql = sprintf("select name from Items where id=%s",quote_smart($item_id));
#  $result = mysql_query($sql);
#  $name = mysql_result($result,0,0);
#  $sql = sprintf("select user_id from Players where loc_id=%s and game_id=%s", quote_smart($game_id), quote_smart($loc_id));
#  $result = mysql_query($sql);
#  while ($usr = mysql_fetch_array($result)) {
#    sys_message_to_modchat($game_id, $user_id, "You $desc); 
#  }     
}

function submit_edit_item($game_id) {
  global $_POST;
  $item_id = $_POST['item_id'];
  $old_data = get_item_info($item_id);
  
  if ( $_POST['item_name'] == "" ) { $_POST['item_name'] = "Unnamed Item"; }
  
  conditional_update($item_id, 'Items', 'name', $old_data['name'], $_POST['item_name']);
  conditional_update($item_id, 'Items', 'description', $old_data['description'], $_POST['item_description']);    
  conditional_update($item_id, 'Items', 'visibility', $old_data['visibility'], $_POST['item_visibility']);  
  conditional_update($item_id, 'Items', 'mobility', $old_data['mobility'], $_POST['item_mobility']);    
  //conditional_update($item_id, 'Items', 'owner_type', $old_data['owner_type'], $_POST['item_owner_type']);
  conditional_update($item_id, 'Items', 'room_id', $old_data['room_id'], $_POST['item_room_id']);
  conditional_update($item_id, 'Items', 'room_alias', $old_data['room_alias'], $_POST['item_room_alias']);
  conditional_update($item_id, 'Items', 'room_color', $old_data['room_color'], $_POST['item_room_color']);
	 
  if ($_POST['item_owner_type'] == 'user') {
	if ($_POST['item_player'] != $old_data['owner_ref_id'] || $_POST['item_owner_type'] != $old_data['owner_type']) {
	  give_item_to_player($game_id,$_POST['item_player'],$item_id);
	}
  } else {
	if ($_POST['item_loc'] != $old_data['owner_ref_id'] || $_POST['item_owner_type'] != $old_data['owner_type']) {
	  give_item_to_loc($game_id,$_POST['item_loc'],$item_id);
	}
  }
}

function submit_edit_item_temp($game_id) {
  global $_POST;
  $temp_id = $_POST['temp_id'];
  $old_data = get_item_temp_info($temp_id);
  
  if ( $_POST['temp_name'] == "" ) { $_POST['temp_name'] = "Unnamed Item"; }
  
  conditional_update($temp_id, 'Item_templates', 'name', $old_data['name'], $_POST['temp_name']);
  conditional_update($temp_id, 'Item_templates', 'description', $old_data['description'], $_POST['temp_description']);    
  conditional_update($temp_id, 'Item_templates', 'visibility', $old_data['visibility'], $_POST['temp_visibility']);  
  conditional_update($temp_id, 'Item_templates', 'mobility', $old_data['mobility'], $_POST['temp_mobility']);    
  conditional_update($temp_id, 'Item_templates', 'room_id', $old_data['room_id'], $_POST['temp_room_id']);
  conditional_update($temp_id, 'Item_templates', 'room_alias', $old_data['room_alias'], $_POST['temp_room_alias']);
  conditional_update($temp_id, 'Item_templates', 'room_color', $old_data['room_color'], $_POST['temp_room_color']);
	
  if ( $_POST['item_apply'] == "on" ) {
    item_update($temp_id, 'name', $_POST['temp_name']);
	item_update($temp_id, 'description', $_POST['temp_description']);
	item_update($temp_id, 'visibility', $_POST['temp_visibility']);
	item_update($temp_id, 'mobility', $_POST['temp_mobility']);
	item_update($temp_id, 'room_id', $_POST['temp_room_id']);
	item_update($temp_id, 'room_alias', $_POST['temp_room_alias']);
	item_update($temp_id, 'room_color', $_POST['temp_room_color']);
  }
}

function get_current_player($game_id,$uid) {
  $sql = sprintf("select replace_id from Replacements where game_id=%s and user_id=%s order by period, number DESC",quote_smart($game_id),quote_smart($uid));
  $result = mysql_query($sql);
  if (mysql_num_rows($result) > 0) { $uid = mysql_result($result,0,0); }
  return $uid;
}

function get_room_names_by_id($game_id) {
  $sql = sprintf("select id, name from Chat_rooms where game_id=%s order by name",quote_smart($game_id));
  $result = mysql_query($sql);
  while ( $row = mysql_fetch_array($result) ) {
    $room_names[$row['id']] = $row['name'];
  }
  return $room_names;  
}

function get_subgame_names_by_id($game_id) {
  $sql = sprintf("select id, title from Games where parent_game_id=%s order by title",quote_smart($game_id));
  $result = mysql_query($sql);
  while ( $row = mysql_fetch_array($result) ) {
    $game_names[$row['id']] = $row['title'];
  }
  return $game_names;  
}

function find_id_for_string($map, $string) {
  if ($map && $string) {
	foreach($map as $id=>$name)
	{
	  if ($string == $id) { return $id; }
	  if ($string == $name) { $namematch = $id; }
	}}
	return $namematch;
}

function get_loc_names_by_id($game_id) {
  $sql = sprintf("select id, name from Locations where game_id=%s order by name",quote_smart($game_id));
  $result = mysql_query($sql);
  while ( $row = mysql_fetch_array($result) ) {
    $loc_names[$row['id']] = $row['name'];
  }
  return $loc_names;  
}

function get_item_names_by_id($game_id) {
  $sql = sprintf("select id, name from Items where game_id=%s order by name",quote_smart($game_id));
  $result = mysql_query($sql);
  while ( $row = mysql_fetch_array($result) ) {
    $item_names[$row['id']] = $row['name'];
  }
  return $item_names;  
}

function get_item_temp_names_by_id($game_id) {
  $sql = sprintf("select id, name from Item_templates where game_id=%s order by name",quote_smart($game_id));
  $result = mysql_query($sql);
  while ( $row = mysql_fetch_array($result) ) {
    $temp_names[$row['id']] = $row['name'];
  }
  return $temp_names;  
}

function get_exit_names_by_id($game_id) {
  $sql = sprintf("select id, name from Exits where game_id=%s order by name",quote_smart($game_id));
  $result = mysql_query($sql);
  while ( $row = mysql_fetch_array($result) ) {
    $exit_names[$row['id']] = $row['name'];
  }
  return $exit_names;  
}

function escape_chars($str) {
  $str = str_replace("'", "\'", $str);
  $str = str_replace("\r\n", "\\r\\n", $str);
  $str = str_replace("\n", "\\n", $str);
  return str_replace('"', '&quot;', $str);
}

function template_script_for_items($temps) {  
  $output = "";
  if ($temps) { 
    foreach ($temps as $temp_id=>$temp_map) {    
      $output .= " if (this.options[this.selectedIndex].value == '$temp_id') {";
      $output .= "  document.add_item_form.item_name.value = '".escape_chars($temp_map['name'])."'; ";
      $output .= "  document.add_item_form.item_description.value = '".escape_chars($temp_map['description'])."'; ";
      $output .= "  document.getElementById('".$temp_map['visibility']."').checked = true; ";
      $output .= "  document.getElementById('".$temp_map['mobility']."').checked = true; ";
      $output .= "  document.add_item_form.item_room_id.value = '".$temp_map['room_id']."'; ";
      $output .= "  document.add_item_form.use_alias.checked = ".($temp_map['room_alias']?"'true'":"0")."; ";
      $output .= "  document.add_item_form.item_room_alias.disabled = ".($temp_map['room_alias']?"0":"'true'")."; ";
      $output .= "  document.add_item_form.item_room_alias.value = '".$temp_map['room_alias']."'; ";
      $output .= "  document.add_item_form.item_room_color.disabled = ".($temp_map['room_alias']?"0":"'true'")."; ";
      $output .= "  document.add_item_form.item_room_color.value = '".$temp_map['room_color']."'; ";
      $output .= " } \n";
    }
  }
  return $output;
}

function display_add_items($game_id) {
  global $_SERVER;
  $players = get_player_loc_info($game_id);
  $p_names = get_all_names_by_ids($game_id);  
  $locs_by_id = get_loc_names_by_id($game_id);
  $temps = get_all_item_temp_info($game_id);  

  $output = "<form name='add_item_form' id='add_item_form' method='post' action='".$_SERVER['PHP_SELF']."' >";  
  $output .= "<table class='forum_table'>\n";
  $output .= "<input type='hidden' name='game_id' value='$game_id' />\n";
  $output .= "<tr><th colspan='2'>Add an Item for this Game</th></tr>\n";  
  $output .= "<tr><td><b>Template:</b></td><td><select id='item_temp_id' name='item_temp_id'  onchange=\"".template_script_for_items($temps)."\"><option value='' selected=1>(Create a new Template)</option>";
  if ($temps) {
		foreach ($temps as $temp_id=>$temp_map) {
  		$output .= "<option value='$temp_id'>".$temp_map['name']."</option>";  	
  }}
  $output .= "</select></td></tr>\n";   
  
  $output .= "<tr><td><b>Item Name:</b></td><td><input type='text' name='item_name' size='30' /></td></tr>\n";
  $output .= "<tr><td><b>Description:</b></td><td><textarea  name='item_description' cols='23' rows='5' /></textarea></td></tr>\n";
  $output .= "<tr><td><b>Visibility:</b></td><td><input type='radio' name='item_visibility' id='obvious' value='obvious' /> Obvious &nbsp;<input type='radio' name='item_visibility' id='hide' value='hide' /> Hidden <br /><input type='radio' name='item_visibility' id='conceal' value='conceal' checked=true /> Concealable &nbsp;<input type='radio' name='item_visibility' id='invis' value='invis' /> Invisible </td></tr>\n";
  $output .= "<tr><td><b>Mobility:</b></td><td><input type='radio' name='item_mobility' id='fixed' value='fixed' /> Fixed &nbsp;<input type='radio' name='item_mobility' id='heavy' value='heavy' /> Heavy <br /><input type='radio' name='item_mobility' id='mobile' value='mobile' /> Mobile &nbsp;<input type='radio' name='item_mobility' id='nonphys' value='nonphys' checked=true /> Non-Physical </td></tr>\n";
  $output .= "<tr><td><b>Owner:</b></td><td><input type='radio' name='item_owner_type' id='item_owner_user' value='user' checked=true>";  
  $output .= "<select name='item_player' onchange='document.getElementById(\"item_owner_user\").checked=true;'>";
  foreach ($players as $pid => $player) {
    $output .= "<option value='$pid'>". $p_names[intval($player['real_id'])] ."</option>";
  }  
  $output .= "</select>&nbsp;<input type='radio' name='item_owner_type' id='item_owner_loc' value='loc'>";  
  
  $output .= "<select name='item_loc' onchange='document.getElementById(\"item_owner_loc\").checked=true;'>";
  if ($locs_by_id) {
    foreach ($locs_by_id as $loc_id => $loc_name) {
      $output .= "<option value='$loc_id'>$loc_name</option>";    
  }}  
  $output .= "</select></td></tr>\n";    
  $output .= "<tr><td><b>Chat room:</b></td><td><select name='item_room_id'><option value='' selected=1>(none)</option>";
  $rooms_by_id = get_room_names_by_id($game_id);
  if ($rooms_by_id) {
    foreach ($rooms_by_id as $room_id => $room_name) {
      $output .= "<option value='$room_id'>$room_name</option>";
  }}
  $output .= "</select><br />";
  $output .= "<input type='checkbox' id='use_alias' name='use_alias' onClick='document.add_item_form.item_room_color.disabled = !document.add_item_form.item_room_color.disabled; document.add_item_form.item_room_alias.disabled = !document.add_item_form.item_room_alias.disabled; document.add_item_form.item_room_alias.value=\"\"' />";
  $output .= "&nbsp;<b>Alias</b>&nbsp;";
  $output .= "<input type='text' id='item_room_alias' name='item_room_alias' value='' size='10' disabled='true' />";
  $output .= "&nbsp;<b>Color</b>&nbsp;";
  $output .= "<input type='text' id='item_room_color' name='item_room_color' value='#000000' size='8' disabled='true' />";
  $output .= "<a href='#' onClick='cp.select(document.add_item_form.item_room_color,\"pick\"); return false;' name='pick' id='pick'><img src='/images/color_pick.gif' border='0' /></a>";
  $output .= "</td></tr>\n";
  $output .= "<tr><td colspan='2' align='center'><input type='submit' name='submit_newitem' value='Submit'></td></tr>\n";
  $output .= "</table>\n";
  $output .= "</form>\n";  
  return $output;  
}

function display_add_item_temps($game_id) {
  global $_SERVER;
  $output .= "<form name='add_temp_form' method='post' action='".$_SERVER['PHP_SELF']."' >";  
  $output .= "<table class='forum_table'>\n";
  $output .= "<input type='hidden' name='game_id' value='$game_id' />\n";
  $output .= "<tr><th colspan='2'>Add an Item Template for this Game</th></tr>\n";  
  $output .= "<tr><td><b>Template Name:</b></td><td><input type='text' name='temp_name' size='30' /></td></tr>\n";
  $output .= "<tr><td><b>Description:</b></td><td><textarea  name='temp_description' cols='23' rows='5' /></textarea></td></tr>\n";
  $output .= "<tr><td><b>Visibility:</b></td><td><input type='radio' name='temp_visibility' id='obvious' value='obvious' /> Obvious &nbsp;<input type='radio' name='temp_visibility' id='hide' value='hide' /> Hidden <br /><input type='radio' name='temp_visibility' id='conceal' value='conceal' checked=true /> Concealable &nbsp;<input type='radio' name='temp_visibility' id='invis' value='invis' /> Invisible </td></tr>\n";
  $output .= "<tr><td><b>Mobility:</b></td><td><input type='radio' name='temp_mobility' id='fixed' value='fixed' /> Fixed &nbsp;<input type='radio' name='temp_mobility' id='heavy' value='heavy' /> Heavy <br /><input type='radio' name='temp_mobility' id='mobile' value='mobile' /> Mobile &nbsp;<input type='radio' name='temp_mobility' id='nonphys' value='nonphys' checked=true /> Non-Physical </td></tr>\n";
  $output .= "<tr><td><b>Chat room:</b></td><td><select name='temp_room_id'><option value='' selected=1>(none)</option>";
  $rooms_by_id = get_room_names_by_id($game_id);
  if ($rooms_by_id) {
    foreach ($rooms_by_id as $room_id => $room_name) {
      $output .= "<option value='$room_id'>$room_name</option>";
  }}
  $output .= "</select><br />";
  $output .= "<input type='checkbox' name='use_alias' onClick='document.add_temp_form.temp_room_color.disabled = !document.add_temp_form.temp_room_color.disabled; document.add_temp_form.temp_room_alias.disabled = !document.add_temp_form.temp_room_alias.disabled; document.add_temp_form.temp_room_alias.value=\"\"' />";
  $output .= "&nbsp;<b>Alias</b>&nbsp;";
  $output .= "<input type='text' id='temp_room_alias' name='temp_room_alias' value='' size='10' disabled='true' />";
  $output .= "&nbsp;<b>Color</b>&nbsp;";
  $output .= "<input type='text' id='temp_room_color' name='temp_room_color' value='#000000' size='8' disabled='true' />";
  $output .= "<a href='#' onClick='cp.select(document.add_temp_form.temp_room_color,\"pick\"); return false;' name='pick' id='pick'><img src='/images/color_pick.gif' border='0' /></a>";
  $output .= "</td></tr>\n";
  $output .= "<tr><td colspan='2' align='center'><input type='submit' name='submit_newtemplate' value='Submit'></td></tr>\n";
  $output .= "</table>\n";
  $output .= "</form>\n";  
  return $output;  
}

function display_add_locs($game_id) {
  global $_SERVER;
  $output = "<form name='add_loc_form' method='post' action='".$_SERVER['PHP_SELF']."' >";
  $output .= "<table class='forum_table'>\n";
  $output .= "<input type='hidden' name='game_id' value='$game_id' />\n";
  $output .= "<tr><th colspan='2'>Add a Location for this Game</th></tr>\n";
  $output .= "<tr><td><b>Location Name:</b></td><td><input type='text' name='loc_name' size='30' /></td></tr>\n";
  $output .= "<tr><td><b>Description:</b></td><td><textarea  name='loc_description' cols='23' rows='5' /></textarea></td></tr>\n";
  $output .= "<tr><td><b>Visibility:</b></td><td><input type='radio' name='loc_visibility' value='Full' checked=true /> Full &nbsp;<input type='radio' name='loc_visibility' value='Search' /> Searchable &nbsp;<input type='radio' name='loc_visibility' value='None' /> None </td></tr>\n";
  $output .= "<tr><td><b>Chat room:</b></td><td><select name='loc_room_id'><option value='' selected=1>(none)</option><option value='new'>(Create a new room)</option>";
  $rooms_by_id = get_room_names_by_id($game_id);
  if ($rooms_by_id) {
    foreach ($rooms_by_id as $room_id => $room_name) {
      $output .= "<option value='$room_id'>$room_name</option>";
  }}
  $output .= "</select></td></tr>\n";
  $output .= "<tr><td><b>Subthread:</b></td><td><select name='loc_subgame_id'><option value='' selected=1>(none)</option>";
  $sub_by_id = get_subgame_names_by_id($game_id);
  if ($sub_by_id) {
    foreach ($sub_by_id as $sub_id => $sub_name) {
      $output .= "<option value='$sub_id'>$sub_name</option>";
  }}
  $output .= "</select></td></tr>\n";

  $output .= "<tr><td><b>Comments:</b></td><td><input type='text' name='loc_comment' size='30' /></td></tr>\n";
 $output .= "<tr><td colspan='2' align='center'><input type='submit' name='submit_newloc' value='Submit'></td></tr>\n";
  $output .= "</table>\n";
  $output .= "</form>\n";
  return $output;
}

function display_add_exits($game_id) {
  global $_SERVER;
  $items_by_id = get_item_temp_names_by_id($game_id);
  
  $output = "<form name='add_exit_form' method='post' action='".$_SERVER['PHP_SELF']."' >";
  $output .= "<table class='forum_table'>\n";
  $output .= "<input type='hidden' name='game_id' value='$game_id' />\n";
  $output .= "<tr><th colspan='2'>Add an Exit for this Game</th></tr>\n";
  $output .= "<tr><td><b>Exit Name:</b></td><td><input type='text' name='exit_name' size='30' /></td></tr>\n";
  $output .= "<tr><td><b>Travel Text:</b></td><td><textarea  name='exit_travel_text' cols='23' rows='5' /></textarea></td></tr>\n";
  if ($items_by_id) {
    $output .= "<tr><td><b>Requires:</b></td><td><select name='exit_temp_id'><option value='' selected=1 >(none)</option>";
    foreach ($items_by_id as $item_id => $item_name) {
      $output .= "<option value='$item_id'>$item_name</option>";
    }
    $output .="</select></td></tr>";
  }  
  
  $output .= "<tr><td><b>Comments:</b></td><td><input type='text' name='exit_comment' size='30' /></td></tr>\n";

  $locs = get_loc_names_by_id($game_id);
  if (count($locs) > 0) {
    $output .= "<tr><td valign='top' colspan='2'>";  
    $output .= "<table border='0' cellpadding='0' cellspacing='1' >\n";
    $output .= "<tr><th align='left'>Applicable Location</th><th>Destination</th></tr>";

    foreach ($locs as $loc_id => $loc_name) {
      $output .= "<tr><td><input type='checkbox' name='loc_$loc_id' /> $loc_name</td>";
      $output .= "<td><select name='dest_$loc_id'>";
      foreach ($locs as $dest_id => $dest_name) {
        $output .= "<option value='$dest_id'>$dest_name</option>";
      }  
      $output .= "</select></td></tr>";
    }
    $output .= "</table>\n";
    $output .= "</td></tr>\n";
  }
  $output .= "<tr><td colspan='2' align='center'><input type='submit' name='submit_newexit' value='Submit'></td></tr>\n";
  $output .= "<tr><td colspan='2' align='center'><input type='submit' name='submit_combexit' value='Make paths for all Pairs of Locations' /></td></tr>\n";
  $output .= "</table>\n";
  $output .= "</form>\n";
  return $output;
}

function display_add_players($game_id) {
  return "";
}

function selected_if_same($val, $str) {
  return ($val == $str ? "selected=1" : "");  
}

function checked_if_same($val, $str) {
  return ($val == $str ? "checked=1" : "");  
}

function display_edit_items($game_id, $item_id) {
  global $_SERVER;
  $item = get_item_info($item_id);
  $template_id = $item['template_id'];
  $players = get_player_loc_info($game_id);
  $p_names = get_all_names_by_ids($game_id);  
  $locs_by_id = get_loc_names_by_id($game_id);  
  $temp_info = get_item_temp_info($template_id);
  
  $output = "<form id = 'edit_item_form' name='edit_item_form' method='post' action='".$_SERVER['PHP_SELF']."' >";  
  $output .= "<table id='mytable' class='forum_table' width='100%'>\n";
  $output .= "<input type='hidden' name='game_id' value='$game_id' />\n";
  $output .= "<input type='hidden' name='item_id' value='$item_id' />\n";
  $output .= "<input type='hidden' name='template_id' value='$template_id' />\n";
  $output .= "<tr><th colspan='2'>Edit Item</th></tr>\n";
  $output .= "<tr><td><b>Item Template:</b></td><td><input type='text' name='template_name' readonly=true size='30' value='". $temp_info['name'] ."' /></td></tr>\n";
  $output .= "<tr><td><b>Item Name:</b></td><td><input type='text' name='item_name' size='30' value='". $item['name'] ."' /></td></tr>\n";
  $output .= "<tr><td><b>Description:</b></td><td><textarea  name='item_description' cols='23' rows='5' />". $item['description'] ."</textarea></td></tr>\n";
  $output .= "<tr><td><b>Visibility:</b></td><td><input type='radio' name='item_visibility' value='obvious' ".checked_if_same('obvious',$item['visibility'])." /> Obvious &nbsp;<input type='radio' name='item_visibility' value='hide' ".checked_if_same('hide',$item['visibility'])." /> Hidden <br /><input type='radio' name='item_visibility' value='conceal' ".checked_if_same('conceal',$item['visibility'])." /> Concealable &nbsp;<input type='radio' name='item_visibility' value='invis' ".checked_if_same('invis',$item['visibility'])." /> Invisible </td></tr>\n";
  $output .= "<tr><td><b>Mobility:</b></td><td><input type='radio' name='item_mobility' value='fixed' ".checked_if_same('fixed',$item['mobility'])." /> Fixed &nbsp;<input type='radio' name='item_mobility' value='heavy' ".checked_if_same('heavy',$item['mobility'])." /> Heavy <br /><input type='radio' name='item_mobility' value='mobile' ".checked_if_same('mobile',$item['mobility'])." /> Mobile &nbsp;<input type='radio' name='item_mobility' value='nonphys' ".checked_if_same('nonphys',$item['mobility'])." /> Non-Physical </td></tr>\n"; 
 $output .= "<tr><td><b>Owner:</b></td><td><input type='radio' name='item_owner_type' value='user' ".checked_if_same('user',$item['owner_type'])." >";  
  $output .= "<select name='item_player' onchange='document.edit_item_form.item_owner_type.value=\"user\"'>";
  foreach ($players as $pid => $player) {
    $output .= "<option value='$pid' ".selected_if_same($pid,$item['owner_ref_id'])." >". $p_names[intval($player['real_id'])] ."</option>";
  }  
  $output .= "</select>&nbsp;<input type='radio' name='item_owner_type' value='loc' ".checked_if_same('loc',$item['owner_type'])." >";  
  
  $output .= "<select name='item_loc' onchange='document.edit_item_form.item_owner_type.value=\"loc\"'>";
  if ($locs_by_id) {
    foreach ($locs_by_id as $loc_id => $loc_name) {
      $output .= "<option value='$loc_id' ".selected_if_same($loc_id,$item['owner_ref_id'])." >$loc_name</option>";    
  }}  
  $output .= "</select></td></tr>\n";
  
  $output .= "<tr><td><b>Chat room:</b></td><td><select name='item_room_id'><option value='' selected=1>(none)</option>";
  $rooms_by_id = get_room_names_by_id($game_id);
  if ($rooms_by_id) {
    foreach ($rooms_by_id as $room_id => $room_name) {
      $output .= "<option value='$room_id' ". selected_if_same($room_id,$item['room_id']).">$room_name</option>";
  }}
  $output .= "</select><br />";
  $output .= "<input type='checkbox' name='use_alias' " . ($item['room_alias']?"checked=true":"") ." onClick='document.edit_item_form.item_room_color.disabled = !document.edit_item_form.item_room_color.disabled; document.edit_item_form.item_room_alias.disabled = !document.edit_item_form.item_room_alias.disabled; document.edit_item_form.item_room_alias.value=\"\"' />";
  $output .= "&nbsp;<b>Alias</b>&nbsp;";
  $output .= "<input type='text' id='item_room_alias' name='item_room_alias' value='".($item['room_alias'])."' size='10' ".($item['room_alias'] ? "" : "disabled='true'")." />";
  $output .= "&nbsp;<b>Color</b>&nbsp;";
  $output .= "<input type='text' id='item_room_color' name='item_room_color' value='".($item['room_color'])."' size='8' ".($item['room_alias'] ? "" : "disabled='true'")." />";
  $output .= "<a href='#' onClick='cp.select(document.edit_item_form.item_room_color,\"pick\"); return false;' name='pick' id='pick'><img src='/images/color_pick.gif' border='0' /></a>";
  $output .= "</td></tr>\n";
  
  $output .= "<tr><td colspan='2' align='center'><input type='submit' name='submit_edititem' value='Submit'><input type='submit' value='Delete' name='delete_item' onClick='warn_delete_phys_submit()' /></td></tr>\n";
  $output .= "</table>\n";
  $output .= "</form>\n";  
  return $output;
}

function display_edit_item_temps($game_id, $temp_id) {
  global $_SERVER;
  $temp = get_item_temp_info($temp_id);
  
  $output = "<form id = 'edit_temp_form' name='edit_temp_form' method='post' action='".$_SERVER['PHP_SELF']."' >";  
  $output .= "<table id='mytable' class='forum_table' width='100%'>\n";
  $output .= "<input type='hidden' name='game_id' value='$game_id' />\n";
  $output .= "<input type='hidden' name='temp_id' value='$temp_id' />\n";
  $output .= "<tr><th colspan='2'>Edit Item Template</th></tr>\n"; 
  $output .= "<tr><td><b>Template Name:</b></td><td><input type='text' name='temp_name' size='30' value='". $temp['name'] ."' /></td></tr>\n";
  $output .= "<tr><td><b>Description:</b></td><td><textarea  name='temp_description' cols='23' rows='5' />". $temp['description'] ."</textarea></td></tr>\n";
  $output .= "<tr><td><b>Visibility:</b></td><td><input type='radio' name='temp_visibility' value='obvious' ".checked_if_same('obvious',$temp['visibility'])." /> Obvious &nbsp;<input type='radio' name='temp_visibility' value='hide' ".checked_if_same('hide',$temp['visibility'])." /> Hidden <br /><input type='radio' name='temp_visibility' value='conceal' ".checked_if_same('conceal',$temp['visibility'])." /> Concealable &nbsp;<input type='radio' name='temp_visibility' value='invis' ".checked_if_same('invis',$temp['visibility'])." /> Invisible </td></tr>\n";
  $output .= "<tr><td><b>Mobility:</b></td><td><input type='radio' name='temp_mobility' value='fixed' ".checked_if_same('fixed',$temp['mobility'])." /> Fixed &nbsp;<input type='radio' name='temp_mobility' value='heavy' ".checked_if_same('heavy',$temp['mobility'])." /> Heavy <br /><input type='radio' name='temp_mobility' value='mobile' ".checked_if_same('mobile',$temp['mobility'])." /> Mobile &nbsp;<input type='radio' name='temp_mobility' value='nonphys' ".checked_if_same('nonphys',$temp['mobility'])." /> Non-Physical </td></tr>\n";   
  $output .= "<tr><td><b>Chat room:</b></td><td><select name='temp_room_id'><option value='' selected=1>(none)</option>";
  $rooms_by_id = get_room_names_by_id($game_id);
  if ($rooms_by_id) {
    foreach ($rooms_by_id as $room_id => $room_name) {
      $output .= "<option value='$room_id' ". selected_if_same($room_id,$temp['room_id']).">$room_name</option>";
  }}
  $output .= "</select><br />";
  $output .= "<input type='checkbox' name='use_alias' " . ($temp['room_alias']?"checked=true":"") ." onClick='document.edit_temp_form.temp_room_color.disabled = !document.edit_temp_form.temp_room_color.disabled; document.edit_temp_form.temp_room_alias.disabled = !document.edit_temp_form.temp_room_alias.disabled; document.edit_temp_form.temp_room_alias.value=\"\"' />";
  $output .= "&nbsp;<b>Alias</b>&nbsp;";
  $output .= "<input type='text' id='temp_room_alias' name='temp_room_alias' value='".($temp['room_alias'])."' size='10' ".($temp['room_alias'] ? "" : "disabled='true'")." />";
  $output .= "&nbsp;<b>Color</b>&nbsp;";
  $output .= "<input type='text' id='temp_room_color' name='temp_room_color' value='".($temp['room_color'])."' size='8' ".($temp['room_alias'] ? "" : "disabled='true'")." />";
  $output .= "<a href='#' onClick='cp.select(document.edit_temp_form.temp_room_color,\"pick\"); return false;' name='pick' id='pick'><img src='/images/color_pick.gif' border='0' /></a>";
  $output .= "</td></tr>\n";
  $output .= "<tr><td></td><td><input type='checkbox' name='item_apply' checked=true /> Apply changes to all existing items with this template.</td></tr>\n";
  
  $output .= "<tr><td colspan='2' align='center'><input type='submit' name='submit_edittemplate' value='Submit'><input type='submit' value='Delete' name='delete_temp' onClick='warn_delete_phys_submit()' /></td></tr>\n";
  $output .= "</table>\n";
  $output .= "</form>\n";  
  return $output;
}

function display_edit_exits($game_id, $exit_id) {
  global $_SERVER;
  $exit = get_exit_info($exit_id);
  $items_by_id = get_item_temp_names_by_id($game_id);

  $output = "<form id='edit_exit_form' name='edit_exit_form' method='post' action='".$_SERVER['PHP_SELF']."' >";
  $output .= "<table id='mytable' class='forum_table' width='100%'>\n";
  $output .= "<input type='hidden' name='game_id' value='$game_id' />\n";
  $output .= "<input type='hidden' name='exit_id' value='$exit_id' />\n";
  $output .= "<tr><th colspan='2'>Edit Exit</th></tr>\n";
  $output .= "<tr><td><b>Exit Name:</b></td><td><input type='text' name='exit_name' size='30' value='". $exit['name'] ."' /></td></tr>\n";
  $output .= "<tr><td><b>Travel Text:</b></td><td><textarea  name='exit_travel_text' cols='23' rows='5' />". $exit['travel_text'] ."</textarea></td></tr>\n";
  if ($items_by_id) {
    $output .= "<tr><td><b>Requires:</b></td><td><select name='exit_temp_id'><option value='' ". selected_if_same('', $exit['template_id']) ." >(none)</option>";
    foreach ($items_by_id as $item_id => $item_name) {
      $output .= "<option value='$item_id' ". selected_if_same("$item_id", $exit['template_id']) ." >$item_name</option>";
    }
    $output .="</select></td></tr>";
  }
  $output .= "<tr><td><b>Comments:</b></td><td><input type='text' name='exit_comment' size='30' value='". $exit['comment'] ."' /></td></tr>\n";

  $locs = get_loc_names_by_id($game_id);
  if (count($locs) > 0) {
    $output .= "<tr><td valign='top' colspan='2'>";
    $output .= "<table border='0' cellpadding='0' cellspacing='1' >\n";
    $output .= "<tr><th align='left'>Applicable Location</th><th>Destination</th></tr>";
  
    $map = get_exit_loc_info_by_exit($exit_id);
    foreach ($locs as $loc_id => $loc_name) {
      $output .= "<tr><td><input type='checkbox' name='loc_$loc_id' ". (($map[$loc_id] != "") ? "checked=1" : "") ." /> $loc_name</td>";
      $output .= "<td><select name='dest_$loc_id'>";
      foreach ($locs as $dest_id => $dest_name) {
        $output .= "<option value='$dest_id' ". (($map[$loc_id] == $dest_id) ? "selected=1" : "") ." >$dest_name</option>";
      }  
      $output .= "</select></td></tr>";
    }
    $output .= "</table>\n";
    $output .= "</td></tr>\n";
  }
  $output .= "<tr><td colspan='2' align='center'><input type='submit' name='submit_editexit' value='Submit'><input type='submit' name='delete_exit' value='Delete' onClick='warn_delete_submit(this)' /></td></tr>\n";
  $output .= "</table>\n";
  $output .= "</form>\n";
  return $output;
}

function display_edit_locs($game_id, $loc_id) {
  global $_SERVER;
  $output = "<form id='edit_loc_form' name='edit_loc_form' method='post' action='".$_SERVER['PHP_SELF']."' >";
  $output .= "<table id='mytable' class='forum_table'>\n";
  $output .= "<input type='hidden' name='loc_id' value='$loc_id' />";
  $output .= "<input type='hidden' name='game_id' value='$game_id' />\n";
  $output .= "<tr><th name='edit_head' colspan='2'>Edit Location</th></tr>\n";
  $loc = get_loc_info($loc_id);
  $output .= "<tr><td><b>Location Name:</b></td><td><input type='text' name='loc_name' size='30' value=\"".$loc['name']."\" /></td></tr>\n";
  $output .= "<input type='hidden' name='created' value='".$loc['created']."' />\n";
  $output .= "<tr><td><b>Description:</b></td><td><textarea  name='loc_description' cols='23' rows='5' />".$loc['description']." </textarea></td></tr>\n";
  $output .= "<tr><td><b>Visibility:</b></td><td><input type='radio' name='loc_visibility' value='Full' ". (('Full'==$loc['visibility']) ? "checked=1" : "") ." /> Full &nbsp;<input type='radio' name='loc_visibility' value='Search' ". (('Search'==$loc['visibility']) ? "checked=1" : "") ." /> Searchable &nbsp;<input type='radio' name='loc_visibility' value='None' ". (('None'==$loc['visibility']) ? "checked=1" : "") ." /> None </td></tr>\n";
  $output .= "<tr><td><b>Chat room:</b></td><td><select name='loc_room_id'><option value='' ". (('NULL'==$loc['room_id']) ? "selected=1" : "") ." >(none)</option><option value='new'>(Create a new room)</option>";
  $rooms_by_id = get_room_names_by_id($game_id);
  if ($rooms_by_id) {
    foreach ($rooms_by_id as $room_id => $room_name) {
    $output .= "<option value='$room_id' ". (($room_id==$loc['room_id']) ? "selected=1" : "") ." >$room_name</option>";
  }}
  $output .= "</select></td></tr>\n";
  $output .= "<tr><td><b>Subthread:</b></td><td><select name='loc_subgame_id'><option value='' ". (('NULL'==$loc['subgame_id']) ? "selected=1" : "") ." >(none)</option>";
  $sub_by_id = get_subgame_names_by_id($game_id);
  if ($sub_by_id) {
    foreach ($sub_by_id as $sub_id => $sub_name) {
      $output .= "<option value='$sub_id' ". (($sub_id==$loc['subgame_id']) ? "selected=1" : "") ." >$sub_name</option>";
  }}
  $output .= "</select></td></tr>\n";

  $output .= "<tr><td><b>Comments:</b></td><td><input type='text' name='loc_comment' size='30' value='".$loc['comment']."' /></td></tr>\n";
  
  $exits = get_exit_names_by_id($game_id);
  $locs = get_loc_names_by_id($game_id);
  if (count($exits) > 0) {
    $output .= "<tr><td valign='top' colspan='2'>";
    $output .= "<table border='0' cellpadding='0' cellspacing='1' >\n";
    $output .= "<tr><th align='left'>Applicable Exit</th><th>Destination</th></tr>";  

    $map = get_exit_loc_info_by_loc($loc_id);
    foreach ($exits as $exit_id => $exit_name) {
      $output .= "<tr><td><input type='checkbox' name='exit_$exit_id' ". (($map[$exit_id] != "") ? "checked=1" : "") ." /> $exit_name</td>";
      $output .= "<td><select name='dest_$exit_id'>";
      foreach ($locs as $dest_id => $dest_name) {
        $output .= "<option value='$dest_id' ". (($map[$exit_id] == $dest_id) ? "selected=1" : "") ." >$dest_name</option>";
      }  
      $output .= "</select></td></tr>";
    }    
    
    $output .= "</table>\n";
    $output .= "</td></tr>\n";      
  }
  
 $output .= "<tr><td colspan='2' align='center'><input type='submit' name='submit_editloc' value='Submit' /><input type='submit' name='delete_loc' value='Delete' onClick='warn_delete_submit(this)' /></td></tr>\n";
  $output .= "</table>\n";
  $output .= "</form>\n";
  return $output;
}
function display_edit_players($game_id, $user_id) {
  return "";
}


function list_items($game_id) {
  $sql = sprintf("select * from Items where game_id=%s order by name", quote_smart($game_id));
  $result = mysql_query($sql);
  $num_items = mysql_num_rows($result);
  $locs_by_id = get_loc_names_by_id($game_id);
  $pnames = get_all_names_by_ids($game_id);
  $rooms_by_id = get_room_names_by_id($game_id);
  
  $output .= "<form id='all_items' name='all_items'>\n";
  $output .= "<table class='forum_table'><tr><th><a href='javascript:add_item_dialog()'><img src='/images/add.png'  border='0' /></a></th><th colspan='2'>Current Items ($num_items)</th></tr>\n";
  
  while ( $item = mysql_fetch_array($result) ) {
    $output .= "<tr>";
    $output .= "<td valign='top'>";
    $output .= "<a href='javascript:edit_item_dialog(\"".$item['id']."\")'><img src='/images/edit.png' border='0' /></a>";
    $output .= "<br />";
    $output .= "<input type='checkbox' name='selected_".$item['id']."' />\n";
    $output .= "</td>";
    $output .= "<td valign='top'>";
    $output .= "<b>".$item['name']."</b>";
    $output .= "<br />Created: ".$item['created'];
    if ($item['description']) {
      $output .= "<br />\"<i>" . $item['description'] . "</i>\"";
    }
    $output .= "</td><td valign='top'>";
    if ($item['owner_type'] == 'user') {
      $real_id = intval(get_current_player($game_id,$item['owner_ref_id']));      
      $output .= "Owned by: " . $pnames[$real_id];
    } else {
      $output .= "At: " .  $locs_by_id[$item['owner_ref_id']];
    }
	if ($item['room_id']) {
		$output .= "<br />Chat room: " . $rooms_by_id[$item['room_id']];
		if ($item['room_alias']) {
		  $output .= "<br />Alias: <span style='color:".$item['room_color'].";'>".$item['room_alias']."</span>";
		}
	}
    $output .= "</td>";
    $output .= "</tr>";
  }
  
  $output .= "</table>\n";
  $output .= "<input type='button' name='delete' value='Delete Selected Items' onClick='delete_selected_items()' >";
  $output .= "</form>\n";
  
  return $output;
}

function list_item_temps($game_id) {
  $sql = sprintf("select * from Item_templates where game_id=%s order by name", quote_smart($game_id));
  $result = mysql_query($sql);
  $num_temps = mysql_num_rows($result);
  $rooms_by_id = get_room_names_by_id($game_id);
  
  $output .= "<form id='all_temps' name='all_temps'>\n";
  $output .= "<table class='forum_table'><tr><th><a href='javascript:add_temp_dialog()'><img src='/images/add.png'  border='0' /></a></th><th colspan='2'>Current Item Templates ($num_temps)</th></tr>\n";
  
  while ( $temp = mysql_fetch_array($result) ) {
    $output .= "<tr>";
    $output .= "<td valign='top'>";
    $output .= "<a href='javascript:edit_temp_dialog(\"".$temp['id']."\")'><img src='/images/edit.png' border='0' /></a>";
    $output .= "<br />";
    $output .= "<input type='checkbox' name='selected_".$temp['id']."' />\n";
    $output .= "</td>";
    $output .= "<td valign='top'>";
    $output .= "<b>".$temp['name']."</b>";
    if ($temp['description']) {
      $output .= "<br />\"<i>" . $temp['description'] . "</i>\"";
    }
    $output .= "</td><td valign='top'>";
	$sql_cnt = sprintf("select count(1) from Items where template_id = %s", quote_smart($temp['id']));
	$result_cnt = mysql_query($sql_cnt);
	$item_cnt = 0;
	if (mysql_num_rows($result_cnt) > 0) { $item_cnt = mysql_result($result_cnt,0,0); }
	$output .= "Items: " . $item_cnt;
	if ($temp['room_id']) {
		$output .= "<br />Chat room: " . $rooms_by_id[$temp['room_id']];
		if ($temp['room_alias']) {
		  $output .= "<br />Alias: <span style='color:".$temp['room_color'].";'>".$temp['room_alias']."</span>";
		}		
	}
    $output .= "</td>";
    $output .= "</tr>";
  }
  
  $output .= "</table>\n";
  $output .= "<input type='button' name='delete' value='Delete Selected Templates' onClick='delete_selected_temps()' >";
  $output .= "</form>\n";
  
  return $output;
}

function list_exits($game_id) {
  $sql = sprintf("select * from Exits where game_id=%s order by name", quote_smart($game_id));
  $result = mysql_query($sql);
  $num_exits = mysql_num_rows($result);
  $map = get_exit_loc_info($game_id);
  $locs_by_id = get_loc_names_by_id($game_id);
  $items_by_id = get_item_temp_names_by_id($game_id);

  $output .= "<form id='all_exits' name='all_exits'>\n";
  $output .= "<table class='forum_table'><tr><th><a href='javascript:add_exit_dialog()'><img src='/images/add.png'  border='0' /></a></th><th colspan='2'>Current Exits ($num_exits)</th></tr>\n";

  while ( $exit = mysql_fetch_array($result) ) {
    $output .= "<tr>";
    $output .= exit_info($exit['id'],$items_by_id);
    if (count($map['by_exit'][$exit['id']]) > 0) {
      $output .= exit_loc_info($exit['id'],$map['by_exit'][$exit['id']],$locs_by_id);
    }
    $output .= "</tr>\n";
  }
  $output .= "</table>\n";
  $output .= "<input type='button' name='delete' value='Delete Selected Exits' onClick='delete_selected_exits()' >";
  $output .= "</form>\n";
  
  return $output;
}

function list_locs($game_id) {
  $sql = sprintf("select * from Locations where game_id=%s order by name", quote_smart($game_id));
  $result = mysql_query($sql);
  $num_locs = mysql_num_rows($result);
  $output .= "<form id='all_locs' name='all_locs'>\n";
  $output .= "<table class='forum_table'><tr><th><a href='javascript:add_loc_dialog()'><img src='/images/add.png'  border='0' /></a></th><th colspan='2'>Current Locations ($num_locs)</th></tr>\n";

  while ( $loc = mysql_fetch_array($result) ) {
    $output .= "<tr>";
    $output .= loc_info($loc['id']);
    $output .= "</tr>\n";
  }
  $output .= "</table>\n";
  $output .= "<input type='button' name='delete' value='Delete Selected Locations' onClick='delete_selected_locs()' >";
  $output .= "</form>\n";

  return $output;
}

function sort_by_name($a, $b) {
  return strcasecmp($p_names[intval($a['real_id'])], $p_names[intval($b['real_id'])]);
}

function list_players_disp($game_id) {
  $players = get_player_loc_info($game_id);
  $locs_by_id = get_loc_names_by_id($game_id);
  $rooms_by_id = get_room_names_by_id($game_id);
  $p_names = get_all_names_by_ids($game_id);
  $item_names = get_all_item_names_by_player($game_id);

  //usort($players, 'sort_by_name');

  $output .= "<form id='edit_player_form' name='edit_player_form' method='post' action='".$_SERVER['PHP_SELF']."' >\n";
  $output .= "<input type='hidden' name='game_id' value='$game_id' />\n";
  $output .= "<table class='forum_table'><tr><th colspan='2'>Current Players (".count($players).")</th></tr>\n";

  if (count($players) > 0) {
    $output .= "<script type=\"text/javascript\">\n";
    $output .= "function reset_moves() {\n";
    foreach ($players as $pid => $player) {
      $output .= "  document.edit_player_form.moves_$pid.value=\"0\";\n";
    }    
    $output .= "}\n";
    $output .= "</script>\n";
    $output .="<tr><th>Name</th><th>Location</th><th>Feedback Chat</th><th>Movements<br /><a href='javascript:reset_moves()'>(Reset)</a></th><th>Movement Limit</th>";
	if ($item_names) {
	  $output .= "<th>Items</th><th>Item Limit</th>";
	 }
	 $output .= "</tr>\n";  
  }

  foreach ($players as $pid => $player) {
    $output .= "<tr><td>". $p_names[intval($player['real_id'])] ."</td>";
    $output .= "<td><select name='ploc_$pid'><option value='' ". (('NULL'==$player['loc_id']) ? "selected=1" : "") ." >(none)</option>";
  if ($locs_by_id) {
    foreach ($locs_by_id as $loc_id => $loc_name) {
    $output .= "<option value='$loc_id' ". (($loc_id==$player['loc_id']) ? "selected=1" : "") ." >$loc_name</option>";
  }}
    $output .= "</select></td>";
    $output .= "<td><select name='pchat_$pid'><option value='' ". (('NULL'==$player['modchat_id']) ? "selected=1" : "") ." >(none)</option>";
  if ($rooms_by_id) {
    foreach ($rooms_by_id as $room_id => $room_name) {
    $output .= "<option value='$room_id' ". (($room_id==$player['modchat_id']) ? "selected=1" : "") ." >$room_name</option>";
  }}
    $output .= "</select></td>";
    $output .= "<td><input type='text' name='moves_$pid' id='moves_$pid' size='3' value='".$player['phys_moves']."' /></td>";
    $output .= "<td><input type='text' name='move_limit_$pid' size='3' value='".$player['phys_move_limit']."' /></td>";
	if ($item_names) {
      $output .= "<td>";
	  if ($item_names[$pid]) {
	    $myitems = $item_names[$pid];
	    foreach ($myitems as $iid => $iname) {
		  $output .= $iname . "<br />";
	  }}
	  $output .= "</td>";
      $output .= "<td><input type='text' name='item_limit_$pid' size='3' value='".$player['phys_item_limit']."' /></td>"; 
    }	  
    $output .= "</tr>\n";
  }

 $output .= "<tr><td colspan='7' align='center'><input type='submit' name='submit_editplayer' value='Submit' /></td></tr>\n";
  $output .= "</table></form>";
  return $output;
}

function list_phys_settings($game_id, $user_id) {
  $output = "";
  $output .= "<form id='edit_config_form' name='edit_config_form' method='post' action='".$_SERVER['PHP_SELF']."' >\n";
  $output .= "<input type='hidden' name='game_id' value='$game_id' />\n";
  $sql = sprintf("select * from Games where id=%s", quote_smart($game_id));
  $result = mysql_query($sql);
  $current = mysql_fetch_array($result);
  $output .= "Limit Movements Per Day: ";
  $output .= "<input type='text' name='move_limit' size='3' value='".$current['phys_move_limit']."' /><br />";
  $output .= "Limit Held Items Per Player: ";
  $output .= "<input type='text' name='item_limit' size='3' value='".$current['phys_item_limit']."' /><br />";
  $output .= "Auto-Reset Movements for Players: <select name='phys_reset_moves'>";
  $output .= "<option value='none' ". selected_if_same('none',$current['phys_reset_moves']) ." >Never</option>";
  $output .= "<option value='dawn' ". selected_if_same('dawn',$current['phys_reset_moves']) ." >At Dawn</option>";
  $output .= "<option value='dusk' ". selected_if_same('dusk',$current['phys_reset_moves']) ." >At Dusk</option>";
  $output .= "</select><br />";
  $output .= "<input type='submit' name='submit_phys_settings' value='Edit Physics Settings'/><br />";  
  $output .= "</form>";
  
  $sql = sprintf("select Games.id id, Games.title title from Games, Moderators where Moderators.game_id = Games.id and Moderators.user_id=%s and Games.status in ('Finished', 'In Progress')",quote_smart($user_id));
  $result = mysql_query($sql);
  if (mysql_num_rows($result) > 0) {
    $output .= "<form id='import_physics_form' name='import_physics_form' method='post' action='".$_SERVER['PHP_SELF']."' >\n";
    $output .= "<input type='hidden' name='game_id' value='$game_id' />\n";  
    $output .= "Import Physics System from a previous game:<br />";
    $output .= "<select name='import_physics'>";
    while ($game = mysql_fetch_array($result)) {
      $output .= "<option value='".$game['id']."'>".$game['title']."</option>";
    }    
    $output .= "</select><br />";
    $output .= "<input type='submit' name='submit_import_phys' value='Import!'/><br />";  
    $output .= "To import the Physics system from someone else's game, request from the moderator to be temporarily added as a co-mod.<br />";    
    $output .= "</form>";    
  }
  return $output;    
}

function list_phys_processing($game_id) {
  $output = "";
  $output .= "<form id='edit_processing_form' name='edit_processing_form' method='post' action='".$_SERVER['PHP_SELF']."' >\n";
  $output .= "<input type='hidden' name='game_id' value='$game_id' />\n";
  
  $sql = sprintf("select * from Physics_processing where game_id=%s and type='movement'",quote_smart($game_id));
  $result = mysql_query($sql);
  if ($current = mysql_fetch_array($result)) {
    $movement_set = 1;
  }
  
  $output .= "<input type='checkbox' name='movement_set' ".($movement_set ? "checked=1" : "")." > Schedule Movement Processing<br />";
  $output .= "Frequency: <select name='move_frequency' onchange='document.edit_processing_form.move_hour.disabled = (document.edit_processing_form.move_frequency.value.indexOf(\"daily\")+1); document.edit_processing_form.move_minute.disabled = (document.edit_processing_form.move_frequency.value.indexOf(\"mins\")+1);' >";
  $output .= "<option value='5daily' ".($current['frequency'] == '5daily' ? "selected=1" : "" )." >Daily (weekdays only)</option>";
  $output .= "<option value='7daily' ".($current['frequency'] == '7daily' ? "selected=1" : "" )." >Daily (7/week)</option>";
  $output .= "<option value='hourly' ".($current['frequency'] == 'hourly' ? "selected=1" : "" )." >Hourly</option>";
  $output .= "<option value='2hourly' ".($current['frequency'] == '2hourly' ? "selected=1" : "" )." >Every 2 Hours</option>";
  $output .= "<option value='30mins' ".($current['frequency'] == '30mins' ? "selected=1" : "" )." >Every Half Hour</option>";
  $output .= "<option value='15mins' ".($current['frequency'] == '15mins' ? "selected=1" : "" )." >Every 15 minutes</option>";
  $output .= "<option value='immediate' ".($current['frequency'] == 'immediate' ? "selected=1" : "" )." >Instant</option>";
  $output .= "</select><br />";
  
  $output .= "Hour: <input type='text' name='move_hour' size='2' value='".($movement_set ? $current['hour'] : '0')."' />";
  $output .= "Minute: <input type='text' name='move_minute' value='".($movement_set ? $current['minute'] : '0')."' size='2'/><br />";
  
  $sql = sprintf("select * from Physics_processing where game_id=%s and type='item'",quote_smart($game_id));
  $result = mysql_query($sql);
  if ($current = mysql_fetch_array($result)) {
    $item_set = 1;
  }

  $output .= "<input type='checkbox' name='item_set' ".($item_set ? "checked=1" : "")." > Schedule Item Processing<br />";
  $output .= "Frequency: <select name='item_frequency' onchange='document.edit_processing_form.item_hour.disabled = (document.edit_processing_form.item_frequency.value.indexOf(\"daily\")+1); document.edit_processing_form.item_minute.disabled = (document.edit_processing_form.item_frequency.value.indexOf(\"mins\")+1)' >";
  $output .= "<option value='5daily' ".($current['frequency'] == '5daily' ? "selected=1" : "" )." >Daily (weekdays only)</option>";
  $output .= "<option value='7daily' ".($current['frequency'] == '7daily' ? "selected=1" : "" )." >Daily (7/week)</option>";
  $output .= "<option value='hourly' ".($current['frequency'] == 'hourly' ? "selected=1" : "" )." >Hourly</option>";
  $output .= "<option value='2hourly' ".($current['frequency'] == '2hourly' ? "selected=1" : "" )." >Every 2 Hours</option>";
  $output .= "<option value='30mins' ".($current['frequency'] == '30mins' ? "selected=1" : "" )." >Every Half Hour</option>";
  $output .= "<option value='15mins' ".($current['frequency'] == '15mins' ? "selected=1" : "" )." >Every 15 minutes</option>";  
  $output .= "<option value='immediate' ".($current['frequency'] == 'immediate' ? "selected=1" : "" )." >Instant</option>";
  $output .= "</select><br />";
  
  $output .= "Hour: <input type='text' name='item_hour' size='2' value='".($item_set ? $current['hour'] : '0')."' />";
  $output .= "Minute: <input type='text' name='item_minute' value='".($item_set ? $current['minute'] : '0')."' size='2'/><br />";
    
  $output .= "<input type='submit' name='submit_schedule' value='Edit Schedule'/><br />";  
  $output .= "<input type='submit' name='submit_process_locs' value='Process Movements Now'/><br />";
  $output .= "<input type='submit' name='submit_process_items' value='Process Items Now'/>";
  $output .= "</form>";
  
  return $output;
}

function exit_info($exit_id,$items_by_id,$page='config') {
  if ($exit_id == 0 ) { return "No Exit Selected"; }
  $sql = sprintf("select * from Exits where id=%s", quote_smart($exit_id));
  $result = mysql_query($sql);
  $exit = mysql_fetch_array($result);

  if ($page == 'config') {
   $output .= "<td valign='top'>";
  }

  $output .= "<a href='javascript:edit_exit_dialog(\"$exit_id\")'><img src='/images/edit.png' border='0' /></a>";
  if ( $page == "config" )  {
    $output .= "<br />";
    $output .= "<input type='checkbox' name='selected_$exit_id' />\n";
    $output .= "</td>";
    $output .= "<td valign='top'>";
  }

  $output .= "<b>".$exit['name']."</b>";
  $output .= "<br />Created: ".$exit['created'];
  if ( $exit['comment'] != "" ) {
    $output .= "<br />" .$exit['comment'];
  }
  if ( $page == "config" ) {
    $output .= "</td><td valign='top'>";
    if ( $exit['travel_text'] != "" ) {
      $output .= $exit['travel_text'] . "<br />";
    }
    if ( $exit['template_id'] != "") {
      $output .= "<i>Associated with: </i>" . $items_by_id[$exit['template_id']];
    }
    $output .= "</td>";
  }

  return $output;
}

function exit_loc_info($exit_id,$map,$loc_names) {
   $output = "<td valign='top'>";
   foreach ($map as $from => $to) {
     $output .= $loc_names[$from] . " -&gt; " . $loc_names[$to] . "<br>";
   }
   $output .= "</td>";
   return $output;
}

function loc_info($loc_id,$page='config') {
  if ($loc_id == 0 ) { return "No Location Selected"; }
  $sql = sprintf("select * from Locations where id=%s", quote_smart($loc_id));
  $result = mysql_query($sql);
  $loc = mysql_fetch_array($result);
  $sql_rooms = sprintf ("select name from Chat_rooms where id=%s", quote_smart($loc['room_id']));
  $result_rooms = mysql_query($sql_rooms);
  if (mysql_num_rows($result_rooms) > 0) {
    $room_row = mysql_fetch_row($result_rooms);
    $room_name = $room_row[0];
  }
  $sql_subthread = sprintf ("select title from Games where id=%s", quote_smart($loc['subgame_id']));
  $result_sub = mysql_query($sql_subthread);
  if (mysql_num_rows($result_sub) > 0) {
    $sub_row = mysql_fetch_row($result_sub);
    $sub_name = $sub_row[0];
  }

  if ($page == 'config') {
   $output .= "<td valign='top'>";
  }

  $output .= "<a href='javascript:edit_loc_dialog(\"$loc_id\")'><img src='/images/edit.png' border='0' /></a>";
  if ( $page == "config" )  {
    $output .= "<br />";
    $output .= "<input type='checkbox' name='selected_$loc_id' />\n";
    $output .= "</td>";
    $output .= "<td valign='top'>";
  }

  $output .= "<b>".$loc['name']."</b>";
  $output .= "<br />Created: ".$loc['created'];
  if ( $loc['comment'] != "" ) {
    $output .= "<br />" .$loc['comment'];
  }
  if ( $loc['description'] != "") {
    $output .= "<br />\"<i>" .$loc['description']."</i>\"";
  }
  if ( $page == "config" ) {
    $output .= "</td><td valign='top'>";
    if ( $loc['room_id'] != "" ) {
      $output .= "Chat room: ".$room_name;
      if ($loc['subgame_id'] != "") {
        $output .= "<br />";
      }
    }
    if ($loc['subgame_id'] != "") {
      $output .= "Subthread: ".$sub_name;
    }
    $output .= "</td>";
  }

  return $output;
}


?>
