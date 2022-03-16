<?php

include_once "php/accesscontrol.php";
include_once "php/db.php";
include_once "php/common.php";
include_once "configure_physics_functions.php";

dbConnect();


if ( isset($_POST['submit']) || isset($_POST['cancel'])) {
  $sql = sprintf("select phase from Games where id=%s",quote_smart($_POST['game_id']));
  $result = mysql_query($sql);
  $game_phase = mysql_result($result,0,0);
  if ( $game_phase ==  'night' && $_POST['target_id'] == '0' ) {
    error("Please input the players name now that the game is in night phase rather than the generic 'Daykill Victim' as your target");
  }
  $sql = sprintf("insert into Game_orders (id, user_id, game_id, `desc`, day) values (NULL, %s,%s,%s,%s)",quote_smart($_POST['user_id']),quote_smart($_POST['game_id']),quote_smart($_POST['desc']),quote_smart($_POST['day']));
  $result = mysql_query($sql);
  $order_id = mysql_insert_id();
  $message = "<b>".$_POST['desc']." ";
  if ( isset($_POST['target_id']) ) {
    $sql = sprintf("update Game_orders set target_id=%s where id=%s",quote_smart($_POST['target_id']),quote_smart($order_id));
    $result=mysql_query($sql);
	if ( $_POST['target_id'] != "" ) {
	  if ( $_POST['target_id'] != '0' ) {
        $sql = sprintf("select name from Users where id=%s",quote_smart($_POST['target_id']));
        $result=mysql_query($sql);
        $target = mysql_result($result,0,0);
        $message .= $target." ";
	  } else {
        $message .= "Daykill Victim ";
	  }
	}
  }
  if ( isset($_POST['user_text']) ) {
    $sql = sprintf("update Game_orders set user_text=%s where id=%s",quote_smart($_POST['user_text']),quote_smart($order_id));
	$result = mysql_query($sql);
	$message .= $_POST['user_text'];
  }
  if ( isset($_POST['cancel']) ) {
    $sql = sprintf("update Game_orders set cancel='1' where id=%s",quote_smart($order_id));
    $result=mysql_query($sql);
	$message .= "canceled";
  }
  $message .= "</b>";
  $sql = sprintf("select id from Chat_rooms, Chat_users where Chat_rooms.id=Chat_users.room_id and Chat_rooms.`lock`='Off' and Chat_users.`lock`='Off' and user_id=%s and game_id=%s order by name",quote_smart($_POST['user_id']),quote_smart($_POST['game_id']));
  $result = mysql_query($sql);
  while ( $room = mysql_fetch_array($result) ) {
    if ( isset($_POST['room_'.$room['id']]) ) {
      $sql2 = sprintf("insert into Chat_messages (id, room_id, user_id, message, post_time) values (NULL, %s,%s,%s, now() )",quote_smart($room['id']),quote_smart($_POST['user_id']),quote_smart($message));
	  $result2=mysql_query($sql2);
	}
  }
  if ( isset($_POST['cancel']) ) {
    error("Your Game Order has been canceled.");
  } else {
    error("Your Game Order has been recorded.");
  }
} elseif ( $_REQUEST['action'] == 'lock' || $_REQUEST['action'] == 'unlock' ) {
  # Make sure player submitting the lock is the player being locked.
  if ( $uid != $_REQUEST['user_id'] ) {
    error("You can not lock or unlock this player's actions.");
  }
  $sql = sprintf("select original_id from Players_all where user_id=%s and game_id=%s",quote_smart($_REQUEST['user_id']),quote_smart($_REQUEST['game_id']));
  $result = mysql_query($sql);
  $user_id = mysql_result($result,0,0);
  if ($_REQUEST['action'] == 'lock' ) {
	$lock = "'1'";
	$msg = "Your game actions have been locked.  They will be unlocked at dawn, unless you manually unlock them first.";
  }	else { 
    $lock = "null";
	$msg = "Your game actions have been unlocked.  You may modify your orders, however in order to hasten processing of dawn you must relock them.";
  }	
  $sql = sprintf("update Players set ga_lock=$lock where user_id=%s and game_id=%s",quote_smart($user_id),quote_smart($_REQUEST['game_id']));
  $result = mysql_query($sql);
  error($msg);
} else { # possibly physics orders
  $game_id = $_POST['game_id'];
  $sub_id = $_POST['user_id'];
  $sql = sprintf("select original_id from Players_all where user_id=%s and game_id=%s",quote_smart($sub_id),quote_smart($game_id));
  $result = mysql_query($sql);
  $user_id = mysql_result($result,0,0);  
  $sql = sprintf("select modchat_id from Players where user_id=%s and game_id=%s",quote_smart($user_id),quote_smart($game_id));
  $result = mysql_query($sql);
  if (mysql_num_rows($result) > 0) {
    $modchat = mysql_result($result,0,0);
  }
  
  if ( isset($_POST['submit_move']) || isset($_POST['cancel_move'])) {
    # cancel previous move order
    $sql = sprintf("update Move_orders set status='canceled' where status='active' and user_id=%s and game_id=%s",quote_smart($user_id),quote_smart($game_id));
    $result = mysql_query($sql);
    if (isset($_POST['submit_move'])) {
	  $sql_imm = sprintf("select frequency from Physics_processing where game_id=%s and type='movement'",quote_smart($game_id));
	  $result_imm = mysql_query($sql_imm);
	  $processing = 'none';
	  if (mysql_num_rows($result_imm) > 0) {
	    $processing = mysql_result($result_imm,0,0);
	  }
      if ($modchat != "") {
        $sql = sprintf("select name from Exits where id=%s",quote_smart($_POST['exit']));
        $result = mysql_query($sql);
        $exit_name = mysql_result($result,0,0);
        $sql = sprintf("insert into Chat_messages (id, room_id, user_id, message, post_time) values (NULL, %s,%s,%s, now() )",quote_smart($modchat),quote_smart($sub_id),quote_smart(sprintf("<b>Move: %s</b>",$exit_name)));
        $result = mysql_query($sql);    
      }	  
	  if ($processing == "immediate")
	  {
		$order['user_id'] = $user_id;
		$order['game_id'] = $game_id;
		$order['exit_id'] = $_POST['exit'];
		process_movement_order_ex($game_id, $order);
		error("Your Move Order has been processed.");
	  }
	  else
	  {
		$sql = sprintf("insert into Move_orders (id, user_id, game_id, exit_id) values (NULL, %s, %s, %s)",quote_smart($user_id),quote_smart($game_id),quote_smart($_POST['exit']));
		$result = mysql_query($sql);
		error("Your Move Order has been recorded.");
	  }      
    } else {
      if ($modchat != "") {
        $sql = sprintf("insert into Chat_messages (id, room_id, user_id, message, post_time) values (NULL, %s,%s,%s, now() )",quote_smart($modchat),quote_smart($sub_id),quote_smart("<b>Move order canceled</b>"));
        $result = mysql_query($sql);       
      }
      error("Your Move Order has been canceled.");
    }
  } elseif ( isset($_POST['submit_pickup']) || isset($_POST['cancel_pickup'])) {
    # cancel previous pickup order
    $item_id = $_POST['rem_item'];
    $sql = sprintf("update Item_orders set status='canceled' where status='active' and user_id=%s and game_id=%s and item_id=%s",quote_smart($user_id),quote_smart($game_id),quote_smart($item_id));
    $result = mysql_query($sql);
    if ($modchat != "") {
      $sql = sprintf("select name from Items where id=%s",quote_smart($item_id));
      $result = mysql_query($sql);
      $item_name = mysql_result($result,0,0);
    }
    if (isset($_POST['submit_pickup'])) {
	  $sql_imm = sprintf("select frequency from Physics_processing where game_id=%s and type='item'",quote_smart($game_id));
	  $result_imm = mysql_query($sql_imm);
	  $processing = 'none';
	  if (mysql_num_rows($result_imm) > 0) {
	    $processing = mysql_result($result_imm,0,0);
	  }	
      if ($modchat != "") {
        $sql = sprintf("insert into Chat_messages (id, room_id, user_id, message, post_time) values (NULL, %s,%s,%s, now() )",quote_smart($modchat),quote_smart($sub_id),quote_smart("<b>Pickup $item_name</b>"));
        $result = mysql_query($sql);       
      }
	  if ($processing == "immediate")
	  {
	    $order_b["user_id"] = $user_id;
		$order_b["game_id"] = $game_id;
		$order_b["item_id"] = quote_smart($item_id);
		$order_b["target_id"] = $user_id;
		$order_b["target_type"] = 'user';
		process_item_order_ex($game_id, $order_b);
		error("Your Pickup Order has been processed.");
	  }	  
	  else {
        $sql = sprintf("insert into Item_orders (id, user_id, game_id, item_id, target_id, target_type) values (NULL, %s, %s, %s, %s,'user')",quote_smart($user_id),quote_smart($game_id),quote_smart($item_id),quote_smart($user_id));
        $result = mysql_query($sql);    
        error("Your Pickup Order has been recorded.");
	  }
    } else {     
      if ($modchat != "") {
        $sql = sprintf("insert into Chat_messages (id, room_id, user_id, message, post_time) values (NULL, %s,%s,%s, now() )",quote_smart($modchat),quote_smart($sub_id),quote_smart("<b>Pickup $item_name canceled</b>"));
        $result = mysql_query($sql);       
      }
      error("Your Pickup Order has been canceled.");    
    }
    $result = mysql_query($sql);
  } elseif ( isset($_POST['submit_inv']) || isset($_POST['cancel_inv'])) {
	  $sql_imm = sprintf("select frequency from Physics_processing where game_id=%s and type='item'",quote_smart($game_id));
	  $result_imm = mysql_query($sql_imm);
	  $processing = 'none';
	  if (mysql_num_rows($result_imm) > 0) {
	    $processing = mysql_result($result_imm,0,0);
	  }  
    $inv_sql = sprintf("select * FROM Items where game_id=%s and owner_type='user' and owner_ref_id=%s", quote_smart($game_id), quote_smart($user_id));  
    $inv_result = mysql_query($inv_sql);
    while ($item = mysql_fetch_array($inv_result)) {
      $sql_order = sprintf("update Item_orders set status='canceled' where status='active' and user_id=%s and game_id=%s and item_id=%s",quote_smart($user_id),quote_smart($game_id),quote_smart($item['id']));
      $result_order = mysql_query($sql_order);    
      $item_name = $item['name'];  
      $item_id = $item['id'];
      if (isset($_POST['submit_inv']) && isset($_POST["item_$item_id"]) && $_POST["item_$item_id"] != 'keep') {
        $order = $_POST["item_$item_id"];
        $target_id = $order == 'drop' ? $_POST['loc_id'] : $_POST["target_$item_id"];
        $target_type = $order == 'drop' ? 'loc' : 'user';      
		if ($processing == "immediate") {
	      $order_a["user_id"] = $user_id;
		  $order_a["game_id"] = $game_id;
		  $order_a["item_id"] = quote_smart($item_id);
		  $order_a["target_id"] = $target_id;
		  $order_a["target_type"] = $target_type;
		  process_item_order_ex($game_id, $order_a);
		} else {
          $sql_order = sprintf("insert into Item_orders (id, user_id, game_id, item_id, target_id, target_type) values (NULL, %s, %s, %s, %s, %s)",quote_smart($user_id), quote_smart($game_id),quote_smart($item_id),quote_smart($target_id), quote_smart($target_type));
          $result_order = mysql_query($sql_order);
		}
        if ($modchat != "") {
          $target_name = "";
          if ($order == 'pass') {
		    $target_name = get_alias($game_id, $target_id);
          }
          $sql = sprintf("insert into Chat_messages (id, room_id, user_id, message, post_time) values (NULL, %s,%s,%s, now() )",quote_smart($modchat),quote_smart($sub_id),quote_smart("<b>$order $item_name $target_name</b>"));
          $result = mysql_query($sql);       
        }             
      } else {          
        if ($modchat != "") {
          $sql = sprintf("insert into Chat_messages (id, room_id, user_id, message, post_time) values (NULL, %s,%s,%s, now() )",quote_smart($modchat),quote_smart($sub_id),quote_smart("<b>Keep $item_name</b>"));
          $result = mysql_query($sql);       
        }
      }
    }  
	if ($processing == "immediate") {
	  error("Your Inventory Orders have been processed.");           
	} else {
      error("Your Inventory Orders have been recorded.");           
	}
  } else {
    error("Invalid page request.");
  }
}
?>
