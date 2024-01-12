## De MySQL vers SQLITE

1- Enlever les directives du début et de la fin, incluant le COMMIT;
1- Enlever le PRIMARY_KEY
1- Enlever les KEY... et UNIQUE KEY... (attention à la virgule)
1- Ajouter? les AUTOINCREMENT dans les PRIMARY_KEY
1- Enlever les ENGINE=... (attention au point virgule)
1- Remplacer les \' par des ''
1- Enlever les ADD CONSTRAINT...

```sql
CREATE TABLE `test` (
	`Field1`	INTEGER,
	PRIMARY KEY(`Field1` AUTOINCREMENT)
)
```

```sql
CREATE TABLE `comments` (
	`id`	bigint UNSIGNED NOT NULL,
	`wording`	varchar(255) NOT NULL,
	`abbr`	varchar(255) DEFAULT NULL,
	`details`	text,
	`parameter_id`	bigint UNSIGNED NOT NULL,
	`value`	decimal(8, 2) DEFAULT NULL,
	`relative`	tinyint(1) NOT NULL DEFAULT '1',
	`proportional`	tinyint(1) NOT NULL DEFAULT '0',
	`created_at`	timestamp DEFAULT NULL,
	`updated_at`	timestamp DEFAULT NULL,
	PRIMARY KEY(`id`),
	FOREIGN KEY(`parameter_id`) REFERENCES `parameters`(`id`) on delete cascade
)


  KEY `comments_parameter_id_foreign` (`parameter_id`)
devient
  FOREIGN KEY(`parameter_id`) REFERENCES `parameters`(`id`) on delete cascade
```

