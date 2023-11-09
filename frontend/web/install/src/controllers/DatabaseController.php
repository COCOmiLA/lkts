<?php

require_once(__DIR__ . '/../../../../../vendor/autoload.php');

use yii\helpers\FileHelper;

class DatabaseController
{
    public $type;
    public $port;
    public $server;
    public $dbname;
    public $username;
    public $password;

    private $db;

    const ENV_PATH = '/.env';

    const EMPTY_ENV_PATH = '/confs/empty.env';

    public function setup()
    {
        $server_with_port = $_POST['ServerAddress'];
        $exploded = explode(':', $server_with_port);
        $this->server = $exploded[0];
        if (isset($exploded[1])) {
            $this->port = $exploded[1];
        }

        $this->username = $_POST['DbUserName'];
        $this->password = $_POST['DbUserPassword'];
        $this->dbname = $_POST['DbName'];
        $this->type = $_POST['DbType'];

        if (empty($this->port)) {
            $this->port = '3306';
            if ($this->type == 'pgsql') {
                $this->port = '5432';
            }
        }

        if (!in_array($this->type, ['mysql', 'pgsql'])) {
            http_response_code(400);
            header('Content-Type: text/plain');
            echo 'Ошибка: поддерживаются следующие типы подключений к СУБД: mysql,pgsql';
            die();
        }
        if (preg_match('/[^a-zA-Z0-9]+^_/', $this->dbname)) {
            http_response_code(400);
            header('Content-Type: text/plain');
            echo 'Ошибка: в названии БД используются недопустимый символ';
            die();
        }

        $this->connectToServer();
        $this->useDb();
        $this->writeDsnToEnv('/../..');

        http_response_code(200);
        header('Content-Type: text/plain');
        echo true;
    }

    




    public function connectToServer($forGuiInstaller = true)
    {
        try {
            $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
            $pdo_options[PDO::ATTR_TIMEOUT] = '10';

            $connection = "{$this->type}:host={$this->server};port={$this->port}";
            if ($this->type != 'mysql') {
                $connection .= ";dbname={$this->dbname}";
            }
            $this->db = new PDO($connection, $this->username, $this->password, $pdo_options);

            return true;
        } catch (PDOException $e) {
            $response = "Ошибка: {$e->getMessage()}";

            switch ($e->getCode()) {
                case (2002):
                    $response = '';
                    if ($forGuiInstaller) {
                        $response = 'Ошибка: ';
                    }
                    $response .= 'Сервер БД недоступен, либо его адрес указан неверно.';
                    break;

                case (1045):
                    $response = '';
                    if ($forGuiInstaller) {
                        $response = 'Ошибка: ';
                    }
                    $response .= 'Неправильный логин или пароль, доступ к серверу БД запрещен.';
                    break;
            }

            if (!$forGuiInstaller) {
                return $response;
            }

            http_response_code(400);
            header('Content-Type: text/plain');
            echo $response;
            die();
        }
    }

    




    public function useDb($forGuiInstaller = true)
    {
        if ($this->type != 'mysql') {
            return true;
        }
        try {
            $this->db->exec('USE ' . $this->dbname);
            if ($this->checkNotEmpty()) {
                if (!$forGuiInstaller) {
                    return 'указанная база данных не пуста';
                }

                http_response_code(400);
                header('Content-Type: text/plain');
                echo 'Ошибка: указанная база данных не пуста';
                die();
            }

            return true;
        } catch (Throwable $e) {
            if (($e instanceof PDOException) && $e->getCode() == '42000') {
                $createDbResult = $this->createDb($forGuiInstaller);
                $this->db->exec("USE {$this->dbname}");

                return $createDbResult;
            } else {
                if (!$forGuiInstaller) {
                    return $e->getMessage();
                }

                http_response_code(400);
                header('Content-Type: text/plain');
                echo "Ошибка: {$e->getMessage()}";
                die();
            }
        }
    }

    protected function checkNotEmpty()
    {
        $sql = "SELECT COUNT(DISTINCT table_name) FROM information_schema.columns WHERE table_schema = ?";
        $statement = $this->db->prepare($sql);
        $statement->execute([$this->dbname]);
        $result = $statement->fetch();

        return isset($result[0]) && (int)$result[0] > 0;
    }

    




    protected function createDb($forGuiInstaller = true)
    {
        try {
            $sql = "CREATE DATABASE {$this->dbname}";
            if ($this->type == 'mysql') {
                $sql .= ' CHARACTER SET utf8 DEFAULT COLLATE utf8_unicode_ci';
            } elseif ($this->type == 'pgsql') {
                $sql .= " ENCODING 'UTF8'";
            }
            $this->db->exec($sql);
        } catch (PDOException $e) {
            if ($e->getCode() == '42000') {
                if (!$forGuiInstaller) {
                    return 'у пользователя недостаточно прав для использования (или создания) указанной базы данных';
                }

                http_response_code(400);
                header('Content-Type: text/plain');
                echo 'Ошибка: У пользователя недостаточно прав для использования (или создания) указанной базы данных';
                die();
            } else {
                if (!$forGuiInstaller) {
                    return $e->getMessage();
                }

                http_response_code(400);
                header('Content-Type: text/plain');
                echo "Ошибка: {$e->getMessage()}";
                die();
            }
        }
        return true;
    }

    





    public function writeDsnToEnv($pathPrefix, $forGuiInstaller = true)
    {
        $project_root = FileHelper::normalizePath(getcwd() . $pathPrefix);
        $path_empty_env = FileHelper::normalizePath($project_root . self::EMPTY_ENV_PATH);
        $path = FileHelper::normalizePath($project_root . self::ENV_PATH);

        if (!is_readable($project_root)) {
            http_response_code(400);
            header('Content-Type: text/plain');
            echo "Нет доступа на чтение или не найден целевой путь ({$project_root}).";
            echo "Обратитесь к администратору для предоставления доступа для системного пользователя " . get_current_user();
            die();
        }
        
        if (file_exists($path)) {
            unlink($path);
        }
        
        copy($path_empty_env, $path);

        if (!is_writable($path)) {
            if (!$forGuiInstaller) {
                return 'невозможно записать информацию в ' . self::ENV_PATH;
            }

            http_response_code(400);
            header('Content-Type: text/plain');
            echo "Нет доступа на запись или не найден целевой путь ({$path}).";
            echo "Обратитесь к администратору для предоставления доступа для системного пользователя " . get_current_user();
            die();
        }

        $this->updateEnv(
            'DB_DSN',
            "DB_DSN=\"{$this->type}:host={$this->server};port={$this->port};dbname={$this->dbname}\"" . PHP_EOL,
            $path
        );
        $this->updateEnv(
            'DB_USERNAME',
            "DB_USERNAME=\"{$this->username}\"" . PHP_EOL,
            $path
        );
        $this->updateEnv(
            'DB_PASSWORD',
            "DB_PASSWORD=\"{$this->password}\"" . PHP_EOL,
            $path
        );

        return true;
    }

    private function updateEnv($sub_str, $content, $env_path)
    {
        $lines = file($env_path);

        $new_lines = [];
        foreach ($lines as $string) {
            if (strpos($string, $sub_str) !== false) {
                $new_lines[] = $content;
            } else {
                $new_lines[] = $string;
            }
        }

        file_put_contents($env_path, $new_lines);
    }
}
