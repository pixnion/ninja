<?php

// TODO add tests for these autocompletions

$manifest = array_merge_recursive($manifest, array(
	"usergroups" => array(
		array(
			'display_column' => 'name',
			'query' => '[usergroups] name ~~ "%s"'
		)
	),
	"users" => array(
		array(
			'display_column' => 'name',
			'query' => '[users] name ~~ "%s"'
		)
	),
));
