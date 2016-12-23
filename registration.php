<?php
session_start();
require_once "autoloader.php";
require_once "config.php";
$db = new DataBase();
$db->connectToDB();
$auth = new Authorization($db);
$auth->signUp();
?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<h4>Регистрация новой учетной записи</h4>
<?php if(isset($_SESSION['error_reg'])):?>
    <h4 style="color: crimson"><?php echo $_SESSION['error_reg'] ?></h4>
<?php endif; ?>
<form method="post">
    <label for="username">Имя пользователя: </label>
    <input name="username">
    <label for="password">Пароль: </label>
    <input name="password" type="password">
    <button type="submit">Войти</button>
</form>
</body>
</html>