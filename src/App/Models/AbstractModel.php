<?php


namespace Shorter\Backend\App\Models;

use Shorter\Backend\App\Database\Connection;

abstract class AbstractModel
{

    protected static string $tableName;

    protected static function getMysqlPdo(): \PDO
    {

        return Connection::getMysqlPdo();

    }


    #[\ReturnTypeWillChange]
    protected static function findByField(string $field, string|int|float|bool $value)
    {

        $tableName = static::$tableName;

        $Statement = self::getMysqlPdo()->prepare("SELECT * FROM $tableName WHERE $field = ?");
        $Statement->execute([$value]);

        return @$Statement->fetchAll(\PDO::FETCH_ASSOC)[0];

    }

}