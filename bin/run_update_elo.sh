#!/bin/bash

for x in $(seq 1 1505) 
do
	echo $x
	./update_elo.pl	$x
done
