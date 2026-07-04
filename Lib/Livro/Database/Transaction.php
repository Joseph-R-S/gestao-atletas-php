<?php
namespace Livro\Database;

use PDO;
use Livro\Log\Logger;

class Transaction
{
    private static ?PDO $conn = null;
    private static ?Logger $logger = null;
    public function __construct() {}

    public static function open(string $database)
    {
        self::$conn = Connection::open($database);
        self::$conn->beginTransaction();
    }

    public static function close()
    {
        if (self::$conn) {
            self::$conn->commit();
            self::$conn = null;
        }
    }

    public static function get()
    {
        return  self::$conn;
    }

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

    public static function log(string $mesagge)
    {
        if (self::$logger) {
            self::$logger->write($mesagge);
        }
    }
}
