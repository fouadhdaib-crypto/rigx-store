<?php
require_once __DIR__ . '/config.php';
class DB_conn
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        global $dataBaseValues;
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=" . $dataBaseValues['host'] .
                       ";dbname=" . $dataBaseValues['dbname'] .
                       ";charset=utf8mb4";

                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];

                self::$instance = new PDO(
                    $dsn,
                    $dataBaseValues['username'],
                    $dataBaseValues['password'],
                    $options
                );

            } catch (PDOException $e) {
                throw new Exception(
                    "Database Connection Failed",
                    0,
                    $e
                );
            }
        }

        return self::$instance;
    }

    private function __construct() {}

    private function __clone() {}
}