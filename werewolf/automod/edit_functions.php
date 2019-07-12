<?php

include_once "../php/accesscontrol.php";
include_once "../php/db.php";
include_once "../php/common.php";

dbConnect();

# Array of Roles Automod can handle.
$roles['02'] = "werewolf";
$rules['02'] = "Must put wolves in a group.  This group will be the name of their chat room. And only one wolf from the group need put in kill orders.";
$roles['03'] = "seer";
$rules['03'] = "";
$roles['04'] = "villager";
$rules['04'] = "";
$roles['05'] = "hunter";
$rules['05'] = "Wins if alone with a wolf.";
$roles['06'] = "bodyguard";
$rules['06'] = "Will block night kill target. (Must have guard action)";
$roles['07'] = "priest";
$rules['07'] = "Choose what type of priest in main template section.";
$roles['08'] = "sorcerer";
$rules['08'] = "";
$roles['10'] = "mason";
$rules['10'] = "You must group the masons together for them to be able to chat.";
$roles['13'] = "vigilante";
$rules['13'] = "You must set attribute to brutal.";
$roles['14'] = "witch";
$rules['14'] = "Blocks night game action of target. (Must have block action)";
$roles['15'] = "traitor";
$rules['15'] = "";
$roles['21'] = "wolf cub";
$rules['21'] = "";
$roles['22'] = "cultist";
$rules['22'] = "";
$roles['23'] = "martyr";
$rules['23'] = "Will replace night kill target. (Must have protect action)";
$roles['24'] = "aux seer";
$rules['24'] = "Should be used for seers who see Aux evil rather than wolves.";
$roles['25'] = "white hat";
$rules['25'] = "This player's 'reveal as' field will be used even when the 'Role Reveal' is set to no";
$roles['26'] = "random role seer";
$rules['26'] = "You must still specify view result, but the view choice will be chosen automatically from among those voting for the player.";
$roles['27'] = "latent seer";
$rules['27'] = "You must still set this role up to be promoted to the seer when the seer dies.";

function create_title($template_id,$edit=false,$buttons=true) {
  if ( is_numeric($buttons) ) { $buttons=true; }
  $sql = sprintf("select * from AM_template where id=%s",quote_smart($template_id));
  $result = mysql_query($sql);
  $template = mysql_fetch_array($result);
  if ( $edit ) {
    $output = "<input type='textbox' name='name' value='".$template['name']."' size='50' />\n";
	if ( $buttons ) {
	  $output .= "<input type='submit' name='submit' value='Submit' />\n";
	  $output .= "<input type='button' value='Cancel' onClick='cancel(\"create_title\",\"title_td\",\"$template_id\")' />\n";
	}
  } else {
    $output = "<h1>".$template['name']."</h1>\n";
  }

  return $output;
}

function mode_select($template_id) {
  global $level;
  $sql = sprintf("select mode from AM_template where id=%s",quote_smart($template_id));
  $result = mysql_query($sql);
  $current_mode = mysql_result($result,0,0);
  $sql = sprintf("select count(*) from Games where automod_id=%s and status != 'Finished' ",quote_smart($template_id));
  $result = mysql_query($sql);
  $active_games = mysql_result($result,0,0);
  if ( $current_mode == "Edit" && $active_games > 0 ) {
    return "<p style='color:red;'>ERROR: Please let Melsana or Jmilum know that you have an Automod Template Mode Error.</p>";
  }
  $mode_options = get_enum_array('mode','AM_template');
  # Remove Edit from options if active_games is != 0
  if ( $active_games != 0 ) {
    unset($mode_options['Edit']);
  }
  # Remove Active if game is in Edit mode
  if ( $current_mode == "Edit" ) {
    unset($mode_options['Active']);
  }
  # Remove Test if game is in Edit mode and not a level 1 change
  if ( $current_mode == "Edit" && $level != 1) {
    unset($mode_options['Test']);
  }
  $output .= create_dropdown('mode',$current_mode,$mode_options,"onChange='change_mode(\"$template_id\")'");

  return $output;
}

