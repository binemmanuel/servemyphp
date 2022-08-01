# Serve My PHP

A light php framwork for building Server Side Applications (APIs)

## Installation

To install use composer

```bash
composer require binemmanuel/servemyphp
```

## Usage

```.htaccess
# ./public_html/.htaccess

RewriteEngine On

<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
</IfModule>

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-l
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]

<FilesMatch "\.(json|lock|md|env|txt|gitignore)">
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch "config.php">
    Order allow,deny
    Deny from all
</FilesMatch>

Options -Indexes
```

```env
# .env

# Database Cridentials
DB_HOST = <host-name-here>
DB_USER = <database-username-here>
DB_PASSWORD = <database-password-here>
DB_NAME = <database-name-here>
DB_CHASET = 'utf8mb4'
```

```php
# ./public_html/index.php

use Binemmanuel\ServeMyPhp\Router;
use Binemmanuel\ServeMyPhp\Request;
use Binemmanuel\ServeMyPhp\Response;
use Binemmanuel\ServeMyPhp\Database;

$database = (new Database($_ENV))->mysqli();
$app = new Router($database);

$app->get('/api/v1/get/message', function (Request $req, Response $res) use ($database) {
     $res::sendJson(["message" : "Hello, world"]);
});

$app->run();
```


### Start Dev Server
```bash
php -S localhost:8080
```