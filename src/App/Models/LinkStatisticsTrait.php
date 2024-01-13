<?php

namespace Shorter\Backend\App\Models;

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

        if($countryCode == "") $countryCode = "--";

        $Statement = self::getMysqlPdo()->prepare("INSERT INTO statistics (ip, country, time, link) VALUES (?, ?, ?, ?)");

        $Statement->execute([
            $ip,
            $countryCode,
            $time,
            $this->getId(),
        ]);

    }

}