function create_info_table($template_id,$edit=false) {
  $sql = sprintf("select * from AM_template where id=%s",quote_smart($template_id));
  $result = mysql_query($sql);
  $template = mysql_fetch_array($result);
  $output = "<table class='forum_table' width='75%'>\n";
  $output .= "<tr><td><b>Number of Players</b></td><td>";
  if ( $edit ) {
    $output .= "<input type='text' size='2' name='num_players' value='".$template['num_players']."' />";
  } else {
    $output .= $template['num_players'];
  }
  $output .= "</td></tr>\n";
  $output .= "<tr><td><b>Required from each grouping</b></td><td>\n";
  if ( $edit ) {
    $output .= "This is used for game where not all roles are required.  You specify here how many roles in each set (1,2,3...N) are required. Where N is the number of numbers in the list given here.  For example in a 9 player game where you could have either a sorcerer or a cultist, and either a bodyguard or a martyr, you would set this value to '7,1,1'.  Then in the Role Table you would set Required role group to 1 for each of the standard roles, 2 for the sorcerer and cultist and 3 for bodyguard and martyr. The total must = the number of Players<br />\n"; 
    $output .= "<input type='text' size='10' name='num_player_sets' value='".$template['num_player_sets']."' />";
  } else {
    $output .= $template['num_player_sets'];
  }
  $output .= "</td></tr>\n";
  $output .= "<tr><td><b>Role Reveal</b></td><td>";
  if ( $edit ) {
    $options = get_enum_array('role_reveal','AM_template');
	foreach ( $options as $value ) {
      $options[$value] = expand_role_reveal($value);
	}
	$output .= create_dropdown('role_reveal',$template['role_reveal'],$options);
  } else {
    $output .= expand_role_reveal($template['role_reveal']);
  }
  $output .= "</td></tr>\n";
  $output .= "<tr><td><b>Random N0 View?</b></td><td>";
  if ( $edit ) {
    $options = get_enum_array('random_n0','AM_template');
	$output .= create_dropdown('random_n0',$template['random_n0'],$options);
  } else {
    $output .= $template['random_n0'];
  }
  $output .= "</td></tr>\n";
  $output .= "<tr><td><b>Priest Type?</b></td><td>";
  if ( $edit ) {
    $options = get_enum_array('priest_type','AM_template');
	foreach ( $options as $value ) {
      $options[$value] = expand_priest_type($value);
	}
	$output .= create_dropdown('priest_type',$template['priest_type'],$options);
  } else {
    $output .= expand_priest_type($template['priest_type']);
  }
  $output .= "</td></tr>\n";
  $output .= "<tr><td>";
  $output .= "<b>Possible Villager<br />Attributes (hidden)</b>";
  $output .= "</td><td>";
  $checked = "";
  $nonedit_show = "";
  if ( $template['random_tinker'] == 1 ) { 
    $checked = "checked='checked'"; 
	$nonedit_show .= "Random Tinker <br />";
  }
  if ( $edit ) { $output .= "<input type='checkbox' name='random_tinker' $checked />Random Tinker<br />"; }
  $checked = "";
  if ( $template['random_whitehat'] == 1 ) { 
    $checked = "checked='checked'"; 
	$nonedit_show .= "Random WhiteHat <br />";
  }
  if ( $edit ) { $output .= "<input type='checkbox' name='random_whitehat' $checked />Random WhiteHat<br />"; }
  if ( ! $edit ) { $output .= $nonedit_show; }
  $output .= "</td></tr>\n";
  $output .= "<tr><td valign='top'><b>Description</b><br />Use html tags to format.<br />This is what is shown on the<br />'Add New Automod' page and <br />on the Game page in the <br />description box.</td><td valign='top'>";
  if ( $edit ) {
    $output .= "<textarea name='description' rows='10' cols='50'>";
    $output .= $template['description'];
	$output .= "</textarea>";
  } else {
    $output .= $template['description'];
  }
  $output .= "</td></tr>\n";
  if ( $edit ) {
    $output .= "<tr><td colspan='2'>";
	$output .= "<input type='submit' name='submit' value='Submit' />";
	$output .= "<input type='button' value='Cancel' onClick='cancel(\"create_info_table\",\"info_td\",\"$template_id\")' />\n";
	$output .= "<input type='hidden' name='info' value='info' />";
	$output .= "</td></tr>\n";
  }
  $output .= "</table><br />\n";

  return $output;
}

function expand_role_reveal($role_reveal) {
  switch($role_reveal) {
    case yes:
	  return "Yes";
	break;
	case no:
	  return "No";
	break;
	default:
	  return "Under development (don't use yet)";
	break;
  }
  
  return;
}

function expand_priest_type($priest_type) {
  switch ($priest_type) {
    case "none":
	  return "No Priest in game";
	break;
	case "choose":
	  return "Priest must choose view";
	break;
	case "lynch":
	  return "Priest gets all lynch deaths automatically";
	break;
	case "all":
	  return "Priest gets all deaths automatically";
	break;
	case "passive":
	  return "Passive Priest - Reveal roles as long as priest is alive";
	default:
	  return "Under development (don't use yet)";
	break;
  }

  return;
}

