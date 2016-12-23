<?php
session_start();

// Подключаемся к БД
require_once "autoloader.php";
require_once "config.php";
$db = new DataBase();
$db->connectToDB();

// Проверяем авторизацию
$auth = new Authorization($db);
$auth->isLogged();

// Создаем объект задач
$tasks = new Tasks($db);

// Проверяем, есть ли запросы от пользователя на изменение данных. Если есть, выполняем.
$tasks->action();
?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>TO-DO приложение</title>
    <style>
        table, td, th {
            border: 1px solid #ddd;
            text-align: left;
            padding: 15px;
        }

        table.menu {
            border: 0;
            width: 90%;
            margin-left:5%;
            margin-right:5%;
        }

        td.menu { border: 0; }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        h2 {
            text-align: center;
        }

        h3 {
            text-align: center;
        }
    </style>
</head>

<body>
<h2>Добро пожаловать, <?php echo $_SESSION['username'] ?>!</h2>

<table class="menu">
    <tr>
        <td class="menu">
            <form method="POST" style="margin: 20px">
                <label for="new_task">Новая задача: </label><input type="text" name="new_task" value=""/>
                <input type="submit" name="save" value="Добавить"/>
            </form>
        </td>
        <td class="menu">
            <form method="POST" style="margin: 20px">
                <label for="sort" style="margin-left: 30px">Сортировать по:</label>
                <select name="sort_by">
                    <option value="date_added">Дате добавления</option>
                    <option value="is_done">Статусу</option>
                    <option value="description">Описанию</option>
                </select>
                <input type="submit" name="sort" value="Отсортировать" />
            </form>
        </td>
        <td class="menu">
        <div style="text-align: center"><a href="logout.php">Выйти из учетной записи</a></div>
        </td>
    </tr>
</table>

<?php if(isset($_SESSION['error'])):?>
    <h4 style="color: crimson; text-align: center"><?php echo $_SESSION['error'] ?></h4>
<?php endif; ?>

<h3>Вы постановщик:</h3>

<?php
// Загружаем данные из БД: задачи, где пользователь - постановщик
$tasksCreatedByUser = $tasks->tasksCreatedByUser(); ?>

<table>
    <tr>
        <th>Дата добавления</th>
        <th>Описание задачи</th>
        <th>Статус</th>
        <th>Исполнитель</th>
        <th>Действия</th>
    </tr>

    <?php
    // Формируем таблицу с задачами, где пользователь - автор
    foreach ($tasksCreatedByUser as $task): ?>
    <tr>
        <td>
            <?php if (isset($_POST['change']) && $_POST['change'] == $task['id'] . 't1'): ?>
            <form method="post">
                <input type="text" name="new_date_added" value="<?php echo $task['date_added']?>">
            <?php else: echo $task['date_added'];
            endif; ?>
        </td>
        <td><?php if (isset($_POST['change']) && $_POST['change'] == $task['id'] . 't1'): ?>
                    <input type="text" name="new_description" value="<?php echo $task['description']?>">
            <?php else: echo $task['description'];
            endif; ?></td>
        <td><?php if (isset($_POST['change']) && $_POST['change'] == $task['id'] . 't1'): ?>
                <select name="new_is_done">
                    <option value="0">Не выполнено</option>
                    <option value="1">В процессе</option>
                    <option value="2">Выполнено</option>
                </select>
            <?php else: echo $task['is_done'];
            endif; ?></td>
        <td><?php if (isset($_POST['change']) && $_POST['change'] == $task['id'] . 't1'):
            $users = $tasks->getAllUsers(); ?>
            <select name="new_responsible">
                <?php foreach ($users as $user):?>
                <option value="<?php echo $user['user_id'] ?>"><?php echo $user['username'] ?></option>
                <?php endforeach; ?>
            </select>
            <?php elseif($task['responsible'] == $_SESSION['username']) : echo 'Я'; else : echo $task['responsible']; endif; ?>
        </td>
        <td>
            <?php if (isset($_POST['change']) && $_POST['change'] == $task['id'] . 't1'): ?>
                <div style="text-align: center">
                    <button type="submit" name="change_id" value="<?php echo $task['id']?>">Сохранить</button>
                    <button type="submit" name="change_id" value="">Отмена</button>
                </div>
                </form>
            <?php else: ?>
                </form>
                <div style="text-align: center">
                    <form method="post">
                        <?php if ($task['is_done'] !== 'Выполнено') : ?>
                        <button type="submit" value="<?php echo $task['id'] . 't1'?>" name="mark_as_done">Выполнить</button>
                        <?php endif; ?>
                        <button type="submit" value="<?php echo $task['id'] . 't1'?>" name="change">Изменить</button>
                        <button type="submit" value="<?php echo $task['id'] . 't1'?>" name="delete">Удалить</button>
                    </form>
                </div>
            <?php endif; ?></td>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<h3>Вы исполнитель:</h3>

<?php
// Загружаем данные из БД: задачи, где пользователь - исполнитель
$tasksWhereUserIsResponsible = $tasks->tasksWhereUserIsResponsible(); ?>

<table>
    <tr>
        <th>Дата добавления</th>
        <th>Описание задачи</th>
        <th>Статус</th>
        <th>Постановщик</th>
        <th>Действия</th>
    </tr>

    <?php
    // Формируем таблицу с задачами, где пользователь - исполнитель
    foreach ($tasksWhereUserIsResponsible as $task): ?>
        <tr>
            <td>
                <?php if (isset($_POST['change']) && $_POST['change'] == $task['id'] . 't2'): ?>
                <form method="post">
                    <input style="text-align: left" type="text" name="new_date_added" value="<?php echo $task['date_added']?>">

                    <?php else: echo $task['date_added'];
                    endif; ?>
            </td>
            <td><?php if (isset($_POST['change']) && $_POST['change'] == $task['id'] . 't2'): ?>
                    <input type="text" name="new_description" value="<?php echo $task['description']?>">

                <?php else: echo $task['description'];
                endif; ?></td>
            <td><?php if (isset($_POST['change']) && $_POST['change'] == $task['id'] . 't2'): ?>
                    <select name="new_is_done">
                        <option value="0">Не выполнено</option>
                        <option value="1">В процессе</option>
                        <option value="2">Выполнено</option>
                    </select>

                <?php else: echo $task['is_done'];
                endif; ?></td>
            <td>
                <?php if ($task['author'] == $_SESSION['username']) : echo 'Я'; else : echo $task['author']; endif;  ?>
            </td>
            <td>
                <?php if (isset($_POST['change']) && $_POST['change'] == $task['id'] . 't2'): ?>
                    <div style="text-align: center">
                    <button type="submit" name="change_id" value="<?php echo $task['id']?>">Сохранить</button>
                    <button type="submit" name="change_id" value="">Отмена</button>
                    </div>
                    </form>
                <?php else: ?>
                    </form>
                    <div style="text-align: center">
                    <form method="post">
                        <?php if ($task['is_done'] !== 'Выполнено') : ?>
                            <button type="submit" value="<?php echo $task['id'] . 't2'?>" name="mark_as_done">Выполнить</button>
                        <?php endif; ?>
                        <button type="submit" value="<?php echo $task['id'] . 't2'?>" name="change">Изменить</button>
                        <button type="submit" value="<?php echo $task['id'] . 't2'?>" name="delete">Удалить</button>
                    </form>
                    </div>
                <?php endif; ?></td>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
</body>
</html>

<?php $_SESSION['error'] = ''; ?>
