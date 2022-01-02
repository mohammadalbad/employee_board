<?php

require_once $_SERVER['DOCUMENT_ROOT']."/employee_board/config/database.php";

class DatabaseHelper
{

    public function connect()
    {

        try {

            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_DATABASE . ";charset=" . DB_CHARSET;
            $opt = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            return new PDO($dsn, DB_USER, DB_PASSWORD, $opt);
        } catch (PDOException $exception) {
            echo "Connection Failed: " . $exception->getMessage();
        }
    }
}
