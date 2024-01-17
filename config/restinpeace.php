<?php
return [
	// Included tables or views will be included in the generated schema
	'zzzincluded_tables' => ['users'],
	// Excluded tables or views will be excluded from the generated schema. Will be ignored if include_tables is set.
	'excluded_tables' => [
		'migrations', 'password_resets', 'sqlite_sequence', 'personal_access_tokens', 'failed_jobs',
	],
	// Hidden tables or views will be excluded from the generated schema.
	'hidden_tables' => [
		'evaluation_teacher', 'comment_result',
	],
	'hidden_columns' => [
		'users' => ['password', 'remember_token', ],
	],
	
	'hide_suffixed_views' => true,
	'primary_key_pattern' => '^id$',	// A regex pattern to match primary keys
	'foreign_key_pattern' => '^([a-0-9_]+)_id$',	// A regex pattern to match primary keys
];
