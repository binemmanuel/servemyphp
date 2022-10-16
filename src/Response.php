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
    ): void {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=UTF-8');
        header('Access-Control-Allow-Methods: GET, POST');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Authorization, Content-Type');

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
