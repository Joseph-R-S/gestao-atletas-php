<?php

namespace App\Database;

use PDO;
use PDOException;

class Connection
{
    private static ?PDO $conn = null;

    public static function open(string $name)
    {
        $file = __DIR__ . "../../../config/{$name}.ini";
        if (!file_exists($file)) {
            throw new PDOException("Arquivo {$name} não encontrado");
        }

        $ini = parse_ini_file($file);

        try {
            if (empty(self::$conn)) {
                $host = isset($ini['host']) ? $ini['host'] : null;
                $dbname = isset($ini['dbname']) ? $ini['dbname'] : null;
                $user = isset($ini['user']) ? $ini['user'] : null;
                $pass = isset($ini['pass']) ? $ini['pass'] : null;
                $type = isset($ini['type']) ? $ini['type'] : null;

                switch ($type) {
                    case 'pgsql':
                        $port = isset($ini['port']) ? $ini['port'] : '5432';
                        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
                        self::$conn = new PDO($dsn, $user, $pass);
                        break;
                    case 'mysql':
                        $port = isset($ini['port']) ? $ini['port'] : '3306';
                        $dsn = "mysql:host={$host};port={$port};dbname={$dbname}";
                        self::$conn = new PDO($dsn, $user, $pass);
                        break;
                }
            }
            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return self::$conn;
        } catch (PDOException $e) {
            throw new PDOException('Erro ao conectar: ' . $e->getMessage());
        }
    }
}
