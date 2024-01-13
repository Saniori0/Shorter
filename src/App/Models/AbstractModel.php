<?php


namespace Shorter\Backend\App\Models;

use PDO;
use ReturnTypeWillChange;
use Shorter\Backend\App\Database\Connection;

abstract class AbstractModel
{

    protected static string $tableName;
    protected int $id;

    #[ReturnTypeWillChange]
    protected static function findByField(string $field, string|int|float|bool $value)
    {

        $tableName = static::$tableName;

        $Statement = self::getMysqlPdo()->prepare("SELECT * FROM $tableName WHERE $field = ?");
        $Statement->execute([$value]);

        return @$Statement->fetchAll(PDO::FETCH_ASSOC)[0];

    }

    public static function findById(int $id): false|static
    {

        return static::findByField("id", $id);

    }

    protected static function getMysqlPdo(): PDO
    {

        return Connection::getMysqlPdo();

    }

    public function getId(): int
    {
        return $this->id;
    }

}