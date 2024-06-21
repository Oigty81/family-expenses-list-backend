<?php

use Laminas\Db\Adapter\Adapter;
use App\Service\BackendServiceService;

return function($config, $dbAdapter, $logger) {
    $backendServiceService = new BackendServiceService($config, $dbAdapter, $logger);
    try {
        $sqlTables = $backendServiceService->getSqlTables();

        $connection = $dbAdapter->getDriver()->getConnection();
        $connection->beginTransaction();

        foreach(array_reverse($sqlTables) as $table) {
            $dbAdapter->query("DELETE FROM ".$table, Adapter::QUERY_MODE_EXECUTE);
        }

        foreach(array_reverse($sqlTables) as $table) {
            $dbAdapter->query("DROP TABLE IF EXISTS ".$table, Adapter::QUERY_MODE_EXECUTE);
        }

        $connection->commit();
        
    } catch (Throwable $e) {
        
        return "error DbCleaner.".$e->getMessage();
    }
    
    return true;
};
