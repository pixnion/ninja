<?php

echo "<h2>Who has access to this dashboard</h2>\n";

$display = " style='display: none'";
if(!$shared_to['user'] && !$shared_to['group']) {
	$display = "";
}
echo "<p class='shared_with_placeholder'$display>Looks like you haven't shared this dashboard yet</p>\n";

echo "<ul class='shared_with_these_entities'>\n";

$unshare_link = LinkProvider::factory()
	->get_url('tac', 'unshare_dashboard');
foreach($shared_to as $type => $entities) {
	foreach($entities as $entity) {
		echo "<li>
			$type
			<span style=''>".html::specialchars($entity)."</span>
			<a
				class='unshare_dashboard'
				href='$unshare_link'
				data-dashboard-id='".html::specialchars($dashboard_id)."'
				data-group_or_user='$type'
				data-name='".html::specialchars($entity)."'
			>X</a>
		</li>\n";
	}
}

echo "</ul>\n";

