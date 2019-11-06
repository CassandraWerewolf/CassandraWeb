<?php
include_once ("autocomplete.php");

function display_menu(){
  global $username;
?>

<script language="javascript">
<!--
function view_menu_player() {
  player = document.getElementById("player_name_menu").value
  pageType = document.getElementById("page_type").value
  if ( pageType == "all_games" ) {
    location.href = "/player/"+player+"/games_played"
  } else {
    location.href = "/"+pageType+"/"+player
  }
}
function view_menu_game() {
  game = document.getElementById("game_id_menu").value
  location.href = "/game/"+game
}
function show_menu_search_form() {
  document.getElementById("menu_search_form_label").style.display = "none";
  document.getElementById("menu_search_form").style.display = "inline-block";
}
//-->
</script>

<nav role="navigation" class="menu">
  <ul class="menu-links">
    <li>
      <a href="/">Cassy Home</a>
    </li><li>
      <a href="http://www.boardgamegeek.com/forum/76/forum/1">BGG WW Forum</a>
    </li><li>
      <a href="javascript:show_menu_search_form()" id="menu_search_form_label">Search</a>
      <span id="menu_search_form" class="menu-form" style="display: none">
        <label>Player:</label>
        <?php print player_autocomplete_form("menu"); print player_autocomplete_js("menu")?>
        <select id="page_type"><option selected value="player">Stats</option><option value="all_games">Games</option><option value="profile">Profile</option><option value="social/user">Social</option></select>
        <a href="javascript:view_menu_player()">Go</a>
      </span>
    </li>
    <!-- 
      <li>
      <table border="0">
        <tr>
          <td>Game:</td>
          <td><?php // print game_autocomplete_form("menu"); print game_autocomplete_js("menu");?></td>
          <td><a href="javascript:view_menu_game()">Go</a></td>
        </tr>
      </table>
      </li>
    -->
  </ul>
  <ul class="menu-profile">
    <?php if ( isset($username) ) { ?>
      <li>
        <strong>Welcome, <a href="/player/<?=$username;?>"><?=$username;?></a></strong>
      </li><li>
        <a href="/logout.php">Log Out</a>
      </li>
    <?php } else { ?>
      <li><a href="/index.php?login=true">Log In</a></li>
    <?php } ?>
  </ul>
</nav>
<?php
}
?>