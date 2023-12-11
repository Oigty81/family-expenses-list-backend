<?php

declare(strict_types=1);

namespace App\Service;

use Laminas\Db\Adapter\Adapter;
use Psr\Log\LoggerInterface;

use Exception;

class ExpensesDataService
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

    /**
     * @var CategoryDataService
     */
    private $categoryDataService;

    public function __construct(
        array $config,
        Adapter $db,
        LoggerInterface $logger,
        CategoryDataService $categoryDataService,
    )
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->db = $db;
        $this->categoryDataService = $categoryDataService;
    }

    public function fetchExpensesPeriod($from, $to) 
    {
        try {
            $spendings = $this->db->query(
                "SELECT a.userId, a.categoryCompositionId, a.price, a.created, a.metatext, b.displayname FROM "
                .$this->config["tableNames"]["expenses"]." a LEFT JOIN "
                .$this->config["tableNames"]["users"]." b  ON a.userId = b.id WHERE a.created > ? AND a.created < ? ORDER BY a.created;",
                [
                    $from,
                    $to
                ]
            )->toArray();
    
            return $spendings;
        } catch (Exception $e) {
            return [
                "error" => 1,
                "errormsg" => $e->getMessage(),
            ];
        }    
    }

    public function insertExpenses($userId, $categoryCompositionId, $price, $metatext) 
    {
        try {
            $this->db->query(
                "INSERT INTO ".$this->config["tableNames"]["expenses"]." (userId, categoryCompositionId, price, created, metatext) VALUES(?, ?, ? , now(), ?);",
                [
                    $userId,
                    $categoryCompositionId,
                    $price,
                    $metatext
                ]
                );
            return true;
        } catch (Exception $e) {
            return [
                "error" => 1,
                "errormsg" => $e->getMessage(),
            ];
        }       
    }
}