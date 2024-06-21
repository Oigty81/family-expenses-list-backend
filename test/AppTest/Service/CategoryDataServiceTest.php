<?php

declare(strict_types=1);

namespace AppTest\Service;

use App\Service\CategoryDataService;
use App\Service\BackendServiceService;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

use Laminas\Db\Adapter\Adapter;
use Psr\Log\LoggerInterface;

use Laminas\Db\Adapter\Exception\InvalidQueryException;

class CategoryDataServiceTest extends TestCase
{
    use ProphecyTrait;

    protected $dataBaseAdapter;

    protected $config;

    /** @var LoggerInterface|ObjectProphecy */
    protected $loggerInterface;

    protected function setUp() : void
    {
        $this->dataBaseAdapter = require "Test/AppTest/_helper/DbAdapterLoader.php";
        $this->config = require "Test/AppTest/_helper/ConfigLoader.php";
        $this->loggerInterface = $this->prophesize(LoggerInterface::class);
    }

    protected function tearDown() : void 
    {
        $dbCleaner = require "Test/AppTest/_helper/DbCleaner.php";
        $dbCleaner($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());
    }

    public function testWheterGetAllStoredCategoriesAndCategoryCompositions(): void
    {
        $backendServiceService = new BackendServiceService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());
        $backendServiceService->createTableStruct();
        $backendServiceService->createUser("testuser", "unit1234test", "theTestUser");

