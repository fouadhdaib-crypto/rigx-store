<?php
declare(strict_types=1);

require_once __DIR__ . '/DashboardClass.php';

class ModelFactory
{
    private function __construct()
    {
    }

    public static function dashboard(): DashboardBAL
    {
        $connection = DB_conn::getConnection();

        $dal = new DashboardDAL($connection);

        return new DashboardBAL($dal);
    }
}