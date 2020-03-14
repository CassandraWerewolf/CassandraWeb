#!/bin/bash

PROG_DIR=/opt/werewolf
PROG=$PROG_DIR/collect_posts_fast.sh
LOCKFILE=/tmp/collect_posts.lock

if [ ! -e $LOCKFILE ]; then
	trap "rm -f $LOCKFILE; exit" INT TERM EXIT
	touch $LOCKFILE
	$PROG
	rm $LOCKFILE
	trap - INT TERM EXIT
else
	echo "collect_posts is already running"
fi
