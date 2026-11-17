#!/bin/bash
set -euo pipefail

PROG_DIR=/opt/werewolf
PROG=$PROG_DIR/collect_posts_fast.sh
LOCKFILE=/tmp/collect_posts.lock

if [ ! -e "$LOCKFILE" ]; then
	trap 'rm -f "$LOCKFILE"; exit' INT TERM EXIT
	touch "$LOCKFILE"
	echo "[run_collect_posts] Starting at $(date -Is)"
	if [ "${DEBUG_COLLECT:-0}" = "1" ]; then
		echo "[run_collect_posts] DEBUG_COLLECT=1 (tracing enabled)"
		# Use bash -x to trace; collect_posts_fast.sh uses bash features
		bash -x "$PROG"
	else
		"$PROG"
	fi
	echo "[run_collect_posts] Finished at $(date -Is)"
	rm -f "$LOCKFILE"
	trap - INT TERM EXIT
else
	echo "collect_posts is already running"
fi
