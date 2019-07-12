var j_vote_start = "\n[b][vote ";
var j_vote_end = "][/b]\n";

var j_tas = document.getElementsByTagName('textarea');
var j_ta = j_tas[0];

var j_name = prompt("Who do you want to vote for?");

j_ta.value += j_vote_start + j_name + j_vote_end; 



