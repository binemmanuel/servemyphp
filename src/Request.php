<?php

namespace Binemmanuel\ServeMyPhp;

use mysqli;

class Request
{
    public function __construct()
    {
        foreach ($this->body() as $key => $value) {
            if (empty($value))
                $this->{$key} = $value;
            else
                $this->{$key} = trim($value);
        }

        $file = $this->file();

        if (!empty($file)) {
            $this->file = $file;
        }

        $this->header = $_SERVER;
    }

    public function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    public function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    public function body(): array
    {
        if ($this->isGet()) {
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        if ($this->isPost()) {
            foreach ($_POST as $key => $value) {
                $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        return $body ?? $this->jsonBody();
    }

    private function file(): array
    {
        return $_FILES ?? [];
    }

    public function jsonBody(): array
    {
        return json_decode(file_get_contents("php://input"), true) ?? [];
    }
}
