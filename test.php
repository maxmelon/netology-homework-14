<?php
require_once "config.php";
require_once "autoloader.php";
$db = new DataBase();
$db->connectToDB();
$calories = 1;
$colour = 'red';
try {
    $sth = $db->pdo->prepare('INSERT INTO tasks (description, is_done, date_added)
    VALUES (?, ?, ?);');
    $sth->bindValue(1, 'Ehf', PDO::PARAM_STR);
    $sth->bindValue(2, 1, PDO::PARAM_INT);
    $sth->bindValue(3, date("Y-m-d H:i:s"), PDO::PARAM_STR);
    $sth->execute();
    print_r($sth);
    print_r($sth->errorInfo());
} catch (PDOException $e) {
    echo $e->getMessage();
}
var_dump($sth);