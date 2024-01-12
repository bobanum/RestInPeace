<?php
return [
	'tables' => [
		'comment_result' => [
			'name' => 'comment_result',
			'columns' => [
				'id' => [
					'cid' => 0,
					'name' => 'id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 1,
				],
				'comment_id' => [
					'cid' => 1,
					'name' => 'comment_id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'result_id' => [
					'cid' => 2,
					'name' => 'result_id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'special_value' => [
					'cid' => 3,
					'name' => 'special_value',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'created_at' => [
					'cid' => 4,
					'name' => 'created_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'updated_at' => [
					'cid' => 5,
					'name' => 'updated_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
			],
			'indexes' => [
				'sqlite_autoindex_comment_result_1' => [
					'seq' => 0,
					'name' => 'sqlite_autoindex_comment_result_1',
					'unique' => 1,
					'origin' => 'pk',
					'partial' => 0,
				],
			],
			'primary_key' => [
				0 => 'id',
			],
			'foreign_keys' => [
			],
			'views' => [
			],
		],
		'comments' => [
			'name' => 'comments',
			'columns' => [
				'id' => [
					'cid' => 0,
					'name' => 'id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 1,
				],
				'wording' => [
					'cid' => 1,
					'name' => 'wording',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'abbr' => [
					'cid' => 2,
					'name' => 'abbr',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'details' => [
					'cid' => 3,
					'name' => 'details',
					'notnull' => 0,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'parameter_id' => [
					'cid' => 4,
					'name' => 'parameter_id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'value' => [
					'cid' => 5,
					'name' => 'value',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'relative' => [
					'cid' => 6,
					'name' => 'relative',
					'notnull' => 1,
					'dflt_value' => '\'1\'',
					'pk' => 0,
				],
				'proportional' => [
					'cid' => 7,
					'name' => 'proportional',
					'notnull' => 1,
					'dflt_value' => '\'0\'',
					'pk' => 0,
				],
				'created_at' => [
					'cid' => 8,
					'name' => 'created_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'updated_at' => [
					'cid' => 9,
					'name' => 'updated_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
			],
			'indexes' => [
				'sqlite_autoindex_comments_1' => [
					'seq' => 0,
					'name' => 'sqlite_autoindex_comments_1',
					'unique' => 1,
					'origin' => 'pk',
					'partial' => 0,
				],
			],
			'primary_key' => [
				0 => 'id',
			],
			'foreign_keys' => [
			],
			'views' => [
			],
		],
		'course_semester' => [
			'name' => 'course_semester',
			'columns' => [
				'id' => [
					'cid' => 0,
					'name' => 'id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 1,
				],
				'course_id' => [
					'cid' => 1,
					'name' => 'course_id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'semester_id' => [
					'cid' => 2,
					'name' => 'semester_id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'created_at' => [
					'cid' => 3,
					'name' => 'created_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'updated_at' => [
					'cid' => 4,
					'name' => 'updated_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
			],
			'indexes' => [
				'sqlite_autoindex_course_semester_1' => [
					'seq' => 0,
					'name' => 'sqlite_autoindex_course_semester_1',
					'unique' => 1,
					'origin' => 'pk',
					'partial' => 0,
				],
			],
			'primary_key' => [
				0 => 'id',
			],
			'foreign_keys' => [
			],
			'views' => [
			],
		],
		'courses' => [
			'name' => 'courses',
			'columns' => [
				'id' => [
					'cid' => 0,
					'name' => 'id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 1,
				],
				'code' => [
					'cid' => 1,
					'name' => 'code',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'title' => [
					'cid' => 2,
					'name' => 'title',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'abbr' => [
					'cid' => 3,
					'name' => 'abbr',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'slug' => [
					'cid' => 4,
					'name' => 'slug',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'department_id' => [
					'cid' => 5,
					'name' => 'department_id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'created_at' => [
					'cid' => 6,
					'name' => 'created_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'updated_at' => [
					'cid' => 7,
					'name' => 'updated_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
			],
			'indexes' => [
				'sqlite_autoindex_courses_1' => [
					'seq' => 0,
					'name' => 'sqlite_autoindex_courses_1',
					'unique' => 1,
					'origin' => 'pk',
					'partial' => 0,
				],
			],
			'primary_key' => [
				0 => 'id',
			],
			'foreign_keys' => [
			],
			'views' => [
			],
		],
		'departments' => [
			'name' => 'departments',
			'columns' => [
				'id' => [
					'cid' => 0,
					'name' => 'id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 1,
				],
				'code' => [
					'cid' => 1,
					'name' => 'code',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'name' => [
					'cid' => 2,
					'name' => 'name',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'abbr' => [
					'cid' => 3,
					'name' => 'abbr',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'slug' => [
					'cid' => 4,
					'name' => 'slug',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'school_id' => [
					'cid' => 5,
					'name' => 'school_id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'created_at' => [
					'cid' => 6,
					'name' => 'created_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'updated_at' => [
					'cid' => 7,
					'name' => 'updated_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
			],
			'indexes' => [
				'sqlite_autoindex_departments_1' => [
					'seq' => 0,
					'name' => 'sqlite_autoindex_departments_1',
					'unique' => 1,
					'origin' => 'pk',
					'partial' => 0,
				],
			],
			'primary_key' => [
				0 => 'id',
			],
			'foreign_keys' => [
			],
			'views' => [
			],
		],
		'evaluation_teacher' => [
			'name' => 'evaluation_teacher',
			'columns' => [
				'id' => [
					'cid' => 0,
					'name' => 'id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 1,
				],
				'evaluation_id' => [
					'cid' => 1,
					'name' => 'evaluation_id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'teacher_id' => [
					'cid' => 2,
					'name' => 'teacher_id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'role' => [
					'cid' => 3,
					'name' => 'role',
					'notnull' => 1,
					'dflt_value' => '\'1\'',
					'pk' => 0,
				],
				'created_at' => [
					'cid' => 4,
					'name' => 'created_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'updated_at' => [
					'cid' => 5,
					'name' => 'updated_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
			],
			'indexes' => [
				'sqlite_autoindex_evaluation_teacher_1' => [
					'seq' => 0,
					'name' => 'sqlite_autoindex_evaluation_teacher_1',
					'unique' => 1,
					'origin' => 'pk',
					'partial' => 0,
				],
			],
			'primary_key' => [
				0 => 'id',
			],
			'foreign_keys' => [
			],
			'views' => [
			],
		],
		'evaluations' => [
			'name' => 'evaluations',
			'columns' => [
				'id' => [
					'cid' => 0,
					'name' => 'id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 1,
				],
				'title' => [
					'cid' => 1,
					'name' => 'title',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'description' => [
					'cid' => 2,
					'name' => 'description',
					'notnull' => 0,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'course_id' => [
					'cid' => 3,
					'name' => 'course_id',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'teacher_id' => [
					'cid' => 4,
					'name' => 'teacher_id',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'parameter_id' => [
					'cid' => 5,
					'name' => 'parameter_id',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'created_at' => [
					'cid' => 6,
					'name' => 'created_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'updated_at' => [
					'cid' => 7,
					'name' => 'updated_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
			],
			'indexes' => [
				'sqlite_autoindex_evaluations_1' => [
					'seq' => 0,
					'name' => 'sqlite_autoindex_evaluations_1',
					'unique' => 1,
					'origin' => 'pk',
					'partial' => 0,
				],
			],
			'primary_key' => [
				0 => 'id',
			],
			'foreign_keys' => [
			],
			'views' => [
			],
		],
		'failed_jobs' => [
			'name' => 'failed_jobs',
			'columns' => [
				'id' => [
					'cid' => 0,
					'name' => 'id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 1,
				],
				'uuid' => [
					'cid' => 1,
					'name' => 'uuid',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'connection' => [
					'cid' => 2,
					'name' => 'connection',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'queue' => [
					'cid' => 3,
					'name' => 'queue',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'payload' => [
					'cid' => 4,
					'name' => 'payload',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'exception' => [
					'cid' => 5,
					'name' => 'exception',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'failed_at' => [
					'cid' => 6,
					'name' => 'failed_at',
					'notnull' => 1,
					'dflt_value' => 'CURRENT_TIMESTAMP',
					'pk' => 0,
				],
			],
			'indexes' => [
				'sqlite_autoindex_failed_jobs_1' => [
					'seq' => 0,
					'name' => 'sqlite_autoindex_failed_jobs_1',
					'unique' => 1,
					'origin' => 'pk',
					'partial' => 0,
				],
			],
			'primary_key' => [
				0 => 'id',
			],
			'foreign_keys' => [
			],
			'views' => [
			],
		],
		'group_student' => [
			'name' => 'group_student',
			'columns' => [
				'id' => [
					'cid' => 0,
					'name' => 'id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 1,
				],
				'group_id' => [
					'cid' => 1,
					'name' => 'group_id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'student_id' => [
					'cid' => 2,
					'name' => 'student_id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'created_at' => [
					'cid' => 3,
					'name' => 'created_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'updated_at' => [
					'cid' => 4,
					'name' => 'updated_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
			],
			'indexes' => [
				'sqlite_autoindex_group_student_1' => [
					'seq' => 0,
					'name' => 'sqlite_autoindex_group_student_1',
					'unique' => 1,
					'origin' => 'pk',
					'partial' => 0,
				],
			],
			'primary_key' => [
				0 => 'id',
			],
			'foreign_keys' => [
			],
			'views' => [
			],
		],
		'groups' => [
			'name' => 'groups',
			'columns' => [
				'id' => [
					'cid' => 0,
					'name' => 'id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 1,
				],
				'number' => [
					'cid' => 1,
					'name' => 'number',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'schedule' => [
					'cid' => 2,
					'name' => 'schedule',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'teacher_id' => [
					'cid' => 3,
					'name' => 'teacher_id',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'course_semester_id' => [
					'cid' => 4,
					'name' => 'course_semester_id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'created_at' => [
					'cid' => 5,
					'name' => 'created_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'updated_at' => [
					'cid' => 6,
					'name' => 'updated_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
			],
			'indexes' => [
				'sqlite_autoindex_groups_1' => [
					'seq' => 0,
					'name' => 'sqlite_autoindex_groups_1',
					'unique' => 1,
					'origin' => 'pk',
					'partial' => 0,
				],
			],
			'primary_key' => [
				0 => 'id',
			],
			'foreign_keys' => [
			],
			'views' => [
			],
		],
		'parameters' => [
			'name' => 'parameters',
			'columns' => [
				'id' => [
					'cid' => 0,
					'name' => 'id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 1,
				],
				'parent_id' => [
					'cid' => 1,
					'name' => 'parent_id',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'wording' => [
					'cid' => 2,
					'name' => 'wording',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'description' => [
					'cid' => 3,
					'name' => 'description',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'uuid' => [
					'cid' => 4,
					'name' => 'uuid',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'helpers' => [
					'cid' => 5,
					'name' => 'helpers',
					'notnull' => 1,
					'dflt_value' => '\'\'',
					'pk' => 0,
				],
				'value' => [
					'cid' => 6,
					'name' => 'value',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'created_at' => [
					'cid' => 7,
					'name' => 'created_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'updated_at' => [
					'cid' => 8,
					'name' => 'updated_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
			],
			'indexes' => [
				'sqlite_autoindex_parameters_1' => [
					'seq' => 0,
					'name' => 'sqlite_autoindex_parameters_1',
					'unique' => 1,
					'origin' => 'pk',
					'partial' => 0,
				],
			],
			'primary_key' => [
				0 => 'id',
			],
			'foreign_keys' => [
			],
			'views' => [
			],
		],
		'personal_access_tokens' => [
			'name' => 'personal_access_tokens',
			'columns' => [
				'id' => [
					'cid' => 0,
					'name' => 'id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 1,
				],
				'tokenable_type' => [
					'cid' => 1,
					'name' => 'tokenable_type',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'tokenable_id' => [
					'cid' => 2,
					'name' => 'tokenable_id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'name' => [
					'cid' => 3,
					'name' => 'name',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'token' => [
					'cid' => 4,
					'name' => 'token',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'abilities' => [
					'cid' => 5,
					'name' => 'abilities',
					'notnull' => 0,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'last_used_at' => [
					'cid' => 6,
					'name' => 'last_used_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'expires_at' => [
					'cid' => 7,
					'name' => 'expires_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'created_at' => [
					'cid' => 8,
					'name' => 'created_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'updated_at' => [
					'cid' => 9,
					'name' => 'updated_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
			],
			'indexes' => [
				'sqlite_autoindex_personal_access_tokens_1' => [
					'seq' => 0,
					'name' => 'sqlite_autoindex_personal_access_tokens_1',
					'unique' => 1,
					'origin' => 'pk',
					'partial' => 0,
				],
			],
			'primary_key' => [
				0 => 'id',
			],
			'foreign_keys' => [
			],
			'views' => [
			],
		],
		'results' => [
			'name' => 'results',
			'columns' => [
				'id' => [
					'cid' => 0,
					'name' => 'id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 1,
				],
				'parameter_id' => [
					'cid' => 1,
					'name' => 'parameter_id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'student_id' => [
					'cid' => 2,
					'name' => 'student_id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'value' => [
					'cid' => 3,
					'name' => 'value',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'helpers' => [
					'cid' => 4,
					'name' => 'helpers',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'created_at' => [
					'cid' => 5,
					'name' => 'created_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'updated_at' => [
					'cid' => 6,
					'name' => 'updated_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
			],
			'indexes' => [
				'sqlite_autoindex_results_1' => [
					'seq' => 0,
					'name' => 'sqlite_autoindex_results_1',
					'unique' => 1,
					'origin' => 'pk',
					'partial' => 0,
				],
			],
			'primary_key' => [
				0 => 'id',
			],
			'foreign_keys' => [
			],
			'views' => [
			],
		],
		'schools' => [
			'name' => 'schools',
			'columns' => [
				'id' => [
					'cid' => 0,
					'name' => 'id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 1,
				],
				'name' => [
					'cid' => 1,
					'name' => 'name',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'abbr' => [
					'cid' => 2,
					'name' => 'abbr',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'slug' => [
					'cid' => 3,
					'name' => 'slug',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'address' => [
					'cid' => 4,
					'name' => 'address',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'created_at' => [
					'cid' => 5,
					'name' => 'created_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'updated_at' => [
					'cid' => 6,
					'name' => 'updated_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
			],
			'indexes' => [
				'sqlite_autoindex_schools_1' => [
					'seq' => 0,
					'name' => 'sqlite_autoindex_schools_1',
					'unique' => 1,
					'origin' => 'pk',
					'partial' => 0,
				],
			],
			'primary_key' => [
				0 => 'id',
			],
			'foreign_keys' => [
			],
			'views' => [
			],
		],
		'semesters' => [
			'name' => 'semesters',
			'columns' => [
				'id' => [
					'cid' => 0,
					'name' => 'id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 1,
				],
				'year' => [
					'cid' => 1,
					'name' => 'year',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'label' => [
					'cid' => 2,
					'name' => 'label',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'abbr' => [
					'cid' => 3,
					'name' => 'abbr',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'school_id' => [
					'cid' => 4,
					'name' => 'school_id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'created_at' => [
					'cid' => 5,
					'name' => 'created_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'updated_at' => [
					'cid' => 6,
					'name' => 'updated_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
			],
			'indexes' => [
				'sqlite_autoindex_semesters_1' => [
					'seq' => 0,
					'name' => 'sqlite_autoindex_semesters_1',
					'unique' => 1,
					'origin' => 'pk',
					'partial' => 0,
				],
			],
			'primary_key' => [
				0 => 'id',
			],
			'foreign_keys' => [
			],
			'views' => [
			],
		],
		'students' => [
			'name' => 'students',
			'columns' => [
				'id' => [
					'cid' => 0,
					'name' => 'id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 1,
				],
				'code' => [
					'cid' => 1,
					'name' => 'code',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'first_name' => [
					'cid' => 2,
					'name' => 'first_name',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'last_name' => [
					'cid' => 3,
					'name' => 'last_name',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'slug' => [
					'cid' => 4,
					'name' => 'slug',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'address' => [
					'cid' => 5,
					'name' => 'address',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'user_id' => [
					'cid' => 6,
					'name' => 'user_id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'department_id' => [
					'cid' => 7,
					'name' => 'department_id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'created_at' => [
					'cid' => 8,
					'name' => 'created_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'updated_at' => [
					'cid' => 9,
					'name' => 'updated_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
			],
			'indexes' => [
				'sqlite_autoindex_students_1' => [
					'seq' => 0,
					'name' => 'sqlite_autoindex_students_1',
					'unique' => 1,
					'origin' => 'pk',
					'partial' => 0,
				],
			],
			'primary_key' => [
				0 => 'id',
			],
			'foreign_keys' => [
			],
			'views' => [
			],
		],
		'teachers' => [
			'name' => 'teachers',
			'columns' => [
				'id' => [
					'cid' => 0,
					'name' => 'id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 1,
				],
				'code' => [
					'cid' => 1,
					'name' => 'code',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'first_name' => [
					'cid' => 2,
					'name' => 'first_name',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'last_name' => [
					'cid' => 3,
					'name' => 'last_name',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'slug' => [
					'cid' => 4,
					'name' => 'slug',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'address' => [
					'cid' => 5,
					'name' => 'address',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'user_id' => [
					'cid' => 6,
					'name' => 'user_id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'department_id' => [
					'cid' => 7,
					'name' => 'department_id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'created_at' => [
					'cid' => 8,
					'name' => 'created_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'updated_at' => [
					'cid' => 9,
					'name' => 'updated_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
			],
			'indexes' => [
				'sqlite_autoindex_teachers_1' => [
					'seq' => 0,
					'name' => 'sqlite_autoindex_teachers_1',
					'unique' => 1,
					'origin' => 'pk',
					'partial' => 0,
				],
			],
			'primary_key' => [
				0 => 'id',
			],
			'foreign_keys' => [
			],
			'views' => [
			],
		],
		'test' => [
			'name' => 'test',
			'columns' => [
				'Field1' => [
					'cid' => 0,
					'name' => 'Field1',
					'notnull' => 0,
					'dflt_value' => NULL,
					'pk' => 1,
				],
			],
			'indexes' => [
			],
			'primary_key' => [
				0 => 'Field1',
			],
			'foreign_keys' => [
			],
			'views' => [
			],
		],
		'users' => [
			'name' => 'users',
			'columns' => [
				'id' => [
					'cid' => 0,
					'name' => 'id',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 1,
				],
				'name' => [
					'cid' => 1,
					'name' => 'name',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'email' => [
					'cid' => 2,
					'name' => 'email',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'email_verified_at' => [
					'cid' => 3,
					'name' => 'email_verified_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'password' => [
					'cid' => 4,
					'name' => 'password',
					'notnull' => 1,
					'dflt_value' => NULL,
					'pk' => 0,
				],
				'role' => [
					'cid' => 5,
					'name' => 'role',
					'notnull' => 1,
					'dflt_value' => '\'1\'',
					'pk' => 0,
				],
				'remember_token' => [
					'cid' => 6,
					'name' => 'remember_token',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'created_at' => [
					'cid' => 7,
					'name' => 'created_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
				'updated_at' => [
					'cid' => 8,
					'name' => 'updated_at',
					'notnull' => 0,
					'dflt_value' => 'NULL',
					'pk' => 0,
				],
			],
			'indexes' => [
				'sqlite_autoindex_users_1' => [
					'seq' => 0,
					'name' => 'sqlite_autoindex_users_1',
					'unique' => 1,
					'origin' => 'pk',
					'partial' => 0,
				],
			],
			'primary_key' => [
				0 => 'id',
			],
			'foreign_keys' => [
			],
			'views' => [
			],
		],
	],
	'views' => [
	],
	'updated_at' => 1705023106,
];