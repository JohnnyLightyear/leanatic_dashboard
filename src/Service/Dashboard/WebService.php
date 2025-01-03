<?php

namespace App\Service\Dashboard;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;

class WebService
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function format(): array
    {
        try {
            $result = $this->fetchData();
        } catch (\Exception $exception) {
            return [];
        }

        // Order by origin
        $ordered = [];
        foreach ($result as $item) {
            $ordered[$item['origin']][$item['action']] = $item['cnt'];
        }

        return $ordered;
    }

    public function fetchData(): ?array
    {
        $result = $this->connection->fetchAllAssociative('SELECT action, origin, COUNT(*) as cnt FROM webhook_dashboard WHERE creation_date > ? GROUP BY origin, `action`', [Carbon::now('UTC')->startOfDay()]);

        return $result;
    }
}