function create_role_table($template_id,$edit=false,$add=0) {
  global $roles, $rules;
  $sql = sprintf("select * from AM_template where id=%s",quote_smart($template_id));
  $result = mysql_query($sql);
  $template = mysql_fetch_array($result);
  $required_groups = count(explode(",",$template['num_player_sets']));
  $output = "";
  if ( $edit ) {
    $output .= "<p style='color:red; width:800px;'><b>Note:</b> If this is your first time editing roles, please read <b>ALL</b> the instructions below the table.  Also you must ask Melsana to take a quick look before the game can be moved to 'Testing' as Cassy won't be forgiving if you mess anything up.</p>";
  }
  $output .= "<table class='forum_table'>\n";
  $output .= "<tr>";
  if ( $edit ) {  
    $output .= "<th>Copy</th>";
    $output .= "<th>Delete</th>"; 
  }
  $output .= "<th>Role ID</th>";
  $output .= "<th>Role</th>";
  $output .= "<th>Side</th>";
  $output .= "<th>Game Action</th>";
  $output .= "<th>Action Description</th>";
  $output .= "<th>Group Name</th>";
  $output .= "<th>N0 Knows</th>";
  $output .= "<th>N0 View</th>";
  $output .= "<th>View Result</th>";
  $output .= "<th>Reveal as</th>";
  $output .= "<th>Attribute</th>";
  $output .= "<th>Parity Count</th>";
  $output .= "<th>Promotion on death of</th>";
  $output .= "<th>Require Role Group</th>";
  $output .= "</tr>\n";
  $sql = sprintf("select * from AM_roles where template_id=%s order by role_id",quote_smart($template_id));
  $result = mysql_query($sql);
  $count = 0;
  $color = "#F5F5FF";
  while ( $role = mysql_fetch_array($result) ) {
    $count++;
	if ( $color == "#F5F5FF" ) {
	  $color = "white";
	} else {
      $color = "#F5F5FF";
	}
	$td_style = "style='background-color:$color;'";
	if ( $edit ) {
	$output .= "<input type='hidden' name='id_$count' value='".$role['id']."' />\n";
	}
    $output .= "<tr>";
    if ( $edit ) {
	  # Copy and Delete
      $output .= "<td valign='top' $td_style>";
	  $output .= "<a href='/automod/copy_role.php?role_id=".$role['id']."'><img src='/images/copy.png' border='0' /></a>";
	  $output .= "</td>";
      $output .= "<td valign='top' $td_style>";
	  $output .= "<input type='checkbox' name='delete_$count' />";
	  $output .= "</td>";
	}
    # Automod Role ID
    $output .= "<td>".$role['id']."</td>";
	# Role id
	$output .= "<td valign='top' $td_style>";
	if ( $edit ) {
      $output .= create_dropdown("role_id_$count",$role['role_id'],$roles);
	} else {
	  $output .= $roles[$role['role_id']];
	}
	$output .= "</td>";
	# Side
	$output .= "<td valign='top' $td_style>";
	if ( $edit ) {
	  $side_options = get_enum_array('side','AM_roles');
	  $output .= create_dropdown("side_$count",$role['side'],$side_options);
	} else {
	  $output .= $role['side'];
	}
	$output .= "</td>";
	# Game Action
	$output .= "<td valign='top' $td_style>";
	if ( $edit ) {
	  $ga_options = get_enum_array('game_action','AM_roles');
	  $output .= create_dropdown("game_action_$count",$role['game_action'],$ga_options);
	} else {
	  $output .= $role['game_action'];
	}
	$output .= "</td>";
	# Action Description
	$output .= "<td valign='top' $td_style>";
	if ( $edit ) {
	  $ad_options[""] = "";
	  $ad_options["Kill"] = "Kill";
	  $ad_options["View"] = "View";
	  $ad_options["Guard"] = "Guard";
	  $ad_options["Protect"] = "Protect";
	  $ad_options["Block"] = "Block";
	  $output .= create_dropdown("action_desc_$count",$role['action_desc'],$ad_options);
	} else {
	  $output .= $role['action_desc'];
	}
	$output .= "</td>";
	#Group Name
	$output .= "<td valign='top' $td_style>";
	if ( $edit ) {
	  $output .= "<input type='text' name='group_name_$count' value='".$role['group_name']."' />";
	} else {
	  $output .= $role['group_name'];
	}
	$output .= "</td>";
	#N0 Knows
	$output .= "<td valign='top' $td_style>";
	if ( $edit ) {
	  $known_roles = split(",",$role['n0_knows']);
	  $role_list['none'] = "none";
	  foreach ( $roles as $id => $name ) {
        $role_list[$id] = $name;
	  }
      $output .= create_dropdown("n0_knows_${count}[]",$known_roles,$role_list,"size='4'",true);
	} else {
      $output .= expand_n0_knows($role['n0_knows']);
    }
	$output .= "</td>";
	#N0 View
	$output .= "<td valign='top' $td_style>";
	if ( $edit ) {
	  $n0_options["random"] = expand_n0_view('random');
	  $n0_options["none"] = expand_n0_view('none');
	  $n0_options["user_choice"] = expand_n0_view('user_choice');
	  $output .= create_dropdown("n0_view_$count",$role['n0_view'],$n0_options,"onChange='show_rand_box(\"$count\")'");
	  $style = "visibility:visible; position:relative;";
	  $selected_roles = split(",",$role['n0_view']);
	  foreach ( $selected_roles as $key => $value ) {
        $selected_roles[$key] = trim($value,"'");
	  }
	  if ( $role['n0_view'] == 'none' || $role['n0_view'] == 'user_choice' ) {
        $style = "visibility:hidden; position:absolute;";
		$selected_roles = "";
	  }
	  $output .= "<div id='rand_choice_$count' style='$style'>";
	  $output .= create_dropdown("n0_view_random_${count}[]",$selected_roles,$roles,"size='4'",true);
	  $output .= "</div>";
	} else {
	  $output .= expand_n0_view($role['n0_view']);
	}
	$output .= "</td>";
	# View Result
	$output .= "<td valign='top' $td_style>";
	if ( $edit ) {
	  $vr_options['on'] = "Positive Hit on:";
	  $vr_options[''] = "No View";
	  $output .= create_dropdown("view_result_$count",$role['view_result'],$vr_options,"onChange='show_positive_box(\"$count\")'");
	  list($look_for,$see_as) = split(" as ",$role['view_result']);
	  $look_choices = split(" or ",$look_for);
	  $style = "visibility:visible; position:relative;";
	  if ( $role['view_result'] == "" ) {
	    $style = "visibility:hidden; position:absolute;";
		$look_choices  = "";
		$see_as = "";
	  }
	  $output .= "<div id='result_$count' style='$style' >";
	  $vr_look_options['all'] = "All";
	  foreach ( $roles as $key => $value ) {
        $vr_look_options[$key] = $value;
	  }
	  $output .= create_dropdown("vr_look_${count}[]",$look_choices,$vr_look_options,"size='4'",true);
	  $output .= "<br />View as:<br />";
	  $vr_see_options['free'] = "Let me Choose";
	  $vr_see_options['role'] = "Player's Role";
	  $vr_see_options['side'] = "Player's Side";
	  foreach ( $roles as $key => $value ) {
        $vr_see_options[$key] = "$value";
	  }
	  $output .= create_dropdown("vr_see_$count",$see_as,$vr_see_options,"onChange='show_free_box(\"vr\",\"vr_see\",\"$count\")'");
	  $is_free = true;
	  if ( $see_as == "" ) { $is_free = false; }
	  foreach ( $vr_see_options as $key => $value ) {
	    if ( $key == "free" ) { continue; }
        if ( $see_as == $key ) { 
		  $is_free = false; 
		  $see_as = "";
		  break;
		}
	  }
	  $style = "visibility:hidden; position:absolute;";
	  if ( $is_free ) { $style = "visibility:visible; position:relative;"; }
	  $output .= "<div id='vr_free_text_$count' style='$style'>";
	  $output .= "<input type='text' name='vr_text_$count' value='$see_as' />";
	  $output .= "</div>";
	  $output .= "</div>";
	} else {
	  $output .= expand_view_result($role['view_result']);
	}
	$output .= "</td>";
	# Reveal as
	$output .= "<td valign='top' $td_style>";
	if ( $edit ) {
	  $list['free'] = "Let me Choose";
      $list['none'] = expand_reveal_as('none');
	  $list['role'] = expand_reveal_as('role');
	  $list['side'] = expand_reveal_as('side');
	  $list['both'] = expand_reveal_as('both');
	  $output .= create_dropdown("reveal_as_${count}",$role['reveal_as'],$list,"onChange='show_free_box(\"ra\",\"reveal_as\",\"$count\")'");
	  $is_free = true;
	  $reveal_as = $role['reveal_as'];
	  foreach ( $list as $key => $value ) {
	    if ( $key == "free" ) { continue; }
	    if ( $reveal_as == $key ) {
	 	  $is_free = false;
		  $reveal_as = "";
		  break;
	    }
	  }
	  $style = "visibility:hidden; position:absolute;";
	  if ( $is_free ) { $style = "visibility:visible; position:relative;"; }
	  $output .= "<div id='ra_free_text_$count' style='$style'>";
	  $output .= "<input type='text' name='ra_text_$count' value='$reveal_as' />";
      $output .= "</div>";
	} else {
      $output .= expand_reveal_as($role['reveal_as']);
	}
	$output .= "</td>";
	# Attribute
	$output .= "<td valign='top' $td_style>";
	if ( $edit ) {
	  $attributes = get_enum_array('attribute','AM_roles');
	  $output .= create_dropdown('attribute_'.$count,$role['attribute'],$attributes);
	  $output .= "<br />";
	  $checked = "";
	  if ( $role['a_hidden'] == 1 ) {
        $checked = "checked='checked'";
	  }
	  $output .= "<input type='checkbox' name='a_hidden_$count' $checked />Hidden";
	} else {
	  $output .= $role['attribute'];
	  if ( $role['a_hidden'] == 1 ) {
        $output .= "<br />(hidden)";
	  }
	}
	$output .= "</td>";
	# Parity
	$output .= "<td valign='top' $td_style>";
	if ( $edit ) {
	  $parity['0'] = "No";
	  $parity['1'] = "Yes";
	  $output .= create_dropdown('parity_'.$count,$role['parity'],$parity);
	} else {
	  if ( $role['parity'] == 1 ) {
        $output .= "Yes";
	  }
	}
	$output .= "</td>";
    # Promotion
    $output .="<td valign='top' $td_style>";
    if ( $edit ) {
	  $promotion_roles = split(",",$role['promotion']);
	  $role_list['none'] = "none";
	  foreach ( $roles as $id => $name ) {
        $role_list[$id] = $name;
	  }
      $output .= create_dropdown("promotion_${count}[]",$promotion_roles,$role_list,"size='4'",true);
      $checked = "";
      if ( $role['promotion_parity'] == "yes" ) { $checked = "checked='checked'"; }
      $output .= "<input type=checkbox name='promotion_parity_${count}[]' $checked />keep current parity status after promotion";
    } else {
      $output .= expand_promotion($role['promotion']);
      if ( $role['promotion_parity'] == "yes" ) { $output .= "(Parity status kept as is upon promotion)"; }
    } 
    $output .="</td>";
	# Require Role
	$output .= "<td valign='top' $td_style>";
	if ( $edit ) {
      for ( $i=1;$i<=$required_groups;$i++) {
        $group_list[$i]=$i;
      }
	  $output .= create_dropdown('require_role_'.$count,$role['require_role'],$group_list);
	} else {
	  $output .= $role['require_role'];
	}
	$output .= "</td>";
	$output .= "</tr>\n";
  }
  if ( $edit ) {
    if  ( $add != 0 ) {
	  for ( $i=1; $i<=$add; $i++ ) {
        $count++;
	    if ( $color == "#F5F5FF" ) {
	      $color = "white";
	    } else {
          $color = "#F5F5FF";
	    }
	    $td_style = "style='background-color:$color;'";
		$output .= "<input type='hidden' name='id_$count' value='new' />\n";
		$output .= "<tr>";
		$output .= "<td valign='top' $td_style>";
		$output .= "</td>";
		$output .= "<td valign='top' $td_style>";
		$output .= "<input type='checkbox' name='delete_$count' />";
		$output .= "</td>";
        $output .= "<td valign='top' $td_style>TBD</td>";
		$output .= "<td valign='top' $td_style>";
	    $output .= create_dropdown("role_id_$count","04",$roles);
	    $output .= "</td>";
		$output .= "<td valign='top' $td_style>";
		$side_options = get_enum_array('side','AM_roles');
		$output .= create_dropdown("side_$count","Good",$side_options);
	    $output .= "</td>";
        $output .= "<td valign='top' $td_style>";
		$ga_options = get_enum_array('game_action','AM_roles');
		$output .= create_dropdown("game_action_$count","none",$ga_options);
	    $output .= "</td>";
        $output .= "<td valign='top' $td_style>";
		$ad_options[""] = "";
		$ad_options["Kill"] = "Kill";
		$ad_options["View"] = "View";
	    $ad_options["Guard"] = "Guard";
		$output .= create_dropdown("action_desc_$count","",$ad_options);
	    $output .= "</td>";
        $output .= "<td valign='top' $td_style>";
		$output .= "<input type='text' name='group_name_$count' value='' />";
	    $output .= "</td>";
        $output .= "<td valign='top' $td_style>";
		$role_list['none'] = "none";
		foreach ( $roles as $id => $name ) {
		  $role_list[$id] = $name;
		}
		$output .= create_dropdown("n0_knows_${count}[]",'none',$role_list,"size='4'",true);
	    $output .= "</td>";
        $output .= "<td valign='top' $td_style>";
		$n0_options["random"] = expand_n0_view('random');
		$n0_options["none"] = expand_n0_view('none');
		$n0_options["user_choice"] = expand_n0_view('user_choice');
		$output .= create_dropdown("n0_view_$count","none",$n0_options,"onChange='show_rand_box(\"$count\")'");
		$style = "visibility:hidden; position:absolute;";
	    $output .= "<div id='rand_choice_$count' style='$style'>";
	    $output .= create_dropdown("n0_view_random_${count}[]","",$roles,"size='4'",true);
	    $output .= "</div>";
        $output .= "</td>";
		$output .= "<td valign='top' $td_style>";
		$vr_options['on'] = "Positive Hit on:";
		$vr_options[''] = "No View";
		$output .= create_dropdown("view_result_$count","",$vr_options,"onChange='show_positive_box(\"$count\")'");
		$style = "visibility:hidden; position:absolute;";
	    $output .= "<div id='result_$count' style='$style' >";
	    $vr_look_options['all'] = "All";
	    foreach ( $roles as $key => $value ) {
	      $vr_look_options[$key] = $value;
	    }
	    $output .= create_dropdown("vr_look_${count}[]","",$vr_look_options,"size='4'",true);
	    $output .= "<br />View as:<br />";
	    $vr_see_options['free'] = "Let me Choose";
        $vr_see_options['role'] = "Player's Role";
	    $vr_see_options['side'] = "Player's Side";
	    foreach ( $roles as $key => $value ) {
	      $vr_see_options[$key] = "$value";
	    }
	    $output .= create_dropdown("vr_see_$count","",$vr_see_options,"onChange='show_free_box(\"vr\",\"$count\")'");
        $is_free = true;
        if ( $see_as == "" ) { $is_free = false; }
        foreach ( $vr_see_options as $key => $value ) {
          if ( $key == "free" ) { continue; }
          if ( $see_as == $key ) {
            $is_free = false;
            $see_as = "";
            break;
          }
        }
        $style = "visibility:hidden; position:absolute;";
        if ( $is_free ) { $style = "visibility:visible; position:relative;"; }
        $output .= "<div id='vr_free_text_$count' style='$style'>";
        $output .= "<input type='text' name='vr_text_$count' value='$see_as' />";
        $output .= "</div>";
        $output .= "</div>";
	    $output .= "</td>";
		$output .= "<td valign='top' $td_style>";
		$list['free'] = "Let me Choose";
		$list['none'] = expand_reveal_as('none');
		$list['role'] = expand_reveal_as('role');
		$list['side'] = expand_reveal_as('side');
		$list['both'] = expand_reveal_as('both');
		$output .= create_dropdown("reveal_as_${count}",'role',$list,"onChange='show_free_box(\"ra\",\"reveal_as\",\"$count\")'");
		$style = "visibility:hidden; position:absolute;";
		$output .= "<div id='ra_free_text_$count' style='$style'>";
		$output .= "<input type='text' name='ra_text_$count' value='$reveal_as' />";
		$output .= "</div>";
	    $output .= "</td>";
		$output .= "<td valign='top' $td_style>";
		$attributes = get_enum_array('attribute','AM_roles');
		$output .= create_dropdown('attribute_'.$count,'',$attributes);
		$output .= "<br />";
		$output .= "<input type='checkbox' name='a_hidden_$count' />Hidden";
	    $output .= "</td>";
		$output .= "<td valign='top' $td_style>";
		$parity['0'] = "No";
		$parity['1'] = "Yes";
		$output .= create_dropdown('parity_'.$count,'0',$parity);
	    $output .= "</td>";
        $output .= "<td valign='top' $td_style>";
		$role_list['none'] = "none";
		foreach ( $roles as $id => $name ) {
		  $role_list[$id] = $name;
		}
		$output .= create_dropdown("promotion_${count}[]",'none',$role_list,"size='4'",true);
        $output .= "<input type=checkbox name='promotion_parity_${count}[]' />keep current parity status after promotion"; 
	    $output .= "</td>";
		$output .= "<td valign='top' $td_style>";
        for ( $i=1;$i<=$required_groups;$i++) {
          $group_list[$i]=$i;
        }
		$output .= create_dropdown('require_role_'.$count,'0',$group_list);
	    $output .= "</td>";
	    $output .= "</tr>\n";
	  }
	}
    $output .= "<tr><td colspan='16'> ";
	$output .= "<input type='hidden' id='count' name='count' value='$count' />";
	$output .= "<input type='button' name='add' value='Add Role' onClick='add_role(\"$template_id\")' /> ";
	$output .= "<input type='submit' name='submit' value='Submit' /> ";
	$output .= "<input type='button' value='Cancel' onClick='cancel(\"create_role_table\",\"role_td\",\"$template_id\")' />\n";
	$output .= "</td></tr>";
  }
  $output .= "</table><br />\n";
  if ( $edit ) {
    $output .= "<div style='width:800px;'>";
	$output .= "<b style='color:red;'>Note:</b> You MUST copy any roles you want to duplicate before adding or editing any other roles (you can only copy rows that have already been saved), and you MUST add as many roles as you want before you beging editing any roles as any changes you make are not saved until you hit 'Submit'</br>";
    $output .= "<b>Roles:</b> The roles available are the only roles Automod knows how to handle at this time.  If there is a role you would like added please let Melsana know.<br />\n";
	$output .= "<ul>";
	foreach ( $rules as $role => $rule ) {
      $output .= "<li>$roles[$role]";
	  if ( $rule != "" ) {
        $output .= "- $rule";
	  }
	  $output .= "</li>\n";
	}
	$output .= "</ul>\n";
	$output .= "<b>Side:</b> Cassandra needs you to tell her that wolves (ect.) are evil and seers (etc.) are good for the game page table, but when declaring the winner, she will make this assumption.<br />";
	$output .= "<b>Game Action:</b> If the player has a game action you need to specify the player list that should be available for the player to use to submitorders with.  If you don't want the priest to beable to 'cheat' by geting views of living players you must set this to 'dead'<br />";
	$output .= "<b>Action Description:</b> Currently Automod can only handel Kill, View, Guard, Protect, and Block orders.  If you would like something added please let Melsana know.<br />";
	$output .= "<b>Group Name:</b>  If players are given the same group name they will be able to see eachother's orders.  They will also be put in a Chat room withat name. Please do not use 'Dead Chat' as the name of a group, as that chat room is reserved for dead players.  All players with Night Kills, MUST have a chat room.<br />";
	$output .= "<b>NO Knows:</b> This lets you choose if the player will learn the idenity of any of the other roles.  Forexample you could allow the Wolves to know the idenity of the wolfcubs, or visa versa.<br />";
	$output .= "<b>N0 View:</b> This must mesh with the choice you have chosen in the main template table.  If you choose 'Random but not:' you must select all roles that are excluded from the random night view, this inclues the role of the viewer, otherwise their random view may be of themselves.  Use the control button to select more than one role.<br />";
	$output .= "<b>View Result:</b> If the player gets a view this must be set to 'Positive Hit on:'.  Then you must choose which roles will be considered positive hits.  Then you must choose what sort of result the viewer gets.  If the role viewed is postive then the result will be 'X is a Y' where X is the player targeted and Y is the 'View as:' field.  If the role viewed is a negative hit then the result will be 'X is not a Y'.  <br />";
	$output .= "Example 1:  Seer should be 'Positive Hit on:', 'werewolf', View as: 'werewolf'.  That will make a werewolf show up as a werewolf and all other roles show up as not a werewolf.  <br />";
	$output .= "Example 2:  Priest should be 'Positive Hit on', 'all', View as: 'Player's Role'.  That will make it show the player's role no matter who is targeted. <br />";
	$output .= "Note: If you choose 'Player's Role' or 'Player's Side' for what to reveal the negative will return 'You can not determine X's Role' or 'You can not determine X's Side' .<br />";
	$output .= "If you choose 'Let me Choose' you can specify your own phrase for the positive hit.  (ie, the Y in the above instructions.)<br />";
	$output .= "Note: If you choose 'Priest Type' above as 'gets (lynch) deaths automatcially' you still need to specify they View Result for the Priest.<br />";
	$output .= "<b>Reveal as:</b>  This lets you adjust per-role what is revealed on their death.  If 'No' is selected in the top table, then this won't matter except for a white hat or if you have a passive priest.<br />";
	$output .= "<b>Attribute:</b> This lets you specify if the role is Tough, Brutal (can't be hidden), Tinker, or White Hat.  Tough will have to be killed twice, Brutal will get to choose a player to take down when killed (The order command will be created automatically.)  Tinker will make it so that all views are reversed,  If it would have been positive it will now be negative if it would have been negative it will now be positive.  White Hat means the players 'Reveal as' information will be revealed on death regardles.<br />";
	$output .= "<b>Parity Count:</b>  These are the roles the code will count to see if Parity has been reached.  All wolves should have a 'Yes', you can choose if sorcerers or wolfcubs etc are counted towards parity.<br />";
    $output .= "<b>Promotion on Death of:</b> This allows you set this player to be promotable if a player with the given role(s) is killed.  That player will now have all the current abilities of that role.  Parity can be set to remain as it currently is set, or promote to be the same as the new role.<br />";
	$output .= "<b>Require Role:</b>This will be a drop down of numbers equal to the number of sets created in the 'Required from each grouping' row in the info table.  So if you have it set to (7,1,1) as per the example, you will have the option to put the roles into set 1, 2, or 3.  7 roles will be taken from set 1, 1 role will be taken from set 2, and 1 role will be taken from set 3.<br />";
	$output .= "<b>Special Notes:</b> If you use a Passive priest or a Random Role Seer you don't need an Action Description or Game Action but you will need a View Result.  <br />";
  }
  
  return $output;
}

