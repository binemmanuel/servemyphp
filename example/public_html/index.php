<?php

use Binemmanuel\ServeMyPhp\{
    Database,
    Request,
    Response,
    Router,
};

require __DIR__ . '/../config.php';


$dbConfig = [
    'DB_HOST' => 'localhost',
    'DB_USER' => 'binemmanuel',
    'DB_PASSWORD' => '',
    'DB_NAME' => 'tradehouse',
    'DB_CHASET' => 'utf8mb4',
];

$db = (new Database($dbConfig))->mysqli();

$app = new Router($db);

$app->post('/api/v1/auth/user/signup', function (Request $req, Response $res) use ($db) {
});

$app->run();
