<?php
namespace Binemmanuel\ServeMyPhp;

use App\Database as AppDatabase;
use App\Exception\ThemeException;
use Core\Database;
use Exception;

class Response
{
    public static function render(
        String $template,
        String $theme = 'starter-theme',
        String $pageTitle = '',
        int $statusCode = 200,
        ...$props,
    ): void {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: text/html; charset=UTF-8');
        header('Access-Control-Allow-Methods: GET, POST');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Authorization, Content-Type');

        extract($props);

        http_response_code($statusCode);

        // Set the theme path
        $theme = __DIR__ . "/views/{$theme}";

        // Set the template path
        $templatePath =  "{$theme}/{$template}";

        self::themeExists($theme);

        // Check if there's a header file
        if (file_exists("$theme/header.php")) {
            require "$theme/header.php";
        } else if (file_exists("$theme/header.html")) {
            require "$theme/header.html";
        }

        // Check if the required template exists
        if (file_exists("$templatePath.php")) {
            require "$templatePath.php";
        } else if (file_exists("$templatePath.html")) {
            require "$templatePath.html";
        }

        // Check if there's a footer file
        if (file_exists("$theme/footer.php")) {
            require "$theme/footer.php";
        } else if (file_exists("$theme/footer.html")) {
            require "$theme/footer.html";
        }

        exit;
    }

    /**
     * Sends a JSON data to the client
     * 
     * @param array The data to be sent
     */
    public static function sendJson(array $data, $statusCode = 200): void
    {
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

    /**
     * Check if a theme exists
     * 
     * @param String The theme path
     * @return bool|null True if the theme exists else throw a ThemeNotFoudException
     */
    private static function themeExists(String $theme): bool|null
    {
        if (file_exists($theme)) return true;


        echo 'Theme not found';
    }
}