function expand_n0_knows($know) {
  global $roles;
  switch($know) {
    case 'none':
	  return "None";
	break;
	default:
	  $list = split(",",$know);
	  $output = "Will learn the id of: <br />";
	  $count = 0;
	  foreach ( $list as $r ) {
	    if ( $count > 0 ) { $output .= ", "; }
        $output .= $roles[$r];
		$count++;
	  }
	  return $output;
	break;
  }
  return;
}

function expand_n0_view($view) {
  global $roles;
  switch($view) {
    case 'none':
	  return "No N0 view";
	break;
	case 'user_choice':
	  return "Player must choose N0 view";
	break;
	default:
	  $list = split(",",$view);
	  $output = "Random but Not: ";
	  $count = 0;
	  foreach ( $list as $r ) {
	    $r = trim($r,"'");
	    if ( $count > 0 ) { $output .= ", "; }
        $output .= $roles[$r];
		$count++;
	  }
	  return $output;
	break;
  }
  return;
}

function expand_view_result($view) {
  global $roles;
  if ( $view == "" ) { return ""; }
  list($look_for,$see_as) = split(" as ",$view);
  $look_choices = split(" or ",$look_for);
  $output = "Positive Hit on: ";
  $count = 0;
  foreach ( $look_choices as $look ) {
    switch($look) {
      case 'all':
        $output .= "Any player ";
	  break;
	  default:
	    if ( $count > 0 ) { $output .= " or "; }
		$output .= $roles[$look];
		$count++;
	  break;
	}
  }
  $output .= "<br /> Result is: ";
  if ($see_as == 'role' ) {
    $output .= "Player's Role";
  } elseif ($see_as == 'side' ) {
    $output .= "Player's Side";
  } elseif ( is_Numeric($see_as) ) {
    $output .= $roles[$see_as];
  } else {
    $output .= "$see_as";
  }

  return $output;
}

