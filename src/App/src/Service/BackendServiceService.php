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

    public function createTableStruct()
    {
        try {
            $this->db->query($this->config["createTableUsersIfNotExist"], Adapter::QUERY_MODE_EXECUTE);
            $this->db->query($this->config["createTableCategoriesIfExist"], Adapter::QUERY_MODE_EXECUTE);
            $this->db->query($this->config["createTableCategoryCompositionsIfExist"], Adapter::QUERY_MODE_EXECUTE);
            $this->db->query($this->config["createTableCategoryCompositionMembersIfExist"], Adapter::QUERY_MODE_EXECUTE);
            $this->db->query($this->config["createTableExpensesIfExist"], Adapter::QUERY_MODE_EXECUTE);
        } catch (Throwable $e) {
            return "error in backendservice__createTableStruct.. ".$e->getMessage();
        }
        return true;
    }

    public function createInitialContent($username)
    {
        $connection = $this->db->getDriver()->getConnection();
        $connection->beginTransaction();

        $userResult = $this->db->query("SELECT id FROM ".$this->config["tableNames"]["users"]." WHERE username = ?;", [ $username ])->toArray();

        if(count($userResult) == 1) {
            $userId = $userResult[0]["id"];
            try {
                foreach ($this->config["preCategories"] as $key => $value) {
                    $this->db->query("INSERT INTO ".$this->config["tableNames"]["categories"]." (title, created, userId) VALUES('".$value."', now(), ".$userId.")", Adapter::QUERY_MODE_EXECUTE);
                }
            } catch (Throwable $e) {
                return "error in backendservice__createInitialContent.. ".$e->getMessage();
            }
            $connection->commit();
            return true;
        } else {
            return "user: ".$username." not found !!!";
        }
    }

    public function deleteAllEntries()
    {
        try {
            $connection = $this->db->getDriver()->getConnection();
            $connection->beginTransaction();

            foreach(array_reverse($this->config["tableNames"]) as $table) {
                $this->db->query("DELETE FROM ".$table, Adapter::QUERY_MODE_EXECUTE);
            }

            $connection->commit();
        } catch (Throwable $e) {
            return "error in backendservice__deleteAllEntries.. ".$e->getMessage();
        }
        
        return true;
    }

    public function deleteAllTables()
    {
        try {
            foreach(array_reverse($this->config["tableNames"]) as $table) {
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
                "INSERT INTO ".$this->config["tableNames"]["users"]." (username, password, displayname, lastlogin) VALUES (?, ?, ?, now());",
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
                "DELETE FROM ".$this->config["tableNames"]["users"]." WHERE username = ?;", 
                [$userName]
            );
        } catch (Throwable $e) {
            return "error in backendservice__deleteUser.. ".$e->getMessage();
        }
        return true;
    }
}
