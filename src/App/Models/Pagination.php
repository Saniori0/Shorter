<?php


namespace Shorter\Backend\App\Models;


use Shorter\Backend\App\Models\AbstractModel;

class Pagination extends AbstractModel
{

    private string $whereQuery = "";
    private string $primaryKey = "id";
    private int $itemPerPage = 5;

    public function __construct(private string $tableName)
    {
    }

    public function where(string $query, array $bindings): void
    {

        $count = 1;

        foreach ($bindings as $binding) {

            $query = str_replace("?", $binding, $query, $count);

        }

        $this->whereQuery = $query;

    }

    public function countPages(): float
    {

        $Statement = $this->getMysqlPdo()->prepare("SELECT count(*) FROM {$this->getTableName()} WHERE {$this->getWhereQuery()}");
        $Statement->execute();
        $rowsQuantity = $Statement->fetch()[0];

        return ceil($rowsQuantity / $this->getItemPerPage());

    }

    public function countPageOffset(int $page = 1): int
    {

        return ($page - 1) * $this->getItemPerPage();

    }

    private function prepareStatementByPageNumber(int $page = 1): false|\PDOStatement
    {

        return $this->getMysqlPdo()->prepare("SELECT * FROM {$this->getTableName()} WHERE {$this->getWhereQuery()} ORDER BY {$this->getPrimaryKey()} DESC LIMIT {$this->getItemPerPage()} OFFSET {$this->countPageOffset($page)};");

    }

    public function getRowsByPageNumber(int $page = 1): array
    {

        $Statement = $this->prepareStatementByPageNumber($page);

        if(!$Statement){

            return [];

        }

        $Statement->execute();

        return $Statement->fetchAll(\PDO::FETCH_ASSOC);

    }

    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    public function setPrimaryKey(string $primaryKey): void
    {
        $this->primaryKey = $primaryKey;
    }

    public function getWhereQuery(): string
    {
        return $this->whereQuery;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getItemPerPage(): int
    {
        return $this->itemPerPage;
    }

    public function setItemPerPage(int $itemPerPage): void
    {
        $this->itemPerPage = $itemPerPage;
    }

}


$Pagination = new Pagination("statistics");
$Pagination->where("link = ?", [1]);
$Pagination->getRowsByPageNumber(1);