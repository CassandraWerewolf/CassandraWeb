#!/bin/sh
PROG_DIR=/opt/werewolf
MYSQL_PROG="/usr/bin/mysql -h ${MYSQL_HOST} -D ${MYSQL_DATABASE} -u ${MYSQL_USER} -p${MYSQL_PASSWORD} -s"

game=$1

update_sql="update Post_collect_slots set last_dumped=null where game_id=$game;"

/bin/echo "$update_sql" | $MYSQL_PROG
