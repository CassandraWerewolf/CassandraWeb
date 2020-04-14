#!/bin/sh
PROG_DIR=/opt/werewolf
MYSQL_PROG="/usr/bin/mysql -h ${MYSQL_HOST} -D ${MYSQL_DATABASE} -u ${MYSQL_USER} -p${MYSQL_PASSWORD} -s"

game=$1

posts_sql="delete from Posts where game_id=$game;"
votes_sql="delete from Votes where game_id=$game;"
tally_sql="delete from Tally where game_id=$game;"
game_sql="update Games set phase='night', day=0 where id=$game;"
player_sql="update Players set death_phase=null, death_day=null where game_id=$game;"
update_sql="update Post_collect_slots set last_dumped=null where game_id=$game;"

/bin/echo "$posts_sql" | $MYSQL_PROG
/bin/echo "$votes_sql" | $MYSQL_PROG
/bin/echo "$tally_sql" | $MYSQL_PROG
/bin/echo "$game_sql" | $MYSQL_PROG
/bin/echo "$player_sql" | $MYSQL_PROG
/bin/echo "$update_sql" | $MYSQL_PROG
