<?php

include "common.php";

$game = "290";

print get_game($game);
print "<br />";
print get_game($game,"num");
print "<br />";
print get_game($game,"title");
print "<br />";
print get_game($game,"complex");
print "<br />";
print get_game($game,"full");
print "<br />";
print get_game($game,"post");
print "<br />";
print get_game($game,"mod");
print "<br />";
print get_game($game,"num, title");
print "<br />";
print get_game($game,"complex, title, full");
print "<br />";
print get_game($game,"title, mod");
print "<br />";
print get_game($game,"num, title, post");
print "<br />";
print get_game($game,"mod, post, full, complex, title, num");
print "<br />";


?>
