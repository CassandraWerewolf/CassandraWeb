#!/bin/bash

PROG_DIR=/opt/werewolf
PROG=$PROG_DIR/automod.pl
LOCKFILE=$PROG_DIR/automod.lock

if [ ! -e $LOCKFILE ]; then
	trap "rm -f $LOCKFILE; exit" INT TERM EXIT
	touch $LOCKFILE
	$PROG
	rm $LOCKFILE
	trap - INT TERM EXIT
else
	echo "automod is already running"
fi
