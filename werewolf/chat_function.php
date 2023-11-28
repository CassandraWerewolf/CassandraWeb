<?php

include_once "php/db.php";
$mysql = dbConnect();

function show_chatRooms($game_id,$user_id) {
//Get room id's for displaying.
  $output = "";
  global $start_room_id, $start_room_name;
  $sql = sprintf("select id, name from Chat_rooms, Chat_users where Chat_rooms.id=Chat_users.room_id and game_id=%s and user_id=%s",quote_smart($game_id),quote_smart($user_id));
  $result = mysqli_query($mysql, $sql);
  while ( $row = mysqli_fetch_array($result) ) {
    $room_ids[] = $row['id'];
    $room_names[$row['id']] = $row['name'];
    $sql2 = sprintf("select count(*) from Chat_messages where room_id=%s",quote_smart($row['id']));
    $result2 = mysqli_query($mysql, $sql2);
    $num_messages[$row['id']] = mysqli_result($result2,0,0);
    $sql2 = sprintf("select count(*) from Chat_messages, Chat_users where Chat_messages.room_id=Chat_users.room_id and Chat_messages.room_id=%s and Chat_users.user_id=%s and post_time > last_view",quote_smart($row['id']),quote_smart($uid));    $result2 = mysqli_query($mysql, $sql2);
    $new_messages[$row['id']] = mysqli_result($result2,0,0);
  }
  foreach ( $room_names as $room_id => $room_name ) {
    $output .= "<a href='javascript:change_rooms(\"$room_id\",\"$room_name\")'>$room_name</a> (".$num_messages[$room_id].")<br />\n";
    if ( $new_messages[$room_id] != 0 ) {
      $output .= "&nbsp;&nbsp;&nbsp;(new:".$new_messages[$room_id].")<br />";
    }
    $sql2 = sprintf("select name, color, if(last_view>(date_sub(now(), interval 3 second)),'(online)','') as available from Chat_users, Users where Chat_users.user_id=Users.id and room_id=%s order by name",quote_smart($room_id));
    $result2 = mysqli_query($mysql, $sql2);
    while ( $row = mysqli_fetch_array($result2) ) {
      $output .= "&nbsp;&nbsp;&nbsp;<span style='color: ".$row['color'].";'>".$row['name']."</span> ".$row['available']."<br />";
    }
  }
  $start_room_id = $room_ids[0];
  $start_room_name = $room_names[$start_room_id];
                                                                                
  return $output;
}

