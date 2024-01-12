<?php


namespace Shorter\Backend\App;

class DatabaseConnection
{

    private static \PDO $mysqlPdo;

    public static function getMysqlPdo(): \PDO
    {

        return self::$mysqlPdo;

    }

    public static function setMysqlPdo(string $dsn, string $username, string $password): void
    {

        self::$mysqlPdo = new \PDO($dsn, $username, $password);

    }

}