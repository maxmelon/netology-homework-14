<?php
class DataBase
{
    public $pdo;

    public function connectToDB()
    {
        try {
            $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT, DB_USER, DB_PASS);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->exec("SET NAMES 'utf8'");
        } catch (Exception $e) {
            echo "Произошла ошибка при загрузке базы данных.";
            exit;
        }
    }


}