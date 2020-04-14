#!/bin/sh
PROG_DIR=/opt/werewolf
MYSQL_PROG="/usr/bin/mysql -h ${MYSQL_HOST} -D ${MYSQL_DATABASE} -u ${MYSQL_USER} -p${MYSQL_PASSWORD} -s"
NAME_PROG=$PROG_DIR/check_cassy_username_on_bgg.pl

pid=$$

username_sql="select name from Users order by name;"
delete_sql="delete from Users where name="
names=`/bin/echo "$username_sql" | $MYSQL_PROG`

for name in $names
do
	echo "checking $name"
	res=`$NAME_PROG $name`

	if [ ! -n $res ]
	then
		echo "\t$name does not exist and will be deleted"
		#/bin/echo "$delete_sql $name" | $MYSQL_PROG
	fi
done
