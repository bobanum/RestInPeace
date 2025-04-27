#!/bin/bash

# Check if the option -f is passed
FORCE=false
if [[ "$1" == "-f" ]]; then
    FORCE=true
    shift
fi

# Get the directory name
DIR_NAME=$1
if [[ -z "$DIR_NAME" ]]; then
    read -p 'Enter "api" directory name [default: api]: ' DIR_NAME
    DIR_NAME=${DIR_NAME:-api}
else
    shift
fi

# Create the directory if it doesn't exist
if [[ -d "$DIR_NAME" ]]; then
    echo "Directory \"$DIR_NAME\" already exists."
else
    echo "Creating directory \"$DIR_NAME\"..."
    mkdir "$DIR_NAME"
fi

# Handle .htaccess file
if [[ -f "$DIR_NAME/.htaccess" && "$FORCE" == "false" ]]; then
    echo "File \"$DIR_NAME/.htaccess\" already exists."
else
    echo "Creating file \"$DIR_NAME/.htaccess\"..."
    cat > "$DIR_NAME/.htaccess" <<EOL
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ index.php/\$1 [L,QSA]
</IfModule>
EOL
fi

# Handle index.php file
if [[ -f "$DIR_NAME/index.php" && "$FORCE" == "false" ]]; then
    echo "File \"$DIR_NAME/index.php\" already exists."
else
    echo "Creating file \"$DIR_NAME/index.php\"..."
    cat > "$DIR_NAME/index.php" <<EOL
<?php
include_once '../vendor/bobanum/restinpeace/src/debug.php';
include_once '../vendor/autoload.php';
// RestInPeace::guard();
include '../routes.php';
EOL
fi

# Get the config directory name
read -p 'Enter "config" directory name [default: config]: ' CONFIG_DIR
CONFIG_DIR=${CONFIG_DIR:-config}

# Create the config directory if it doesn't exist
if [[ -d "$CONFIG_DIR" ]]; then
    echo "Directory \"$CONFIG_DIR\" already exists."
else
    echo "Creating directory \"$CONFIG_DIR\"..."
    mkdir "$CONFIG_DIR"
fi

# Handle restinpeace.php file
if [[ -f "$CONFIG_DIR/restinpeace.php" && "$FORCE" == "false" ]]; then
    echo "File \"$CONFIG_DIR/restinpeace.php\" already exists."
else
    echo "Creating file \"$CONFIG_DIR/restinpeace.php\"..."
    cat > "$CONFIG_DIR/restinpeace.php" <<EOL
<?php
return [
    'excluded_tables' => [
        'sqlite_sequence',
    ],
    'hidden_tables' => [],
    'hidden_columns' => [
        'users' => ['password', 'remember_token'],
    ],
    'hide_suffixed_views' => true,
    'primary_key_pattern' => '^id$',
    'foreign_key_pattern' => '^([a-0-9_]+)_id$',
];
EOL
fi

# Get the database directory name
read -p 'Enter "database" directory name [default: database]: ' DATABASE_DIR
DATABASE_DIR=${DATABASE_DIR:-database}

# Create the database directory if it doesn't exist
if [[ -d "$DATABASE_DIR" ]]; then
    echo "Directory \"$DATABASE_DIR\" already exists."
else
    echo "Creating directory \"$DATABASE_DIR\"..."
    mkdir "$DATABASE_DIR"
fi
echo "Current working directory: $(cygpath -w "$(pwd)")"
# Handle .env file
if [[ -f ".env" && "$FORCE" == "false" ]]; then
    echo "File \".env\" already exists."
else
    echo "Creating file \".env\"..."
    cat > ".env" <<EOL
RESTINPEACE_CLIENTS=
RESTINPEACE_APP_PATH=$(cygpath -w "$(pwd)")
RESTINPEACE_CONFIG_PATH=$CONFIG_DIR
RESTINPEACE_DATABASE_PATH=$DATABASE_DIR
RESTINPEACE_LOGS_PATH=logs
RESTINPEACE_SCHEMA_CACHE=-1
RESTINPEACE_HIDE_SUFFIXED_VIEWS=false

DB_CONNECTION=sqlite
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db.sqlite
DB_USERNAME=username
DB_PASSWORD=password
EOL
fi

# Handle go(.sh) file
if [[ -f "go" && "$FORCE" == "false" ]]; then
    echo "File \"go\" already exists."
else
    echo "Creating file \"go\"..."
    cat > "go" <<EOL
#!/bin/bash
php -S localhost:8080 -t $DIR_NAME
EOL
    chmod +x "go"
fi

# Handle routes.php file
if [[ -f "routes.php" && "$FORCE" == "false" ]]; then
    echo "File \"routes.php\" already exists."
else
    echo "Creating file \"routes.php\"..."
    cat > "routes.php" <<EOL
<?php
include_once 'vendor/bobanum/restinpeace/routes.php';
EOL
fi

echo "Setup complete."