<?php

namespace App\Database;

use App\Database\Connection;
use App\Services\Logger;
use PDO;

class Transaction
{
    private static ?PDO $conn = null;
    private static ?object $logger = null;
    private function __construct() {}

    //para abrur um transação com o banco
    public static function open(string $database)
    {
        self::$conn = Connection::open($database);
        //beginTransaction indica que estou iniciando uma transação
        //tudo que for executado despois dele é encapsulado em uma transação
        self::$conn->beginTransaction();
    }

    //fecha a transação 
    public static function close()
    {
        if (self::$conn) {
            self::$conn->commit();
            self::$conn = null;
        }
    }

    //para retornar a trasação aberta, 
    public static function get()
    {
        return self::$conn;
    }

    //é um metodo que vai desfazer a transação, caso ocorra alguma falha no meio do processo
    public static function rollback()
    {
        if (self::$conn) {
            self::$conn->rollBack();
            self::$conn = null;
        }
    }

    public static function setLogger(Logger $logger)
    {
        self::$logger = $logger;
    }
    
    /*public static function logs(string $mensage)
    {
        if (self::$logger) {
            self::$logger->write($mensage);
        }
    }*/

    public static function logs(string $message, array $params = [])
    {
        if (self::$logger) {
            // Se houver parâmetros, transforma o array em string para o log
            if (!empty($params)) {
                $message .= " | Dados: " . json_encode($params, JSON_UNESCAPED_UNICODE);
            }
            self::$logger->write($message);
        }
    }
}
