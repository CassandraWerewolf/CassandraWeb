#!/bin/sh
PROG_DIR=/opt/werewolf
MYSQL_PROG="/usr/bin/mysql -h ${MYSQL_HOST} -D ${MYSQL_DATABASE} -u ${MYSQL_USER} -p${MYSQL_PASSWORD} -s"
THREAD_PROG=$PROG_DIR/get_thread.py
GET_VOTES_PROG=$PROG_DIR/scan_posts_for_votes.pl
INSERT_VOTES_PROG=$PROG_DIR/insert_votes_db.pl
SHORT_FILE=$PROG_DIR/short.txt
POST_PROG=$PROG_DIR/post_thread_pipe.pl
TALLY_PROG=$PROG_DIR/get_tally.pl
INV_TALLY_PROG=$PROG_DIR/get_tally_inverted.pl
TALLY_FILE=/tmp/tmp_tally.$$
USER='Cassandra Project'
PASS=${BGG_PASSWORD}

pid=$$

game_sql="SELECT game_id from Post_collect_slots p, Games g WHERE p.game_id IS NOT NULL AND g.id = p.game_id AND (p.last_dumped IS NULL OR (TIMESTAMPDIFF(MINUTE, p.last_dumped, NOW()) > 5) OR (TIME_TO_SEC(timediff(TIME(g.lynch_time), CURRENT_TIME())) BETWEEN 245 AND 300));"

game_id=`/bin/echo "$game_sql" | $MYSQL_PROG`

thread_sql="SELECT thread_id FROM Games WHERE id = "
article_sql="SELECT MAX(article_id) FROM Posts WHERE game_id = "
dump_time_sql="update Post_collect_slots set last_dumped = CURRENT_TIMESTAMP() WHERE game_id = "
vt_sql="SELECT auto_vt from Games where id= "
tally_sql="Select updated_tally from Games where id= "
set_tally_sql="Update Games set updated_tally = 0 where id= "

for game in $game_id
do
	votes_file=/tmp/${pid}_${game}_votes.tmp

	thread_id=`/bin/echo "$thread_sql $game;" | $MYSQL_PROG`
	article=`/bin/echo "$article_sql $game;" | $MYSQL_PROG`

	if [ "$article" == "NULL" ]
	then
		article=0
	fi

	ret=`/bin/echo "$dump_time_sql $game;" | $MYSQL_PROG`
	$THREAD_PROG $thread_id $article

	# scan for votes if enabled
	vt=`/bin/echo "$vt_sql $game;" | $MYSQL_PROG`
	if [ "$vt" != "No" ]
	then
		votes_file=/tmp/${pid}_${game}_votes.tmp
		$GET_VOTES_PROG $game $article $SHORT_FILE > $votes_file
		$INSERT_VOTES_PROG $game < $votes_file
		tally=`/bin/echo "$tally_sql $game;" | $MYSQL_PROG`
		if [ $tally -eq 1 ]
		then
			$TALLY_PROG $game $vt > $TALLY_FILE
			echo >> $TALLY_FILE
			echo "---------------------" >> $TALLY_FILE
			echo >> $TALLY_FILE
			$INV_TALLY_PROG $game >> $TALLY_FILE
			$POST_PROG "$USER" $PASS reply $thread_id < $TALLY_FILE
			/bin/echo "$set_tally_sql $game;" | $MYSQL_PROG
			rm $TALLY_FILE
		fi
		/bin/rm $votes_file
	fi
done
