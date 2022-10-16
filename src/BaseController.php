<?php

namespace Binemmanuel\ServeMyPhp;

use mysqli;
use PDO;

abstract class BaseController
{
    function __construct(protected mysqli|PDO $db)
    {
    }
}
