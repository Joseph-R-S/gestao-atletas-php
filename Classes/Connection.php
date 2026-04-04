<?php
class Connection
{
    private static ?PDO $conn;

    public static function open()
    {
        try
        {
            if(empty(self::$conn))
            {
                $ini = parse_ini_file(__DIR__ . '/../config/config.ini');

                $dsn = "pgsql:host={$ini['host']};port={$ini['port']};dbname={$ini['dbname']}";
                
                self::$conn = new PDO($dsn, $ini['user'], $ini['pass']);
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
            }
            return self::$conn;
        }catch (PDOException $e)
        {
            throw new Exception('Erro ao conectar: ' . $e->getMessage());
        }
    }
}