function expand_reveal_as($reveal) {
  global $roles;
  switch($reveal) {
    case 'none':
	  return "Not Revealed";
	break;
	case 'role':
	  return "Role";
	break;
	case 'side':
	  return "Side";
	break;
    case 'both':
      return "Role and Side";
    break;
	default:
	  return $reveal;
	break;
  }
}

function expand_promotion($promote) {
  global $roles;
  switch($promote) {
    case 'none':
	  return "None";
	break;
	default:
	  $list = split(",",$promote);
	  $count = 0;
	  foreach ( $list as $r ) {
	    if ( $count > 0 ) { $output .= ", "; }
        $output .= $roles[$r];
		$count++;
	  }
	  return $output;
	break;
  }
  return;
}

function get_ruleset($template_id,$edit=false) {
  $file = "rulesets/${template_id}_ruleset.txt";
  if ( file_exists($file) ) {
    $rules = file_get_contents($file);
	if ( ! $edit ) {
	  $rules = preg_replace('/</','&lt;',$rules);
	  $rules = preg_replace('/>/','&gt;',$rules);
	}
  } else {
    if ( $edit ) { 
	  $rules = "";
	} else {
      $rules = "No ruleset found for this template.";
	}
  }
  if ( $edit ) {
    $output = "<p>Please use BBG formating as this will be the first post Cassy post when the game is created.<br />";
    $output .= "The following will be replaced by user input when the game is created:<br />";
    $output .= "&lt;lynch&gt; - Lynch Deadline <br />";
    $output .= "&lt;night&gt; - Nigth Action Deadline <br />";
    $output .= "&lt;tiebreaker&gt; - Tie Breaker Type <br />";
    $output .= "<b>IMPORTANT:</b> You can't use \" you must use ' instead.<br /></p>";
    $output .= "<textarea name='my_rules' rows='25' cols='84'>";
    $output .= $rules;
    $output .= "</textarea>";
    $output .= "<br />";
    $output .= "<input type='submit' name='submit' value='Submit'>";
    $output .= "<input type='button' value='Cancel' onClick='cancel(\"get_ruleset\",\"ruleset\",\"$template_id\")' />\n";
  } else {
    $output .= $rules;
  }

  return $output;
}

