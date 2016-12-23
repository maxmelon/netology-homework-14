<?php

class Authorization
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Проверяем, авторизован ли пользователь
     */
    public function isLogged()
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: login.php');
        }
    }

    /**
     * Регистрация нового пользователя
     */
    public function signUp()
    {
        if (!empty($_POST['username']) && !empty($_POST['password'])) {
            $username = $_POST['username'];
            $password = $_POST['password'];

            // Защита от DDOS-атак
            if (strlen($password) > 72) {
                die("Password must be 72 characters or less");
            }

            // Проверяем имя пользователя
            if ($this->ifUsernameAlreadyExists($username) == true) {
                $_SESSION['error_reg'] = 'Выбранное имя пользователя уже занято';
                return false;
            }

            // Проверяем пароль на соблюдение требований
            if (preg_match("/^[0-9A-Za-z!@#$%]{5,12}$/", $password) !== 1) {
                $_SESSION['error_reg'] = 'Пароль может быть состоять только из букв латинского алфавита, 
                цифр и специальных символов. Длина пароля - не менее 5 символов и не более 12.';
                return false;
            }

            // Хэшируем пароль. Используется фреймворк phpass (http://www.openwall.com/phpass/).
            $hasher = new PasswordHash(8, false);
            $hash = $hasher->HashPassword($password);

            // Отправляем логин и пароль в базу. Авторизируем пользователя и пересылаем на главную.
            if (strlen($hash) >= 20) {
                // Добавляем пользователя
                $sth = $this->db->pdo->prepare('INSERT INTO user (login, password) VALUES (:username, :pass)');
                $sth->bindValue(':username', $username, PDO::PARAM_STR);
                $sth->bindValue(':pass', $hash, PDO::PARAM_STR);
                $sth->execute();
                // Достаем id для сохранения в глобальную переменную сессии
                $sth = $this->db->pdo->prepare('SELECT id FROM user WHERE login = :username');
                $sth->bindValue(':username', $username, PDO::PARAM_STR);
                $sth->execute();
                $result = $sth->fetch(PDO::FETCH_ASSOC);
                $_SESSION['user_id'] = $result['id'];
                $_SESSION['username'] = $username;
                header('Location: index.php');
            } else {
                echo 'При регистрации произошла непредвиденная ошибка';
            }
        }
    }

    /**
     * Проверка логина и пароля при авторизации
     */
    public function checkLogin()
    {
        if (!empty($_POST['username']) && !empty($_POST['password'])) {

            $hasher = new PasswordHash(8, false);

            $username = $_POST['username'];
            $password = $_POST['password'];

            if (strlen($password) > 72) {
                die("Password must be 72 characters or less");
            }

            $stored_hash = "*";

            $sth = $this->db->pdo->prepare('SELECT id, password FROM user WHERE login = :username');
            $sth->bindValue(':username', $username, PDO::PARAM_STR);
            $sth->execute();
            $result = $sth->fetch(PDO::FETCH_ASSOC);
            $stored_hash = $result['password'];

            $check = $hasher->CheckPassword($password, $stored_hash);

            if ($check) {
                $_SESSION['user_id'] = $result['id'];
                $_SESSION['username'] = $username;
                header('Location: index.php');
            } else {
                $_SESSION['error_login'] = 'Неверные логин или пароль';
            }
        }
    }

    /**
     * Проверка, существует ли уже пользователь с таким именем
     * @param $username
     * @return bool
     */
    protected function ifUsernameAlreadyExists($username)
    {
        $sth = $this->db->pdo->prepare('SELECT * FROM user WHERE login = :username');
        $sth->bindValue(':username', $username, PDO::PARAM_STR);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        if (!empty($result)) {
            return true;
        } else {
            return false;
        }
    }
}