        $userId = $this->dataBaseAdapter->query(
            "SELECT id FROM users WHERE userName = 'testuser';",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray()[0]["id"];

        $this->dataBaseAdapter->query("INSERT INTO categories (title, created, userId) VALUES (?, now(), ?);",
            ["TestCategory1", $userId]
        );

        $categoryId1 = $this->dataBaseAdapter->query(
            "SELECT LAST_INSERT_ID();",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray()[0]["LAST_INSERT_ID()"];

        $this->dataBaseAdapter->query(
            "INSERT INTO categories (title, created, userId) VALUES (?, now(), ?);",
            ["TestCategory2", $userId]
        );

        $categoryId2 = $this->dataBaseAdapter->query(
            "SELECT LAST_INSERT_ID();",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray()[0]["LAST_INSERT_ID()"];


        $this->dataBaseAdapter->query(
            "INSERT INTO category_compositions (created, userId) VALUES (now(), ?);",
            [$userId]
        );
        
        $categoryCompositionId1 = $this->dataBaseAdapter->query(
            "SELECT LAST_INSERT_ID();",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray()[0]["LAST_INSERT_ID()"];

        $this->dataBaseAdapter->query(
            "INSERT INTO category_compositions (created, userId) VALUES (now(), ?);",
            [$userId]
        );
        
        $categoryCompositionId2 = $this->dataBaseAdapter->query(
            "SELECT LAST_INSERT_ID();",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray()[0]["LAST_INSERT_ID()"];

        $this->dataBaseAdapter->query(
            "INSERT INTO category_composition_members (categoryId, categoryCompositionId ) VALUES (?, ?);",
            [$categoryId1, $categoryCompositionId1]
        );

        $this->dataBaseAdapter->query(
            "INSERT INTO category_composition_members (categoryId, categoryCompositionId ) VALUES (?, ?);",
            [$categoryId1, $categoryCompositionId2]
        );

        $this->dataBaseAdapter->query(
            "INSERT INTO category_composition_members (categoryId, categoryCompositionId ) VALUES (?, ?);",
            [$categoryId2, $categoryCompositionId2]
        );

        $categoryDataService = new CategoryDataService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());

        $result = $categoryDataService->getCategories();

        $this->assertIsArray($result);
        $this->assertEquals(true, array_key_exists("categoriesData", $result));
        $this->assertEquals(true, array_key_exists("categoryCompositionsData", $result));
        $this->assertEquals(2, count($result["categoriesData"]));
        $this->assertEquals(2, count($result["categoryCompositionsData"]));

        $resultCat1 = false;
        $resultCat2 = false;

        foreach($result["categoriesData"] as $cD) {
            if(strcmp($cD["title"], "TestCategory1")) {
                $resultCat1 = true;
            }
            if(strcmp($cD["title"], "TestCategory2")) {
                $resultCat2 = true;
            }
        }

        $this->assertEquals(true, $resultCat1);
        $this->assertEquals(true, $resultCat2);

        $this->assertEquals(true, array_key_exists("categories", $result["categoryCompositionsData"][0]));
        $this->assertEquals(true, array_key_exists("categories", $result["categoryCompositionsData"][1]));

        $this->assertEquals(1, count($result["categoryCompositionsData"][0]["categories"]));
        $this->assertEquals(2, count($result["categoryCompositionsData"][1]["categories"]));

    }

    public function testWheterStoreCategoriesIsWorksForCurrentUser(): void
    {
        $backendServiceService = new BackendServiceService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());
        $backendServiceService->createTableStruct();
        $backendServiceService->createUser("testuser", "unit1234test", "theTestUser");

        $backendServiceService->createInitialContent("testuser");

        $userId = $this->dataBaseAdapter->query(
            "SELECT id FROM users WHERE userName = 'testuser';",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray()[0]["id"];


        $categoryDataService = new CategoryDataService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());

        $result = $categoryDataService->storeCategory($userId, "TestCategory100");
        $this->assertEquals(true, $result);
        $result = $categoryDataService->storeCategory($userId, "TestCategory200");
        $this->assertEquals(true, $result);
        $result = $categoryDataService->storeCategory($userId, "TestCategory300");
        $this->assertEquals(true, $result);
        
        $categories = $this->dataBaseAdapter->query(
            "SELECT id, title FROM categories ORDER BY id;",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray();

        $this->assertEquals(7, count($categories));

        $hasTestCategory100 = false;
        $hasTestCategory200 = false;
        $hasTestCategory300 = false;

        foreach( $categories as $category ) {
            if(strcmp($category["title"], "TestCategory100")) { $hasTestCategory100 = true; }
            if(strcmp($category["title"], "TestCategory200")) { $hasTestCategory200 = true; }
            if(strcmp($category["title"], "TestCategory300")) { $hasTestCategory300 = true; }
        }

        $this->assertEquals(true, $hasTestCategory100);
        $this->assertEquals(true, $hasTestCategory200);
        $this->assertEquals(true, $hasTestCategory300);
    }

    public function testWheterStoreCategoriesThrowsExceptionWhenTitleNameIsDuplicate(): void
    {
        $backendServiceService = new BackendServiceService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());
        $backendServiceService->createTableStruct();
        $backendServiceService->createUser("testuser", "unit1234test", "theTestUser");

        $backendServiceService->createInitialContent("testuser");

        $userId = $this->dataBaseAdapter->query(
            "SELECT id FROM users WHERE userName = 'testuser';",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray()[0]["id"];

        $categoryDataService = new CategoryDataService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());
        
        $categoryDataService->storeCategory($userId, "TestCategory100");
        $categoryDataService->storeCategory($userId, "TestCategory200");
        $categoryDataService->storeCategory($userId, "TestCategory300");
        
        $result = $categoryDataService->storeCategory($userId, "TestCategory300");
        $this->assertStringContainsString("Duplicate entry 'TestCategory300'", $result["errormsg"]);   
    }

    public function testWheterStoreCategoryCompositionIsWorksForCurrentUser(): void
    {
        $backendServiceService = new BackendServiceService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());
        $backendServiceService->createTableStruct();
        $backendServiceService->createUser("testuser", "unit1234test", "theTestUser");

        $backendServiceService->createInitialContent("testuser");

        $userId = $this->dataBaseAdapter->query(
            "SELECT id FROM users WHERE userName = 'testuser';",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray()[0]["id"];


        $categoryDataService = new CategoryDataService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());

        $categoryDataService->storeCategory($userId, "TestCategory100");
        $categoryDataService->storeCategory($userId, "TestCategory200");
        $categoryDataService->storeCategory($userId, "TestCategory300");

        $categories = $this->dataBaseAdapter->query(
            "SELECT id, title FROM categories ORDER BY id;",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray();

        $result = $categoryDataService->storeCategoryComposition($userId, [ $categories[0]["id"], $categories[2]["id"], $categories[3]["id"] ]);
        $this->assertEquals(true, $result);
        $result = $categoryDataService->storeCategoryComposition($userId, [ $categories[1]["id"], $categories[4]["id"] ]);
        $this->assertEquals(true, $result);

        $categoryDataForValidation = $categoryDataService->getCategories();

        $this->assertEquals(2, count($categoryDataForValidation["categoryCompositionsData"]));
        $this->assertEquals(3, count($categoryDataForValidation["categoryCompositionsData"][0]["categories"]));
        $this->assertEquals(2, count($categoryDataForValidation["categoryCompositionsData"][1]["categories"]));
        
        $this->assertEquals(1, 1);
    }
}