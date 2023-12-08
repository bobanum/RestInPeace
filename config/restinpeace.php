<?php
return [
	// Included tables or views will be included in the generated schema
	'zzzincluded_tables' => ['users'],
	// Excluded tables or views will be excluded from the generated schema. Will be ignored if include_tables is set.
	'excluded_tables' => [
		'migrations', 'password_resets', 'sqlite_sequence',
	],
];
