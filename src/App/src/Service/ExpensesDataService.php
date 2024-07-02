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

    public function getExpenses($filters) 
    {
        $expensesQueryWithoutFilters = <<<SQLQUERY
        SELECT e.id, e.userId, e.categoryCompositionId, e.price, e.created, e.metatext, u.displayname FROM expenses e
        LEFT JOIN users u  ON e.userId = u.id ORDER BY e.created ASC;
        SQLQUERY;

        $expensesQueryWithFilters = <<<SQLQUERY
        SELECT e.id, e.userId, e.categoryCompositionId, e.price, e.created, e.metatext, u.displayname FROM expenses e
        LEFT JOIN users u  ON e.userId = u.id WHERE {{filterOptions}} ORDER BY e.created ASC;
        SQLQUERY;

        $expensesQueryPartCategoryIds = <<<SQLQUERYPART
        (
            SELECT count(*) FROM category_composition_members ccm
            LEFT JOIN category_compositions cc
            ON ccm.categoryCompositionId = cc.id
            WHERE cc.id = e.categoryCompositionId AND ccm.categoryId IN ({{filterOptionsCategoryIds}})
        ) > 0
        SQLQUERYPART;

        $sqlQuery = "";

        $queryFilters = [];

        array_key_exists("daterangeFilter", $filters) == true &&
        array_key_exists("from", $filters["daterangeFilter"]) == true && array_key_exists("to", $filters["daterangeFilter"]) == true
        ? array_push($queryFilters, "(e.created >= '".$filters["daterangeFilter"]["from"]."' AND e.created < '".$filters["daterangeFilter"]["to"]."')")
        : null;

        array_key_exists("metatextFilter", $filters) == true
        ? array_push($queryFilters, "LOWER(e.metatext) LIKE '%".$filters["metatextFilter"]."%'")
        : null;

        array_key_exists("categoriesFilter", $filters) == true
        ? array_push($queryFilters, 
                str_replace("{{filterOptionsCategoryIds}}", implode(", ", $filters["categoriesFilter"]), $expensesQueryPartCategoryIds)
            )
        : null;

        if(count($queryFilters) == 0) {
            $sqlQuery = $expensesQueryWithoutFilters;
        } else {
            $sqlQuery = $expensesQueryWithFilters;
        }

        $sqlQuery = str_replace("{{filterOptions}}", implode(" AND ", $queryFilters), $sqlQuery);

        try {
            $expenses = $this->db->query($sqlQuery, Adapter::QUERY_MODE_EXECUTE)->toArray();

            $total = 0;

            foreach($expenses as $expensesEntry) {
                $total += $expensesEntry['price'];
            }

            return [
                "expenses" => $expenses,
                "total" => $total,
            ];
        } catch (Exception $e) {
            return [
                "error" => 1,
                "errormsg" => $e->getMessage(),
            ];
        }    
    }

    public function insertExpenses($userId, $categoryCompositionId, $price, $metatext) 
    {
        $insertExpensesQuery = <<<SQLQUERY
        INSERT INTO expenses (userId, categoryCompositionId, price, created, metatext) VALUES(?, ?, ? , now(), ?);
        SQLQUERY;

        try {
            $this->db->query($insertExpensesQuery, [$userId, $categoryCompositionId, $price, $metatext]);
            return true;
        } catch (Exception $e) {
            return [
                "error" => 1,
                "errormsg" => $e->getMessage(),
            ];
        }       
    }

    public function updateExpenses($userId, $id, $data)
    {
        $updateColumns = [];

        array_key_exists("created", $data) == true ? array_push($updateColumns, "created = '".$data["created"]."'") : null;
        array_key_exists("metatext", $data) == true ? array_push($updateColumns, "metatext = '".$data["metatext"]."'") : null;
        array_key_exists("categoryCompositionId", $data) == true ? array_push($updateColumns, "categoryCompositionId = '".$data["categoryCompositionId"]."'") : null;
        array_key_exists("price", $data) == true ? array_push($updateColumns, "price = '".$data["price"]."'") : null;
        
        $updateExpensesQuery = <<<SQLQUERY
        UPDATE expenses SET userId = ?, {{columns}} WHERE id = ?;
        SQLQUERY;

        $fullQuery = str_replace("{{columns}}", implode(", ", $updateColumns), $updateExpensesQuery);

        try {
            $this->db->query($fullQuery, [ $userId, $id ]);
        return true;
        
        } catch (Exception $e) {
            return [
                "error" => 1,
                "errormsg" => $e->getMessage(),
            ];
        }
    }

    public function deleteExpenses($id)
    {
        try {
                $this->db->query("DELETE FROM expenses WHERE id = ?", [ $id ]);
            return true;
            
        } catch (Exception $e) {
            return [
                "error" => 1,
                "errormsg" => $e->getMessage(),
            ];
        }
    }
}