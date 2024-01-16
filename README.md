
# RestInPeace: Database-Driven REST API Module with HATEOAS Support
> ATTENTION: This project is still in development and is not ready for production use. Part of this documentation has been create by the [OpenAPI Generator](https://openapi-generator.tech) project.

Introducing RestInPeace, a powerful and versatile PHP module designed to streamline the creation of RESTful APIs by seamlessly analyzing your database structure and establishing intelligent relationships and entry points. This framework-agnostic solution empowers developers to effortlessly expose their database resources through a RESTful interface, all while adhering to the principles of HATEOAS (Hypermedia As The Engine Of Application State).

## Key Features:

1. **Database Intelligence:**
   - RestInPeace dynamically analyzes your database schema, identifying tables, relationships, and data types automatically.
   - Provides a comprehensive understanding of your data model, ensuring accurate representation in the API.

2. **Automatic Relation Mapping:**
   - Establishes relationships between different database entities without manual intervention.
   - Recognizes foreign keys and generates links between associated resources, enhancing the API's navigability.

3. **Resource Exposure:**
   - Effortlessly exposes database tables as RESTful resources, allowing seamless CRUD (Create, Read, Update, Delete) operations.
   - Supports customizable resource naming conventions for tailored API endpoints.

4. **HATEOAS Support:**
   - Implements HATEOAS principles by enriching API responses with hypermedia links.
   - Clients can dynamically discover and navigate the API structure through links, fostering a self-descriptive and discoverable API architecture.

5. **Smart Entry Points:**
   - Automatically generates intelligent entry points based on database structure, allowing clients to explore the API effortlessly.
   - Ensures a straightforward onboarding process for developers unfamiliar with the database schema.

6. **Customization Options:**
   - Offers configuration options for developers to fine-tune API behavior according to specific project requirements.
   - Enables the inclusion or exclusion of specific fields, relationships, or resources based on project needs.

7. **Security Measures:**
   - Implements standard security practices to protect against common API vulnerabilities.
   - Supports authentication and authorization mechanisms to control access to sensitive data.

8. **Framework Agnostic:**
   - Designed to seamlessly integrate with various PHP frameworks or used as a standalone module, ensuring flexibility and compatibility with different project setups.

## Get Started with RestInPeace:
   - Simple installation process with minimal setup requirements.
   - Comprehensive documentation and examples to facilitate quick integration and customization.

RestInPeace takes the complexity out of building REST APIs by leveraging your database structure intelligently, fostering a developer-friendly environment, and adhering to HATEOAS principles for enhanced discoverability. Boost your development productivity and create robust, self-descriptive APIs effortlessly with RestInPeace.

## Installation

Certainly! If your RestInPeace module is available via Composer, you can easily install it using the following steps:

## Step 1: Create a New or Navigate to an ExistinProject**

Ensure you have a PHP project set up where you want to integrate RestInPeace. If you don't have an existing project, create a new one using your preferred structure.

## Step 2: Open a Terminal or Command Prompt

Navigate to the root directory of your PHP project using the terminal or command prompt.

## Step 3: Run Composer Require Command

Use the following Composer command to install RestInPeace:

```bash
composer require bobanum/rest-in-peace
```

Composer will automatically fetch the RestInPeace module and its dependencies and integrate it into your project.

## Step 4: Configure RestInPeace

Depending on the design of RestInPeace, it might require some configuration. Check the module's documentation for any configuration steps or required settings. Configuration may involve specifying database connection details, API routing, or other customization options.

## Step 5: Autoload the Composer Dependencies

Ensure that you have autoloaded the Composer dependencies in your project. If your project doesn't already have an autoload file (usually named `vendor/autoload.php`), make sure to include it in your project files:

```php
require_once __DIR__ . '/vendor/autoload.php';
```

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

