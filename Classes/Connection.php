<?php
class Connection
{
    private static ?PDO $conn;

    public static function open(string $name)
    {
        if (parse_ini_file(__DIR__ . "/../config/{$name}.ini")) {
            $ini = parse_ini_file(__DIR__ . "/../config/{$name}.ini");
        } else {
            throw new Exception("Arquivo {$name} não encontrado");
        }

        try {
            if (empty(self::$conn)) 
            {
            
                $host = isset($ini['host']) ? $ini['host'] : null;
                $dbname = isset($ini['dbname']) ? $ini['dbname'] : null;
                $user = isset($ini['user']) ? $ini['user'] : null;
                $pass = isset($ini['pass']) ? $ini['pass'] : null;
                $type = isset($ini['type']) ? $ini['type'] : null;

                switch ($type)
                {
                    case 'pgsql':
                        $port = isset($ini['port']) ? $ini['port'] : '5432';
                        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
                        self::$conn = new PDO($dsn, $user, $pass);
                    break;
                    case 'mysql':
                        $port = isset($ini['port']) ? $ini['port'] : '3600';
                        $dsn = "mysql:host={$host};port={$port};dbname={$dbname}";
                        self::$conn = new PDO($dsn, $user, $pass);
                    break;
                }
            }
            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return self::$conn;
        } catch (PDOException $e) {
            throw new Exception('Erro ao conectar: ' . $e->getMessage());
        }
    }
}
