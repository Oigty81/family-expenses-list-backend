<?php

declare(strict_types=1);

namespace App\Service;

use Throwable;
use Laminas\Db\Adapter\Adapter;
use Psr\Log\LoggerInterface;

class BackendServiceService
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Adapter
     */
    private $db;

    public function __construct(
        array $config,
        Adapter $db,
        LoggerInterface $logger
    )
    {
        $this->config = $config;
        $this->db = $db;
        $this->logger = $logger;
    }

    public function getSqlTables() 
    {
        $sqlTables = [];
        foreach($this->config as $key=>$value) {
            if(str_contains($key, "createTable_") && str_contains($key, "_IfNotExist")) {
                $key = str_replace("createTable_", "", $key);
                $key = str_replace("_IfNotExist", "", $key);
                array_push($sqlTables, $key);
            }
        }

        return $sqlTables;
    }

    public function getSqlTableQuerys() 
    {
        $queryText ="";

        foreach($this->getSqlTables() as $table) {
            $queryText .= $this->config["createTable_".$table."_IfNotExist"]."\r\n\r\n";
        }

        $queryText .="-------------------------\r\n\r\n";

        foreach(array_reverse($this->getSqlTables()) as $table) {
            $queryText .= "DELETE FROM ".$table.";\r\n";
        }

        return $queryText;
    }

    public function createTableStruct()
    {
        try {
            foreach($this->getSqlTables() as $table) {
                $this->db->query($this->config["createTable_".$table."_IfNotExist"], Adapter::QUERY_MODE_EXECUTE);
            }
        } catch (Throwable $e) {
            return "error in backendservice__createTableStruct.. ".$e->getMessage();
        }
        return true;
    }

    public function createInitialContent($username)
    {
        $this->db->getDriver()->getConnection()->beginTransaction();

        $userResult = $this->db->query("SELECT id FROM users WHERE username = ?;", [ $username ])->toArray();

        if(count($userResult) == 1) {
            $userId = $userResult[0]["id"];
            try {
                foreach ($this->config["preCategories"] as $key => $value) {
                    $this->db->query("INSERT INTO categories (title, created, userId) VALUES('".$value."', now(), ".$userId.")", Adapter::QUERY_MODE_EXECUTE);
                }
            } catch (Throwable $e) {
                return "error in backendservice__createInitialContent.. ".$e->getMessage();
            }
            $this->db->getDriver()->getConnection()->commit();
            return true;
        } else {
            $this->db->getDriver()->getConnection()->rollback();
            return "user: ".$username." not found !!!";
        }
    }

    public function deleteAllEntries()
    {
        try {
            $this->db->getDriver()->getConnection()->beginTransaction();

            foreach(array_reverse($this->getSqlTables()) as $table) {
                $this->db->query("DELETE FROM ".$table, Adapter::QUERY_MODE_EXECUTE);
            }

            $this->db->getDriver()->getConnection()->commit();
        } catch (Throwable $e) {
            $this->db->getDriver()->getConnection()->rollback();
            return "error in backendservice__deleteAllEntries.. ".$e->getMessage();
        }
        
        return true;
    }

    public function deleteAllTables()
    {
        try {
            foreach(array_reverse($this->getSqlTables()) as $table) {
                $this->db->query("DROP TABLE IF EXISTS ".$table, Adapter::QUERY_MODE_EXECUTE);
            }
        } catch (Throwable $e) {
            return "error in backendservice__deleteAllTables.. ".$e->getMessage();
        }
        
        return true;
    }

    public function createUser($username, $password, $displayname)
    {
        try {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT); // PASSWORD_BCRYPT

            $this->db->query(
                "INSERT INTO users (username, password, displayname, lastlogin) VALUES (?, ?, ?, now());",
                [$username, $passwordHash, $displayname]
            );
        } catch (Throwable $e) {
            return "error in backendservice__createUser.. ".$e->getMessage();
        }
        
        return true;
    }

    public function deleteUser($userName)
    {
        try {
            $this->db->query(
                "DELETE FROM users WHERE username = ?;", 
                [$userName]
            );
        } catch (Throwable $e) {
            return "error in backendservice__deleteUser.. ".$e->getMessage();
        }
        return true;
    }
}
