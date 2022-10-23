<?php

namespace Binemmanuel\ServeMyPhp\Example\Controller;

use Binemmanuel\ServeMyPhp\{
    BaseController,
    Request,
    Response
};

class Auth extends BaseController
{
    public function isAdmin(Request $req, Response $res, $next)
    {
        ///TODO: Do your authentication here;

        [$controller, $method] = $next;

        (new $controller($this->db))->$method($req, $res);
    }
}
