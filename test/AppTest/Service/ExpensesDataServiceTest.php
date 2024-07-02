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
        $dbCleaner($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());
    }

    public function testWheterGetExpensesIsWorks(): void
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

        $categoryDataService->storeCategoryComposition($userId, [ $categories[0]["id"], $categories[2]["id"], $categories[3]["id"] ]);
        $categoryDataService->storeCategoryComposition($userId, [ $categories[1]["id"], $categories[4]["id"] ]);
      
        $categoryData = $categoryDataService->getCategories();

        $expensesDataService = new ExpensesDataService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal(), $categoryDataService);

        $expensesDataService->insertExpenses($userId, $categoryData["categoryCompositions"][0]["categoryCompositionId"], 10.33, "2023-08-10 20:00", "test spending 1");
        $expensesDataService->insertExpenses($userId, $categoryData["categoryCompositions"][1]["categoryCompositionId"], 20.99, "2023-08-10 20:12", "test spending 2");
        $expensesDataService->insertExpenses($userId, $categoryData["categoryCompositions"][0]["categoryCompositionId"], 40, "2023-08-10 20:22", "test spending 3");
       
        $this->dataBaseAdapter->query(
            "INSERT INTO expenses (userId, categoryCompositionId, price, created, metatext) VALUES(?, ?, ?, ?, ?);",
            [$userId, $categoryData["categoryCompositions"][0]["categoryCompositionId"], 99.95, "2023-09-10 20:00", "test spending 1" ]
        );

        $this->dataBaseAdapter->query(
            "INSERT INTO expenses (userId, categoryCompositionId, price, created, metatext) VALUES(?, ?, ?, ?, ?);",
            [$userId, $categoryData["categoryCompositions"][0]["categoryCompositionId"], 69.95, "2023-09-10 20:15", "test spending 2" ]
        );

        $this->dataBaseAdapter->query(
            "INSERT INTO expenses (userId, categoryCompositionId, price, created, metatext) VALUES(?, ?, ?, ?, ?);",
            [$userId, $categoryData["categoryCompositions"][0]["categoryCompositionId"], 149.95, "2023-09-13 20:15", "test spending 3 special text" ]
        );

        $expensesSelection1 = $expensesDataService->GetExpenses([]);
        
        $this->assertEquals(6,count($expensesSelection1["expenses"]));
        $this->assertEquals(99.95,$expensesSelection1["expenses"][3]["price"]);
        $this->assertEquals("2023-09-10 20:00:00", $expensesSelection1["expenses"][3]["created"]);
        $this->assertEquals(69.95,$expensesSelection1["expenses"][4]["price"]);
        $this->assertEquals("2023-09-10 20:15:00", $expensesSelection1["expenses"][4]["created"]);
        $this->assertEquals(149.95,$expensesSelection1["expenses"][5]["price"]);
        $this->assertEquals("2023-09-13 20:15:00", $expensesSelection1["expenses"][5]["created"]);
        $this->assertEquals(391.17, $expensesSelection1["total"]);

        $expensesSelection2 = $expensesDataService->GetExpenses([
            "daterangeFilter" => [
                "from" =>  "2023-09-10 19:00",
                "to" => "2023-09-12 -12:00"
            ]
        ]);
    
        $this->assertEquals(2,count($expensesSelection2["expenses"]));

        $expensesSelection3 = $expensesDataService->GetExpenses([
            "metatextFilter" => "cial"
        ]);

        $this->assertEquals(1,count($expensesSelection3["expenses"]));
        $this->assertEquals("test spending 3 special text", $expensesSelection3["expenses"][0]["metatext"]);
    }

    public function testWheterInsertExpensesIsWorks(): void
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

        $categoryDataService->storeCategoryComposition($userId, [ $categories[0]["id"], $categories[2]["id"], $categories[3]["id"] ]);
        $categoryDataService->storeCategoryComposition($userId, [ $categories[1]["id"], $categories[4]["id"] ]);
      
        $categoryData = $categoryDataService->getCategories();

        $expensesDataService = new ExpensesDataService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal(), $categoryDataService);

        $result = $expensesDataService->insertExpenses($userId, $categoryData["categoryCompositions"][0]["categoryCompositionId"], 10.33, "2023-08-10 20:00", "test expenses 1");
        $this->assertEquals(true, $result);
        $result = $expensesDataService->insertExpenses($userId, $categoryData["categoryCompositions"][1]["categoryCompositionId"], 20.99, "2023-08-10 20:12", "test expenses 2");
        $this->assertEquals(true, $result);
        $result = $expensesDataService->insertExpenses($userId, $categoryData["categoryCompositions"][0]["categoryCompositionId"], 40, "2023-08-10 20:22", "test expenses 3");
        $this->assertEquals(true, $result);

        $expenses = $this->dataBaseAdapter->query(
            "SELECT userId, categoryCompositionId, price, created FROM expenses;",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray();
       
        $this->assertEquals(3, count($expenses));
        $this->assertEquals(10.33, $expenses[0]["price"]);
        $this->assertEquals(20.99, $expenses[1]["price"]);
        $this->assertEquals(40, $expenses[2]["price"]);
    }

    public function testWheterUpdateExpensesIsWorks(): void
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

        $categoryDataService->storeCategoryComposition($userId, [ $categories[0]["id"], $categories[2]["id"], $categories[3]["id"] ]);
        $categoryDataService->storeCategoryComposition($userId, [ $categories[1]["id"], $categories[4]["id"] ]);
      
        $categoryData = $categoryDataService->getCategories();

        $expensesDataService = new ExpensesDataService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal(), $categoryDataService);

        $this->dataBaseAdapter->query(
            "INSERT INTO expenses (userId, categoryCompositionId, price, created, metatext) VALUES(?, ?, ?, now(), ?)",
            [$userId, $categoryData["categoryCompositions"][0]["categoryCompositionId"], 10.33, "test expenses 1"]
        );

        $this->dataBaseAdapter->query(
            "INSERT INTO expenses (userId, categoryCompositionId, price, created, metatext) VALUES(?, ?, ?, now(), ?)",
            [$userId, $categoryData["categoryCompositions"][1]["categoryCompositionId"], 20.99, "test expenses 2"]
        );

        $expensesBefore = $this->dataBaseAdapter->query(
            "SELECT id, userId, categoryCompositionId, price, created FROM expenses;",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray();

        $expensesDataService->updateExpenses($userId, $expensesBefore[0]["id"], ["price" => "500.04" ]);
        $expensesDataService->updateExpenses($userId, $expensesBefore[1]["id"], ["price" => "700.08" ]);

        $expensesAfter1 = $this->dataBaseAdapter->query(
            "SELECT id, userId, categoryCompositionId, price, created, metatext FROM expenses;",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray();

        $this->assertEquals(500.04, $expensesAfter1[0]["price"]);
        $this->assertEquals("test expenses 1", $expensesAfter1[0]["metatext"]);
        $this->assertEquals(1, $expensesAfter1[0]["categoryCompositionId"]);
        $this->assertEquals(700.08, $expensesAfter1[1]["price"]);

        $expensesDataService->updateExpenses($userId, $expensesBefore[0]["id"],
            [
                "price" => "1111.04",
                "categoryCompositionId" => "2",
                "metatext" => "new meta test",
                "created" => "2022-01-02 20:20:00"
            ]);

        $expensesAfter2 = $this->dataBaseAdapter->query(
            "SELECT id, userId, categoryCompositionId, price, created, metatext FROM expenses;",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray();

        $this->assertEquals(1111.04, $expensesAfter2[0]["price"]);
        $this->assertEquals("new meta test", $expensesAfter2[0]["metatext"]);
        $this->assertEquals(2, $expensesAfter2[0]["categoryCompositionId"]);
        $this->assertEquals("2022-01-02 20:20:00", $expensesAfter2[0]["created"]);
    }
    
    public function testWheterDeleteExpensesIsWorks(): void
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

        $categoryDataService->storeCategoryComposition($userId, [ $categories[0]["id"], $categories[2]["id"], $categories[3]["id"] ]);
        $categoryDataService->storeCategoryComposition($userId, [ $categories[1]["id"], $categories[4]["id"] ]);
      
        $categoryData = $categoryDataService->getCategories();

        $expensesDataService = new ExpensesDataService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal(), $categoryDataService);

        $this->dataBaseAdapter->query(
            "INSERT INTO expenses (userId, categoryCompositionId, price, created, metatext) VALUES(?, ?, ?, now(), ?)",
            [$userId, $categoryData["categoryCompositions"][0]["categoryCompositionId"], 10.33, "test expenses 1"]
        );

        $this->dataBaseAdapter->query(
            "INSERT INTO expenses (userId, categoryCompositionId, price, created, metatext) VALUES(?, ?, ?, now(), ?)",
            [$userId, $categoryData["categoryCompositions"][1]["categoryCompositionId"], 20.99, "test expenses 2"]
        );

        $this->dataBaseAdapter->query(
            "INSERT INTO expenses (userId, categoryCompositionId, price, created, metatext) VALUES(?, ?, ?, now(), ?)",
            [$userId, $categoryData["categoryCompositions"][0]["categoryCompositionId"], 40, "test expenses 3"]
        );


        $expensesBefore = $this->dataBaseAdapter->query(
            "SELECT id, userId, categoryCompositionId, price, created FROM expenses;",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray();

        $this->assertEquals(3, count($expensesBefore));

        $expensesDataService->deleteExpenses($expensesBefore[0]["id"]);

        $expensesAfter = $this->dataBaseAdapter->query(
            "SELECT id, userId, categoryCompositionId, price, created FROM expenses;",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray();

        $this->assertEquals(2, count($expensesAfter));
    }
}