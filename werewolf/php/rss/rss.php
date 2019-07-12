<?php
require('rss_fetch.inc');

function rss($url)
{
  $url = str_replace(" ", "+", $url);
  list( $rss, $status, $msg) = fetch_rss( $url );

	$html .=  "<table cellpadding=2 bgcolor=#efefef><tr>";
	$html .=   "<td bgcolor=#efefef>";
	# get the channel title and link properties off of the rss object
	#
	$title = $rss->channel['title'];
	$link = $rss->channel['link'];
	
	$html .=   "<a href=$link target=_blank><font size=2><b>$title</b></font></a>";
	$html .=   "</td></tr>";
	# foreach over each item in the array.
	# displaying simple links
	#
	# we could be doing all sorts of neat things with the dublin core
	# info, or the event info, or what not, but keeping it simple for now.
	#
	foreach ($rss->items as $item ) {
		$html .=   "<tr><td bgcolor=#ffffff>";
		$html .=   "<a href=$item[link] target=_blank style='{font-weight:normal;text-decoration:none}'>";
		$html .=   $item[title];
		$html .=   "</a></td></tr>";
	}		
	$html .=   "</table>";
  return $html;
}
?>
