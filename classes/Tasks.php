<?php

class Tasks
{
    private $db;
    private $orderBy;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Аккумулируем в этом методе все возможные действия пользователя
     * @return void
     */
    public function action()
    {
        $this->addNewTask();
        $this->deleteTask();
        $this->changeTask();
        $this->markAsDone();
        $this->changeResponsible();
    }

    /**
     * Получение всех задач
     * @return array

    public function allTasks()
    {
        $this->sortBy();
        if (isset ($this->orderBy)) {
            $sth = $this->db->pdo->prepare("SELECT description, is_done, date_added 
            FROM tasks $this->orderBy");
        } else {
            $sth = $this->db->pdo->prepare("SELECT description, is_done, date_added 
            FROM tasks ORDER BY date_added DESC");
        }
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        $result = $this->statusNumbersToWords($result);
        return $result;
    }
     */

    /**
     * Получение задач, созданных пользователем
     * @return array
     */
    public function tasksCreatedByUser()
    {
    $this->sortBy();
    if (isset ($this->orderBy)) {
        $sth = $this->db->pdo->prepare(
            "SELECT t.id as id, t.description as description, t.is_done as is_done, t.date_added as date_added, u.login as responsible
            FROM tasks as t 
            JOIN user as u ON u.id = t.responsible_id 
            WHERE t.author_id = ? 
            $this->orderBy");
    } else {
        $sth = $this->db->pdo->prepare(
            "SELECT t.id as id, t.description as description, t.is_done as is_done, t.date_added as date_added, u.login as responsible
            FROM tasks as t 
            JOIN user as u ON u.id = t.responsible_id 
            WHERE t.author_id = ? 
            ORDER BY t.date_added DESC");
    }
    $sth->bindValue(1, $_SESSION['user_id'], PDO::PARAM_INT);
    $sth->execute();
    $result = $sth->fetchAll(PDO::FETCH_ASSOC);
    $result = $this->statusNumbersToWords($result);
    return $result;
    }

    /**
     * Получение задач, где пользователь назначен отвественным
     * @return array
     */
    public function tasksWhereUserIsResponsible()
    {
        $this->sortBy();
        if (isset ($this->orderBy)) {
            $sth = $this->db->pdo->prepare(
                "SELECT t.id as id, t.description as description, t.is_done as is_done, t.date_added as date_added, u.login as author
                FROM tasks as t 
                JOIN user as u ON u.id = t.author_id
                WHERE t.responsible_id = ? 
                $this->orderBy");
        } else {
            $sth = $this->db->pdo->prepare(
                "SELECT t.id as id, t.description AS description, t.is_done AS is_done, t.date_added AS date_added, u.login AS author
                FROM tasks AS t 
                JOIN user AS u ON u.id = t.author_id
                WHERE t.responsible_id = ? 
                ORDER BY t.date_added DESC");
        }
        $sth->bindValue(1, $_SESSION['user_id'], PDO::PARAM_INT);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        $result = $this->statusNumbersToWords($result);
        return $result;
    }

    /**
     * Меняем статусы с чисел (как они в базе) на слова
     * @param $allTasks
     * @return array
     */
    protected function statusNumbersToWords($allTasks)
    {
        foreach ($allTasks as $key => $task) {
            switch ($task['is_done']) {
                case 0:
                    $allTasks[$key]['is_done'] = 'Не выполнено';
                    break;
                case 1:
                    $allTasks[$key]['is_done'] = 'В процессе';
                    break;
                case 2:
                    $allTasks[$key]['is_done'] = 'Выполнено';
                    break;
            }
        }
        return $allTasks;
    }

    /**
     * Сортировка задач
     * @return void
     */
    protected function sortBy()
    {
        if (isset($_POST['sort_by'])) {
            switch ($_POST['sort_by']) {
                case 'description':
                    $this->orderBy = 'ORDER BY description';
                    break;
                case 'is_done':
                    $this->orderBy = 'ORDER BY is_done';
                    break;
                case 'date_added':
                    $this->orderBy = 'ORDER BY date_added DESC';
                    break;
                default:
                    $this->orderBy = 'ORDER BY date_added DESC';
            }
        }
    }

    /**
     * Добавляем новую задачу
     * @return void
     */
    protected function addNewTask()
    {
        if (!empty($_POST['new_task'])) {
            $sth = $this->db->pdo->prepare(
                'INSERT INTO tasks (description, is_done, date_added, author_id, responsible_id) 
                VALUES (?, 0, ?, ?, ?)');
            $sth->bindValue(1, $_POST['new_task'], PDO::PARAM_STR);
            $sth->bindValue(2, date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $sth->bindValue(3, $_SESSION['user_id'], PDO::PARAM_INT);
            $sth->bindValue(4, $_SESSION['user_id'], PDO::PARAM_INT);
            $sth->execute();
        }
    }

    /**
     * Удаляем задачу
     * @return void
     */
    protected function deleteTask()
    {
        if (isset($_POST['delete'])) {
            $sth = $this->db->pdo->prepare('SELECT author_id from tasks WHERE id = ? LIMIT 1;');
            $sth->bindValue(1, $_POST['delete'], PDO::PARAM_STR);
            $sth->execute();
            $result = $sth->fetchAll(PDO::FETCH_ASSOC);
            $authorID = $result[0]['author_id'];
            if ($authorID == $_SESSION['user_id']) {
                $sth = $this->db->pdo->prepare('DELETE from tasks WHERE id = ? LIMIT 1;');
                $sth->bindValue(1, $_POST['delete'], PDO::PARAM_STR);
                $sth->execute();
            } else {
                $_SESSION['error'] = 'Вы не можете удалить задачу, созданную другим пользователем!';
            }


        }
    }

    /**
     * Вносим изменения в существующие задачу: дата, описание, статус
     * @return void
     */
    protected function changeTask()
    {
        if (isset($_POST['new_date_added']) || isset($_POST['new_description']) || isset($_POST['new_is_done']))
        {
            $sth = $this->db->pdo->prepare('UPDATE tasks 
            SET date_added = :date, 
            description = :desc,
            is_done = :status
            WHERE id = :num
            LIMIT 1;');

            $sth->bindValue(':num', $_POST['change_id'], PDO::PARAM_INT);
            $sth->bindValue(':date', $_POST['new_date_added'], PDO::PARAM_STR);
            $sth->bindValue(':desc', $_POST['new_description'], PDO::PARAM_STR);
            $sth->bindValue(':status', $_POST['new_is_done'], PDO::PARAM_STR);

            $sth->execute();
        }
    }

    /**
     * Вносим изменения в существующие задачу: исполнитель
     * @return void
     */
    protected function changeResponsible()
    {
        if (isset($_POST['new_responsible']))
        {
            $sth = $this->db->pdo->prepare('UPDATE tasks SET responsible_id = :resp
            WHERE id = :num LIMIT 1;');

            $sth->bindValue(':num', $_POST['change_id'], PDO::PARAM_INT);
            $sth->bindValue(':resp', $_POST['new_responsible'], PDO::PARAM_STR);
            $sth->execute();
        }
    }

    /**
     * Помечаем задачу выполненной
     * @return void
     */
    protected function markAsDone()
    {
        if (isset($_POST['mark_as_done'])) {
            $sth = $this->db->pdo->prepare('UPDATE tasks 
            SET is_done = 2
            WHERE id = :num
            LIMIT 1;');
            $sth->bindValue(':num', $_POST['mark_as_done'], PDO::PARAM_INT);
            $sth->execute();
        }
    }

    /**
     * Получаем всех пользователей (id, username) для выпадающего меню при назначении исполнителя
     * @return array
     */
    public function getAllUsers()
    {
        $sth = $this->db->pdo->prepare("SELECT id as user_id, login as username FROM user ORDER BY login");
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

}
