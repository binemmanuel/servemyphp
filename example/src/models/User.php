<?php

namespace Binemmanuel\ServeMyPhp\Example\Model;

use Binemmanuel\ServeMyPhp\BaseModel;

class User extends BaseModel
{
    protected static array $rules;

    public ?String $userId;
    public ?String $username;
    public ?String $email;
    public ?String $password;

    protected function __setTable(): string
    {
        return 'users';
    }

    public function makeRules(array $rules): void
    {
        self::$rules = $rules;
    }

    protected function rules(): array
    {
        return self::$rules;
    }
}
