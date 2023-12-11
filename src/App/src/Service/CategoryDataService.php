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
        try {

            $categoriesData = $this->db->query(
                "SELECT id, title FROM ".$this->config["tableNames"]["categories"]." ORDER BY id;",
                Adapter::QUERY_MODE_EXECUTE
            )->toArray();
            
            $categoryCompositions = $this->db->query("SELECT id FROM ".$this->config["tableNames"]["category_compositions"].";", Adapter::QUERY_MODE_EXECUTE)->toArray();

            $categoryCompositionsData = [];

            foreach ($categoryCompositions as $categoryCompositionsMember) {
                $categoryCompositionsForMember = $this->db->query(
                    "SELECT b.title as category FROM ".$this->config["tableNames"]["category_composition_members"]." a LEFT JOIN ".$this->config["tableNames"]["categories"]." b ON a.categoryId = b.id WHERE a.categoryCompositionId = ?;",
                    [
                        $categoryCompositionsMember["id"]
                    ]
                )->toArray();
                
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
        try {
            $this->db->query(
                "INSERT INTO ".$this->config["tableNames"]["categories"]." (title, created, userId) VALUES (?, now(), ?);",
                [
                    $title,
                    $userId
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

    public function storeCategoryComposition($userId, $categoryIds)
    {
        if(count($categoryIds) == 0) {
            $ex = new ArrayContentException("no categories in request found!");
            throw $ex;
        }
        
        $connection = $this->db->getDriver()->getConnection();
        $connection->beginTransaction();

        try {
            $this->db->query(
                "INSERT INTO ".$this->config["tableNames"]["category_compositions"]." (created, userId) VALUES (now(), ?);",
                [
                    $userId
                ]
            );
            
            $categoryCompositionIdResult = $this->db->query(
                "SELECT LAST_INSERT_ID() AS lastCategoryCompositionId;",
                Adapter::QUERY_MODE_EXECUTE
            )->toArray();
            
            $categoryCompositionId = $categoryCompositionIdResult[0]["lastCategoryCompositionId"];
    
    
            foreach ($categoryIds as $categoryId) {
                $this->db->query(
                    "INSERT INTO ".$this->config["tableNames"]["category_composition_members"]." (categoryId, categoryCompositionId ) VALUES (?, ?);",
                    [
                        $categoryId,
                        $categoryCompositionId,
                    ]
                );
            }
        } catch (Exception $e) {
            $connection->rollback();
            return [
                "error" => 1,
                "errormsg" => $e->getMessage(),
            ];
        }
       
        $connection->commit();
        return true;
    }
}