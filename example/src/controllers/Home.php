<?php

namespace Binemmanuel\ServeMyPhp\Example\Controller;

use Binemmanuel\ServeMyPhp\Example\Model\{
    User,
};
use Binemmanuel\ServeMyPhp\{
    BaseController,
    Request,
    Response,
};

class Home extends BaseController
{
    public function dashboard(Request $req, Response $res)
    {
        $guest =  (new User($this->db))->fetchAll();

        $res::sendJson(['message' => 'Hello from the dashboard', 'users' => $guest]);
    }
}