function update_name($name,$template_id) {
  $name = safe_html($name);
  $sql = sprintf("update AM_template set name=%s where id=%s",quote_smart($name),quote_smart($template_id));
  $result = mysql_query($sql);

  return;
}

function update_info($data,$template_id) {
  $random_tinker = 0;
  $random_whitehat = 0;
  if ( isset($data['random_tinker']) ) { $random_tinker = 1; }
  if ( isset($data['random_whitehat']) ) { $random_whitehat = 1; }
  $data['description'] = safe_html($data['description']);
  $sql = sprintf("update AM_template set description=%s, num_players=%s, num_player_sets=%s, role_reveal=%s, random_n0=%s, priest_type=%s, random_tinker=%s, random_whitehat=%s where id=%s",quote_smart($data['description']),quote_smart($data['num_players']),quote_smart($data['num_player_sets']),quote_smart($data['role_reveal']),quote_smart($data['random_n0']),quote_smart($data['priest_type']),quote_smart($random_tinker),quote_smart($random_whitehat),quote_smart($template_id));
  $result=mysql_query($sql);

  return;

}

function update_rules($rules,$template_id) {
  $rules = safe_html($rules,"<lynch><night><tiebreaker>");
  $file = "rulesets/${template_id}_ruleset.txt";
  file_put_contents($file,$rules);

  return;
}

