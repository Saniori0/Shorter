<?php


namespace Shorter\Backend\App\Models;

use Shorter\Backend\App\Database\Connection;

abstract class AbstractModel
{

    protected static function getMysqlPdo(): \PDO
    {

        return Connection::getMysqlPdo();

    }

}