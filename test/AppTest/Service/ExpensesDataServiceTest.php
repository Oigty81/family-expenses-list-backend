<?php

declare(strict_types=1);

namespace AppTest\Service;

use App\Service\ExpensesDataService;
use App\Service\CategoryDataService;
use App\Service\BackendServiceService;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

use Laminas\Db\Adapter\Adapter;
use Psr\Log\LoggerInterface;

use Laminas\Db\Adapter\Exception\InvalidQueryException;


class ExpensesDataServiceTest extends TestCase
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
        $dbCleaner($this->config, $this->dataBaseAdapter);
    }

    public function testWheterFetchExpensesPeriodIsWorks(): void
    {
        $backendServiceService = new BackendServiceService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());
        $backendServiceService->createTableStruct();
        $backendServiceService->createUser("testuser", "unit1234test", "theTestUser");

        $backendServiceService->createInitialContent("testuser");

        $userId = $this->dataBaseAdapter->query(
            "SELECT id FROM ".$this->config["tableNames"]["users"]." WHERE userName = 'testuser';",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray()[0]["id"];

        $categoryDataService = new CategoryDataService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());

        $categoryDataService->storeCategory($userId, "TestCategory100");
        $categoryDataService->storeCategory($userId, "TestCategory200");
        $categoryDataService->storeCategory($userId, "TestCategory300");

        $categories = $this->dataBaseAdapter->query(
            "SELECT id, title FROM ".$this->config["tableNames"]["categories"]." ORDER BY id;",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray();

        $categoryDataService->storeCategoryComposition($userId, [ $categories[0]["id"], $categories[2]["id"], $categories[3]["id"] ]);
        $categoryDataService->storeCategoryComposition($userId, [ $categories[1]["id"], $categories[4]["id"] ]);
      
        $categoryData = $categoryDataService->getCategories();

        $expensesDataService = new ExpensesDataService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal(), $categoryDataService);

        $expensesDataService->insertExpenses($userId, $categoryData["categoryCompositionsData"][0]["categoryCompositionId"], 10.33, "test spending 1");
        $expensesDataService->insertExpenses($userId, $categoryData["categoryCompositionsData"][1]["categoryCompositionId"], 20.99, "test spending 2");
        $expensesDataService->insertExpenses($userId, $categoryData["categoryCompositionsData"][0]["categoryCompositionId"], 40, "test spending 3");
       
        $this->dataBaseAdapter->query(
            "INSERT INTO ".$this->config["tableNames"]["expenses"]." (userId, categoryCompositionId, price, created, metatext) VALUES(?, ?, ? , ?, ?);",
            [$userId, $categoryData["categoryCompositionsData"][0]["categoryCompositionId"], 99.95, "2023-09-10 20:00", "test spending 1" ]
        );

        $this->dataBaseAdapter->query(
            "INSERT INTO ".$this->config["tableNames"]["expenses"]." (userId, categoryCompositionId, price, created, metatext) VALUES(?, ?, ? , ?, ?);",
            [$userId, $categoryData["categoryCompositionsData"][0]["categoryCompositionId"], 69.95, "2023-09-10 20:15", "test spending 2" ]
        );

        $this->dataBaseAdapter->query(
            "INSERT INTO ".$this->config["tableNames"]["expenses"]." (userId, categoryCompositionId, price, created, metatext) VALUES(?, ?, ? , ?, ?);",
            [$userId, $categoryData["categoryCompositionsData"][0]["categoryCompositionId"], 149.95, "2023-09-13 20:15", "test spending 3" ]
        );

        $expensesSelection1 = $expensesDataService->fetchExpensesPeriod("2023-09-09 20:15", "2023-09-30 20:15");
        $expensesSelection2 = $expensesDataService->fetchExpensesPeriod("2023-09-09 20:15", "2023-09-11 20:15");
        $expensesSelection3 = $expensesDataService->fetchExpensesPeriod("2000-01-01 00:01", "2000-01-20 23:59");

        $this->assertEquals(3,count($expensesSelection1));
        $this->assertEquals(2,count($expensesSelection2));
        $this->assertEquals(0,count($expensesSelection3));

        $this->assertEquals(99.95,$expensesSelection1[0]["price"]);
        $this->assertEquals("2023-09-10 20:00:00",$expensesSelection1[0]["created"]);
        $this->assertEquals(69.95,$expensesSelection1[1]["price"]);
        $this->assertEquals("2023-09-10 20:15:00",$expensesSelection1[1]["created"]);
        $this->assertEquals(149.95,$expensesSelection1[2]["price"]);
        $this->assertEquals("2023-09-13 20:15:00",$expensesSelection1[2]["created"]);
    }


    public function testWheterInsertExpensesIsWorks(): void
    {
        $backendServiceService = new BackendServiceService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());
        $backendServiceService->createTableStruct();
        $backendServiceService->createUser("testuser", "unit1234test", "theTestUser");

        $backendServiceService->createInitialContent("testuser");

        $userId = $this->dataBaseAdapter->query(
            "SELECT id FROM ".$this->config["tableNames"]["users"]." WHERE userName = 'testuser';",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray()[0]["id"];

        $categoryDataService = new CategoryDataService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());

        $categoryDataService->storeCategory($userId, "TestCategory100");
        $categoryDataService->storeCategory($userId, "TestCategory200");
        $categoryDataService->storeCategory($userId, "TestCategory300");

        $categories = $this->dataBaseAdapter->query(
            "SELECT id, title FROM ".$this->config["tableNames"]["categories"]." ORDER BY id;",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray();

        $categoryDataService->storeCategoryComposition($userId, [ $categories[0]["id"], $categories[2]["id"], $categories[3]["id"] ]);
        $categoryDataService->storeCategoryComposition($userId, [ $categories[1]["id"], $categories[4]["id"] ]);
      
        $categoryData = $categoryDataService->getCategories();

        $expensesDataService = new ExpensesDataService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal(), $categoryDataService);

        $result = $expensesDataService->insertExpenses($userId, $categoryData["categoryCompositionsData"][0]["categoryCompositionId"], 10.33, "test expenses 1");
        $this->assertEquals(true, $result);
        $result = $expensesDataService->insertExpenses($userId, $categoryData["categoryCompositionsData"][1]["categoryCompositionId"], 20.99, "test expenses 2");
        $this->assertEquals(true, $result);
        $result = $expensesDataService->insertExpenses($userId, $categoryData["categoryCompositionsData"][0]["categoryCompositionId"], 40, "test expenses 3");
        $this->assertEquals(true, $result);

        $expenses = $this->dataBaseAdapter->query(
            "SELECT userId, categoryCompositionId, price, created FROM ".$this->config["tableNames"]["expenses"].";",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray();
       
        $this->assertEquals(3, count($expenses));
        $this->assertEquals(10.33, $expenses[0]["price"]);
        $this->assertEquals(20.99, $expenses[1]["price"]);
        $this->assertEquals(40, $expenses[2]["price"]);
    }
}