#!/bin/sh
PROG_DIR=/opt/werewolf
MYSQL_PROG="/usr/bin/mysql -h ${MYSQL_HOST} -D ${MYSQL_DATABASE} -u ${MYSQL_USER} -p${MYSQL_PASSWORD} -s"
NAME_PROG=$PROG_DIR/get_bgg_username.pl
ORIGIFS=$IFS


name_sql="SELECT name FROM Users ORDER BY name"
names=`/bin/echo "$name_sql" | $MYSQL_PROG`

IFS=$'\n'
for name in $names
do
	bgg_name=`$NAME_PROG "$name"`
	if [ "$bgg_name" = "" ]
	then
		echo "NOTFOUND $name"
	elif [ "$bgg_name" != "$name" ]
	then
		IFS=$ORIGIFS
		echo "UPDATED $bgg_name"
		echo "UPDATE Users SET name='$bgg_name' WHERE name='$name';" | $MYSQL_PROG
	fi
done
