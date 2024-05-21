<?php

declare(strict_types=1);

namespace App\Service;

use Laminas\Db\Adapter\Adapter;
use Psr\Log\LoggerInterface;

use App\Exception\ArrayContentException;

use Exception;

class CategoryDataService
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
        $this->logger = $logger;
        $this->db = $db;
    }

    public function getCategories()
    {
        $categoriesDataQuery = <<<SQLQUERY
        SELECT id, title FROM categories ORDER BY id;
        SQLQUERY;

        $categoryCompositionsQuery = <<<SQLQUERY
        SELECT id FROM category_compositions;
        SQLQUERY;

        $categoryCompositionsForMemberQuery = <<<SQLQUERY
        SELECT c.title as category FROM category_composition_members ccm LEFT JOIN categories c ON ccm.categoryId = c.id WHERE ccm.categoryCompositionId = ?
        SQLQUERY;

        try {
            $categoriesData = $this->db->query($categoriesDataQuery, Adapter::QUERY_MODE_EXECUTE)->toArray();
            $categoryCompositions = $this->db->query($categoryCompositionsQuery, Adapter::QUERY_MODE_EXECUTE)->toArray();
            $categoryCompositionsData = [];

            foreach ($categoryCompositions as $categoryCompositionsMember) {
                $categoryCompositionsForMember = $this->db->query( $categoryCompositionsForMemberQuery, [$categoryCompositionsMember["id"]])->toArray();
                
                if(count($categoryCompositionsForMember) > 0) {
                    $categories = [];

                    foreach ( $categoryCompositionsForMember as $member) {
                        array_push($categories, $member["category"]);
                    }

                    $categoryCompositionData = [ 
                        "categoryCompositionId" => $categoryCompositionsMember["id"],
                        "categories" => $categories
                    ];

                    array_push($categoryCompositionsData, $categoryCompositionData);
                }
            }
                    
            return [ 
                "categoriesData" => $categoriesData,
                "categoryCompositionsData" => $categoryCompositionsData,
            ];
        } catch (Exception $e) {
            return [
                "error" => 1,
                "errormsg" => $e->getMessage(),
            ];
        }

    }

    public function storeCategory($userId, $title)
    {
        $insertCategoryQuery = <<<SQLQUERY
        INSERT INTO categories (title, created, userId) VALUES (?, now(), ?);
        SQLQUERY;

        try {
            $this->db->query($insertCategoryQuery, [$title, $userId]);
            return true;
        } catch (Exception $e) {
            return [
                "error" => 1,
                "errormsg" => $e->getMessage(),
            ];
        }
    }

    public function storeCategoryComposition($userId, $categoryIds)
    {
        $insertCategoryCompositionsQuery = <<<SQLQUERY
        INSERT INTO category_compositions (created, userId) VALUES (now(), ?);
        SQLQUERY;

        $insertCategoryCompositionMembersQuery = <<<SQLQUERY
        INSERT INTO category_composition_members (categoryId, categoryCompositionId ) VALUES (?, ?);
        SQLQUERY;

        if(count($categoryIds) == 0) {
            $ex = new ArrayContentException("no categories in request found!");
            throw $ex;
        }
        
        $this->db->getDriver()->getConnection()->beginTransaction();
       
        try {
            $this->db->query($insertCategoryCompositionsQuery, [$userId]);
            
            $categoryCompositionIdResult = $this->db->query("SELECT LAST_INSERT_ID() AS lastCategoryCompositionId;", Adapter::QUERY_MODE_EXECUTE)->toArray();
            $categoryCompositionId = $categoryCompositionIdResult[0]["lastCategoryCompositionId"];
    
            foreach ($categoryIds as $categoryId) {
                $this->db->query($insertCategoryCompositionMembersQuery, [$categoryId, $categoryCompositionId]);
            }
            $this->db->getDriver()->getConnection()->commit();
            return true;
        } catch (Exception $e) {
            $this->db->getDriver()->getConnection()->rollback();
            return [
                "error" => 1,
                "errormsg" => $e->getMessage(),
            ];
        }
    }
}