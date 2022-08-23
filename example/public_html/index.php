<?php

use Binemmanuel\ServeMyPhp\{
    Database,
    Request,
    Response,
    Router,
    Rule,
};
use Binemmanuel\ServeMyPhp\Example\Model\User;

require __DIR__ . '/../config.php';

$db = (new Database($_ENV))->mysqli();

$app = new Router($db);

$app->get('/api/v1/auth/get/users', function (Request $req, Response $res) use ($db) {
    $users = (new User($db))->fetchAll();

    $res::sendJson($users);
});

$app->post('/api/v1/auth/user/signup', function (Request $req, Response $res) use ($db) {
    $user = (new User($db))->loadData($req->jsonBody());

    $user->makeRules([
        'username' => [
            Rule::REQUIRED,
            [Rule::UNIQUE, 'class' => $user::class],
        ],
        'email' => [
            Rule::REQUIRED,
            Rule::EMAIL,
            [Rule::UNIQUE, 'class' => $user::class],
        ],
        'password' => [
            Rule::REQUIRED,
            [Rule::MIN_LENGTH, 4],
        ],
    ]);

    if ($user->hasError()) {
        return $res::sendJson([
            'error' => true,
            'errors' => $user->errors(),
        ], statusCode: 400);
    }

    $user->userId = $user->id;
    $user = $user->save();

    return $res::sendJson([
        'error' => false,
        'message' => 'Created successfully',
        'user' => $user,
    ]);
});

$app->run();
