<?php

namespace Binemmanuel\ServeMyPhp;

use mysqli;
use mysqli_stmt;
use PDO;

class Database
{
    function __construct(private array $config)
    {
    }


    public function mysqli(): mysqli|null
    {
        try {
            // Enable SQL error reporting.
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            // Establish the connection.
            $db = new mysqli(
                $this->config['DB_HOST'],
                $this->config['DB_USER'],
                $this->config['DB_PASSWORD'],
                $this->config['DB_NAME'],
            );

            // Set character ser to UTF-8.
            $db->set_charset($this->config['DB_CHASET']);

            return $db;
        } catch (\mysqli_sql_exception  $e) {
            throw new \mysqli_sql_exception($e->getMessage(), $e->getCode());
        }
    }

    public function pdo(): PDO|null
    {
        try {
            return new PDO(
                "mysql:host={$this->config['DB_HOST']};dbname={$this->config['DB_NAME']}",
                $this->config['DB_USER'],
                $this->config['DB_PASSWORD'],
            );
        } catch (\PDOException $e) {
            echo '<pre>';
            throw new \PDOException($e->getMessage(), $e->getCode()); // Throw error message. 
            echo '</pre>';
        }
    }

    public static function sqlResult(mysqli_stmt $stmt): array
    {
        $keys = [];
        $params = [];

        $meta = $stmt->result_metadata();

        while ($field = $meta->fetch_field()) {
            $params[] = $field->name;
            $keys[] = $field->name;
        }

        return [&$keys, &$params];
    }
}
