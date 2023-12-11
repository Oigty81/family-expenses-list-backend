<?php

use Laminas\Db\Adapter\Adapter;

return function($config, $dbAdapter) {
    
    try {

        $connection = $dbAdapter->getDriver()->getConnection();
        $connection->beginTransaction();

        foreach(array_reverse($this->config["tableNames"]) as $table) {
            $dbAdapter->query("DELETE FROM ".$table, Adapter::QUERY_MODE_EXECUTE);
        }

        foreach(array_reverse($this->config["tableNames"]) as $table) {
            $dbAdapter->query("DROP TABLE IF EXISTS ".$table, Adapter::QUERY_MODE_EXECUTE);
        }

        $connection->commit();
        
    } catch (Throwable $e) {
        
        return "error DbCleaner.".$e->getMessage();
    }
    
    return true;
};
