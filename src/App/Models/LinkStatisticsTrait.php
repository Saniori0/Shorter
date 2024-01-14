<?php

namespace Shorter\Backend\App\Models;

use PDO;
use Shorter\Backend\Utils\Pagination;
use Shorter\Backend\Http\Request;
use Shorter\Backend\Utils\SypexGeo;

trait LinkStatisticsTrait
{

    public function replenishStats(): void
    {

        $geo = new SypexGeo();
        $ip = Request::getInstance()->getClientIp();
        $countryCode = $geo->getCountry($ip);
        $time = time();

        if ($countryCode == "") $countryCode = "--";

        $Statement = self::getMysqlPdo()->prepare("INSERT INTO statistics (ip, country, time, link) VALUES (?, ?, ?, ?)");

        $Statement->execute([
            $ip,
            $countryCode,
            $time,
            $this->getId(),
        ]);

    }

    public function countStatisticsRows(): int
    {

        $Statement = self::getMysqlPdo()->prepare("SELECT count(id) as quantity FROM statistics WHERE link = {$this->id}");
        $Statement->execute();

        $quantity = $Statement->fetch(PDO::FETCH_ASSOC);

        return $quantity["quantity"];

    }

    public function countStatisticsPages(): int
    {

        return $this->generateStatisticsPagination()->countPages();

    }

    private function generateStatisticsPagination(): Pagination
    {

        $Pagination = new Pagination("statistics");
        $Pagination->where("link = ?", [$this->getId()]);

        return $Pagination;

    }

    public function getStatisticsWithPagination(int $page = 1): array
    {

        return $this->generateStatisticsPagination()->getRowsByPageNumber($page);

    }

    public function getStatisticsByCountry(): array
    {

        $Statement = self::getMysqlPdo()->prepare("SELECT count(id) as quantity, country FROM statistics WHERE link = {$this->id} GROUP BY country");
        $Statement->execute();

        $fetchedCountryStatistics = $Statement->fetchAll(PDO::FETCH_ASSOC);
        $servedCountryStatistics = [];

        foreach ($fetchedCountryStatistics as &$fetchedCountryStatistic) {

            $servedCountryStatistics[$fetchedCountryStatistic["country"]] = $fetchedCountryStatistic["quantity"];

        }

        return $servedCountryStatistics;

    }

}