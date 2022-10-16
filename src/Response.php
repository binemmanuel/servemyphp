<?php

namespace Binemmanuel\ServeMyPhp;

class Response
{
    /**
     * Sends a JSON data to the client
     * 
     * @param array The data to be sent
     */
    public static function sendJson(
        array $data,
        int $statusCode = 200,
        bool $allowCredentials = true,
        string $allowOrigin = '*',
        string $allowMethods = 'GET, POST, DELETE, PUT',
        string $contentType = 'application/json; charset=UTF-8',
        string $allowHeader = 'Authorization, Content-Type',
    ): void {
        header("Access-Control-Allow-Origin: $allowOrigin");
        header("Content-Type: $contentType");
        header("Access-Control-Allow-Methods: $allowMethods");
        header("Access-Control-Allow-Credentials: $allowCredentials");
        header("Access-Control-Allow-Headers: $allowHeader");

        http_response_code($statusCode);
        echo json_encode($data);
    }


    /**
     * Sanitize a data
     */
    public static function sanitize(String|array $data): String|array
    {
        if (is_array($data)) {
            $temp = [];

            foreach ($data as $key => $value) {
                $temp[$key] = htmlspecialchars(trim($value));
            }

            return $temp;
        }

        return htmlspecialchars(trim($data));
    }
}
