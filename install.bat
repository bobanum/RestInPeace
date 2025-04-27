@echo off

@REM Check if the option -f is passed
IF "%~1"=="-f" (
    set FORCE=true
    shift
) else (
    set FORCE=false
)
set DIR_NAME=%~1
IF "%~1%"=="" (
    set /p DIR_NAME=Enter "api" directory name [default: api]: 
    IF "%DIR_NAME%"=="" set DIR_NAME=api
) else (
    set DIR_NAME=%~1
    shift
)

IF exist "%DIR_NAME%" (
    echo Directory "%DIR_NAME%" already exists.
    GOTO :END_DIR_NAME
)
echo Creating directory "%DIR_NAME%"...
mkdir "%DIR_NAME%"
:END_DIR_NAME

IF exist "%DIR_NAME%/.htaccess" IF "%FORCE%"=="false" (
    echo File "%DIR_NAME%/.htaccess" already exists.
    GOTO :END_HTACCESS
) 
echo Creating file "%DIR_NAME%/.htaccess"...
> "%DIR_NAME%/.htaccess" (
    echo ^<IfModule mod_rewrite.c^>
    echo    RewriteEngine	On
    echo    RewriteCond		%{REQUEST_FILENAME}	!-d
    echo    RewriteCond		%{REQUEST_FILENAME}	!-f
    echo    RewriteRule		^^^(.*^)$ index.php/$1	[L,QSA]
    echo ^</IfModule^>
)
:END_HTACCESS

IF exist "%DIR_NAME%/index.php" IF "%FORCE%"=="false" (
    echo File "%DIR_NAME%/index.php" already exists.
    GOTO :END_INDEX
)

echo Creating file "%DIR_NAME%/index.php"...
> "%DIR_NAME%/index.php" (
    echo ^<?php
    echo include_once '../vendor/bobanum/restinpeace/src/debug.php';
    echo include_once '../vendor/autoload.php';
    echo // RestInPeace::guard(^);
    echo.
    echo include '../routes.php';
)
:END_INDEX

set /p CONFIG_DIR=Enter "config" directory name [default: config]: 
IF "%CONFIG_DIR%"=="" set CONFIG_DIR=config
IF exist "%CONFIG_DIR%" (
    echo Directory "%CONFIG_DIR%" already exists.
    GOTO :END_CONFIG_DIR
)
echo Creating directory "%CONFIG_DIR%"...
mkdir "%CONFIG_DIR%"
:END_CONFIG_DIR

IF exist "%CONFIG_DIR%/restinpeace.php" IF "%FORCE%"=="false" (
    echo File "%CONFIG_DIR%/restinpeace.php" already exists.
    GOTO :END_RESTINPEACE
)
echo Creating file "%CONFIG_DIR%/restinpeace.php"...
> "%CONFIG_DIR%/restinpeace.php" (
    echo ^<?php
    echo return [
    echo    'excluded_tables' =^> [
    echo       'sqlite_sequence',
    echo    ],
    echo    'hidden_tables' =^> [],
    echo    'hidden_columns' =^> [
    echo       'users' =^> ['password', 'remember_token', ],
    echo    ],
    echo. 
    echo    'hide_suffixed_views' =^> true,
    echo    'primary_key_pattern' =^> '^id$',
    echo    'foreign_key_pattern' =^> '^([a-0-9_]+^)_id$',
    echo ];
)
:END_RESTINPEACE

set /p DATABASE_DIR=Enter "database" directory name [default: database]: 
if "%DATABASE_DIR%"=="" set DATABASE_DIR=database
if exist "%DATABASE_DIR%" (
    echo Directory "%DATABASE_DIR%" already exists.
    GOTO :END_DATABASE_DIR
)
echo Creating directory "%DATABASE_DIR%"...
mkdir "%DATABASE_DIR%"
:END_DATABASE_DIR

IF exist ".env" IF "%FORCE%"=="false" (
    echo File ".env" already exists.
    GOTO :END_ENV
)
echo Creating file ".env"...
> ".env" (
    echo RESTINPEACE_CLIENTS=
    echo RESTINPEACE_APP_PATH=%CD%
    echo RESTINPEACE_CONFIG_PATH=%CONFIG_DIR%
    echo RESTINPEACE_DATABASE_PATH=%DATABASE_DIR%
    echo RESTINPEACE_LOGS_PATH=logs
    echo RESTINPEACE_SCHEMA_CACHE=-1
    echo RESTINPEACE_HIDE_SUFFIXED_VIEWS=false
    echo.
    echo DB_CONNECTION=sqlite
    echo DB_HOST=127.0.0.1
    echo DB_PORT=3306
    echo DB_DATABASE=db.sqlite
    echo DB_USERNAME=username
    echo DB_PASSWORD=password
)
:END_ENV

IF exist "go.bat" IF "%FORCE%"=="false" (
    echo File "go.bat" already exists.
    GOTO :END_GO
)
echo Creating file "go.bat"...
> "go.bat" (
    echo @echo off
    echo php -S localhost:8080 -t %DIR_NAME%
)
:END_GO

IF exist "routes.php" IF "%FORCE%"=="false" (
    echo File "routes.php" already exists.
    GOTO :END_ROUTES
)
echo Creating file "routes.php"...
> "routes.php" (
    echo ^<?php
    echo include_once 'vendor/bobanum/restinpeace/routes.php';
)
:END_ROUTES

:END