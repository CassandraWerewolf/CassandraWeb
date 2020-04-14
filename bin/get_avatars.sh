#!/bin/sh
PROG_DIR=/opt/werewolf
MYSQL_PROG="/usr/bin/mysql -h ${MYSQL_HOST} -D ${MYSQL_DATABASE} -u ${MYSQL_USER} -p${MYSQL_PASSWORD} -s"
AVATAR_PROG=$PROG_DIR/get_avatar_file.pl
AVATAR_DIR=/home/ww/werewolf/avatars

pid=$$

id_sql="select id from Users where name="
#bio_sql="select u.name from Bio b, Users u where b.avatar is null and u.id = b.user_id order by name;"
bio_sql="select u.name from Bio b, Users u where u.id = b.user_id order by name;"
names=`/bin/echo "$bio_sql" | $MYSQL_PROG`

for name in $names
do
	echo "getting $name"
	tmp_file=$PROG_DIR/${pid}_avatar.tmp
	$AVATAR_PROG $name > $tmp_file

	if [ -s $tmp_file ]
	then
		/bin/cp $tmp_file $AVATAR_DIR/$name.jpg
		id=`/bin/echo "$id_sql '$name';" | $MYSQL_PROG`
		avatar_sql="update Bio set avatar='$name.jpg' where user_id = $id;"
		/bin/echo "$avatar_sql" | $MYSQL_PROG
	fi

	/bin/rm $tmp_file
done
