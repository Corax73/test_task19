<?php

namespace DB;

use PDO;

class Connection
{
    /**
     * creates a connection to the database
     * @param string $path
     * @return PDO
     */
    public function connect(string $path): PDO
    {
        static $pdo;

        if (!$pdo) {
            if (file_exists($path)) {
                $config = include $path;
            } else {
                $msg = 'Создайте и настройте config.php';
                trigger_error($msg, E_USER_ERROR);
            }
            $dsn = sprintf(
                "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
                $config['db_host'],
                $config['db_port'],
                $config['db_name'],
                $config['db_user'],
                $config['db_pass']
            );
            $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $pdo;
    }
}
