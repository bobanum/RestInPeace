# REST IN PEACE (RIP)

## Setting `.env`
Parameters start with `RESTINPEACE_` or `RIP_` and are in uppercase. The following parameters are available:

|Parameter|Description|Default|
|---|---|---|
|CLIENTS|Allowed IP addresses to access the application. Comma separated. Leave blank to allow all IP addresses.||
|APP_PATH|Path to the application. Leave blank to use `DOCUMENT_ROOT`. Relative paths are allowed, and are relative to `DOCUMENT_ROOT`|`.`|
|DATABASE_PATH|Path to the database. Leave blank to use `database`. Relative paths are allowed, and are relative to `APP_PATH`|`database`|
|CONFIG_PATH|Path to the configuration file. Leave blank to use `config`. Relative paths are allowed, and are relative to `APP_PATH`|`config`|
|LOGS_PATH|Path to the log file. Leave blank to use `logs`. Relative paths are allowed, and are relative to `APP_PATH`|`logs`|
|LOGS_LEVEL|Log level. Available values are `DEBUG`, `INFO`, `WARNING`, `ERROR` and `CRITICAL`|`INFO`|
|SCHEMA_CACHE|Duration in seconds to cache the schema. Negative values disable caching.|`86400` (1&nbsp;day)|
KEEP_ALL_VIEWS|Keep all views in the schema.|`false`|

## Using Database Views
<!-- Database views are not included in the schema by default. To include them, set `KEEP_ALL_VIEWS` to `true`. -->
if `KEEP_ALL_VIEWS` is set to `true`, all views are included in the schema. Otherwise, only views without suffixe are exposed.

### The suffixes
To create a suffixes view for a table, create a view with the same name as the table, but with the suffix preceded by a double underscore (`__`).

### The schema cache
- Are located in the `config` folder.
- Are named `schema.<database>.php`.
- Are cached for 1 day by default.

## The config file (`config/restinpeace.php`);

|Parameter|Description|Default|
|---|---|---|
|include_tables|An array of __tables__ or __views__ will be included in the generated schema, Empty array means all tables and views will be included. When set, `excluded_tables` will be ignored.|`[]`|
|excluded_tables|An array of __tables__ or __views__ will be excluded from the generated schema. Will be ignored if `include_tables` is set.|`[]`|
|hidden_tables|An array of __tables__ or __views__ will be included in the schema but will be hidden from the entry points (router).|`[]`|
|keep_all_views|Keep all views in the schema and entry points.|`false`|
|primary_key_pattern|A regex pattern to match primary keys|`^id$`|
|foreign_key_pattern|A regex pattern to match foreign keys|`^([a-0-9_]+)_id$`|


## Nomenclature

ALL SINGULAR or ALL PLURAL or MIXED

