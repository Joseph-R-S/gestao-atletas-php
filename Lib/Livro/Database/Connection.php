<?php
namespace Livro\Database;

use PDOException;
use Exception;
use PDO;

class Connection
{
    private static ?PDO $conn = null;
    private function __construct() {}
    public static function open(string $name)
    {
        $file = "App/Config/{$name}.ini";
        if (file_exists($file)) {
            $db = parse_ini_file($file);
        } else {
            throw new Exception("Arquivo {$file} não encontrado");
        }
        try {
            if (empty(self::$conn)) {
                $user = isset($db['user']) ? $db['user'] : null;
                $pass = isset($db['pass']) ? $db['pass'] : null;
                $name = isset($db['name']) ? $db['name'] : null;
                $host = isset($db['host']) ? $db['host'] : null;
                $type = isset($db['type']) ? $db['type'] : null;
                $port = isset($db['port']) ? $db['port'] : null;
                
                switch ($type) {
                    case 'pgsql':
                        $port = isset($db['port']) ? $db['port'] : 5432;
                        self::$conn = new PDO("pgsql:dbname={$name};user={$user};password={$pass};host={$host};
                port={$port}");
                        break;
                    case 'mysql':
                        $port = isset($db['port']) ? $db['port'] : 3606;
                        self::$conn = new PDO("mysql:dbhost={$host};port={$port};dbname={$name}", $user, $pass);
                        break;
                    case 'sqlite':
                        self::$conn = new PDO("sqlite:{$name}");
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
