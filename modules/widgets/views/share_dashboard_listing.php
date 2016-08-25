<?php

echo "<h2>Who has access to this dashboard</h2>\n";

if(!$shared_to) {
	echo "<p>Looks like you haven't shared this dashboard yet</p>\n";
	return;
}

echo "<ul class='actionable'>\n";

foreach($shared_to as $user) {
	echo "<li style='display: flex'><span style=''>".html::specialchars($user)."</span> <a href='#' style='width'>X</a></li>\n";
}

echo "</ul>\n";