function update_roles($count,$data,$template_id) {
  for ( $i=1; $i<=$count; $i++ ) {
    if ( isset ( $data['delete_'.$i] )) {
      $sql = sprintf("delete from AM_roles where id=%s",quote_smart($data['id_'.$i]));
	  $result = mysql_query($sql);
	  continue;
	}
    $role_id = $data['role_id_'.$i];
	$side = $data['side_'.$i];
	$game_action = $data['game_action_'.$i];
	$action_desc = $data['action_desc_'.$i];
	if ( $action_desc == "" ) {
      $action_desc = 'null';
	} else {
      $action_desc = quote_smart($action_desc);
	}
	$group_name = $data['group_name_'.$i];
	if ( $group_name == "" ) {
      $group_name = 'null';
	} else {
	  $group_name = safe_html($group_name);
      $group_name = quote_smart($group_name);
	}
	$n0_knows = $data['n0_knows_'.$i];
	if ( $n0_knows == "none" ) {
	  $n0_knows = 'none';
	} else {
	  $n0_knows = "";
	  $c=0;
	  foreach ($data['n0_knows_'.$i] as $r_id ) {
		if ( $c > 0 ) { $n0_knows .= ","; }
		$n0_knows .= "$r_id";
		$c++;
	  }
	}
	$n0_view = $data['n0_view_'.$i];
	if ( $n0_view == "random" ) {
	   $n0_view = "";
	   $c=0;
       foreach ($data['n0_view_random_'.$i] as $r_id ) {
	     if ( $c > 0 ) { $n0_view .= ","; }
         $n0_view .= "'$r_id'";
		 $c++;
	   }
	}
	$view_result = $data['view_result_'.$i];
	if ( $view_result == "" ) {
      $view_result = 'null';
	} else {
      $view_result = "";
	  $c = 0;
	  foreach ( $data['vr_look_'.$i] as $look ) {
        if ( $c > 0 ) { $view_result .= " or "; }
		$view_result .= $look;
		$c++;
	  }
	  $view_result .= " as ";
	  if ( $data['vr_see_'.$i] == "free" ) {
	    $data['vr_text_'.$i] = safe_html($data['vr_text_'.$i]);
        $view_result .= $data['vr_text_'.$i];
	  } else {
        $view_result .= $data['vr_see_'.$i];
	  }
	  $view_result = quote_smart($view_result);
	}
	$reveal_as = $data['reveal_as_'.$i];
	if ( $reveal_as == 'free' ) {
	  $data['ra_text_'.$i] = safe_html($data['ra_text_'.$i]);
      $reveal_as = $data['ra_text_'.$i];
	}
	$attribute = $data['attribute_'.$i];
	$a_hidden = 0;
	if ( isset($data['a_hidden_'.$i]) ) { $a_hidden =1; }
	$parity = $data['parity_'.$i];
	$promotion = $data['promotion_'.$i];
	if ( $promotion == "none" ) {
	  $promotion = 'none';
	} else {
	  $promotion = "";
	  $c=0;
	  foreach ($data['promotion_'.$i] as $r_id ) {
		if ( $c > 0 ) { $promotion .= ","; }
		$promotion .= "$r_id";
		$c++;
	  }
	}
    $promotion_parity="no";
	if ( isset($data['promotion_parity_'.$i]) ) { $promotion_parity ="yes"; }
	$require_role = $data['require_role_'.$i];
    if ( $data['id_'.$i] == 'new' ) {
	  $sql = sprintf("insert into AM_roles (id, template_id, role_id, side, game_action, action_desc, group_name, n0_knows, n0_view, view_result, reveal_as, attribute, a_hidden, parity, promotion, promotion_parity, require_role) VALUES ( null, %s, %s, %s, %s, $action_desc, $group_name, '$n0_knows', %s, $view_result, %s, %s, %s, %s, '$promotion', %s, %s)",quote_smart($template_id),quote_smart($role_id),quote_smart($side),quote_smart($game_action),quote_smart($n0_view),quote_smart($reveal_as),quote_smart($attribute),quote_smart($a_hidden),quote_smart($parity),quote_smart($promotion_parity),quote_smart($require_role));
	  $result = mysql_query($sql);
	} else {
	  $id = $data['id_'.$i];
	  $sql = sprintf("update AM_roles set role_id=%s, side=%s, game_action=%s, action_desc=$action_desc, group_name=$group_name, n0_knows='$n0_knows', n0_view=%s, view_result=$view_result, reveal_as='$reveal_as', attribute=%s, a_hidden=%s, parity=%s, promotion='$promotion', promotion_parity=%s, require_role=%s where id=%s",quote_smart($role_id),quote_smart($side),quote_smart($game_action),quote_smart($n0_view),quote_smart($attribute),quote_smart($a_hidden),quote_smart($parity),quote_smart($promotion_parity),quote_smart($require_role),quote_smart($id));
	  $result = mysql_query($sql);
	}
  }
  return;
}

?>
