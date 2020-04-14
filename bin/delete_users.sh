#!/bin/sh
PROG_DIR=/opt/werewolf
MYSQL_PROG="/usr/bin/mysql -h ${MYSQL_HOST} -D ${MYSQL_DATABASE} -u ${MYSQL_USER} -p${MYSQL_PASSWORD} -s"
ORIGIFS=$IFS


post_sql="SELECT count(*) FROM Posts p, Users u WHERE u.id=p.user_id AND u.name="
names=`cat $1`

IFS=$'\n'
for name in $names
do
	IFS=$ORIGIFS
	posts=`echo "$post_sql '$name';" | $MYSQL_PROG`

	if [ $posts -eq 0 ]
	then
		echo "deleting $name";
		echo "DELETE FROM Users WHERE name='$name';" | $MYSQL_PROG
	fi

done
