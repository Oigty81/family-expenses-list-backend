<?php

declare(strict_types=1);

namespace AppTest\Service;

use App\Service\BackendServiceService;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

use Laminas\Db\Adapter\Adapter;
use Psr\Log\LoggerInterface;

class BackendServiceServiceTest extends TestCase
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

    public function testWhetherTablesExtractFromConfig(): void
    {
        $backendServiceService = new BackendServiceService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());
        $result = $backendServiceService->getSqlTables();

        $this->assertContains("users", $result);
    }

    public function testWhetherTablesAreCreated(): void
    {
        $backendServiceService = new BackendServiceService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());
        $resultQuery = $backendServiceService->createTableStruct();
        
        $resultContent = $this->dataBaseAdapter->query(
            "SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA = '".$this->dataBaseAdapter->getCurrentSchema()."'",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray();

        $this->assertEquals(true, $resultQuery);

        foreach($resultContent as $content) {
            $this->assertEquals(true, in_array($content["TABLE_NAME"], $backendServiceService->getSqlTables()));
        }
    }

    public function testWhetherUserWasInserted(): void
    {
        $backendServiceService = new BackendServiceService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());
        $backendServiceService->createTableStruct();
        $result = $backendServiceService->createUser("testuser", "unit1234test", "theTestUser");
        
        $resultContent = $this->dataBaseAdapter->query("SELECT username, displayname FROM users;", Adapter::QUERY_MODE_EXECUTE
        )->toArray();

        $this->assertEquals(true, $result);
        $this->assertEquals("testuser", $resultContent[0]["username"]);
        $this->assertEquals("theTestUser", $resultContent[0]["displayname"]);
    }

    public function testWhetherUserWasDeleted(): void
    {
        $backendServiceService = new BackendServiceService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());
        $backendServiceService->createTableStruct();
        $backendServiceService->createUser("testuser", "unit1234test", "theTestUser");
        $result = $backendServiceService->deleteUser("testuser");
        
        $resultContent = $this->dataBaseAdapter->query("SELECT username, displayname FROM users;", Adapter::QUERY_MODE_EXECUTE
        )->toArray();

        $this->assertEquals(true, $result);
        $this->assertEquals(0, count($resultContent));
    }

    public function testWhetherInitialContentWasInserted(): void
    {
        $backendServiceService = new BackendServiceService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());
        $backendServiceService->createTableStruct();
        $backendServiceService->createUser("testuser", "unit1234test", "theTestUser");
        $result = $backendServiceService->createInitialContent("testuser");

        $this->assertEquals(true, $result);

        $resultContent = $this->dataBaseAdapter->query("SELECT title, userId FROM categories;", Adapter::QUERY_MODE_EXECUTE
        )->toArray();

        $existedCategories = [];

        foreach($resultContent as $content) {
            array_push($existedCategories, $content["title"]);
        }

        $this->assertEquals(true, $result);

        foreach ($this->config["preCategories"] as $key => $value) {
            $search = false;
            foreach($existedCategories as $c) {
                if(strcmp($c, $value) == 0) {
                    $search = true;
                    break;
                }
            }
            
            $this->assertEquals(true, $search);
        }
    }

    public function testWhetherNoInitialContentWasInsertedWhenGiveUnknownUser(): void
    {
        $backendServiceService = new BackendServiceService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());
        $backendServiceService->createTableStruct();
        $backendServiceService->createUser("testuser", "unit1234test", "theTestUser");
        $result = $backendServiceService->createInitialContent("testuserUnknown");

        $resultContent = $this->dataBaseAdapter->query("SELECT title, userId FROM categories;", Adapter::QUERY_MODE_EXECUTE
        )->toArray();

        $this->assertEquals("user: testuserUnknown not found !!!", $result);
        $this->assertEquals(0, count($resultContent));
    }

    public function testWhetherDeletedAllEntries(): void
    {
        $backendServiceService = new BackendServiceService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());
        $backendServiceService->createTableStruct();
        $backendServiceService->createUser("testuser", "unit1234test", "theTestUser");
        $backendServiceService->createInitialContent("testuser");

        $userId = $this->dataBaseAdapter->query(
            "SELECT id FROM users WHERE username = 'testuser';",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray()[0]["id"];

        $lastCategoryId = $this->dataBaseAdapter->query(
            "SELECT MAX(id) FROM categories;",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray()[0]["MAX(id)"];

        $this->dataBaseAdapter->query(
            "INSERT INTO category_compositions (created, userId) VALUES (now(),".$userId.");",
            Adapter::QUERY_MODE_EXECUTE
        );

        $lastCategoryCompositionId = $this->dataBaseAdapter->query(
            "SELECT LAST_INSERT_ID();",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray()[0]["LAST_INSERT_ID()"];
         
        $this->dataBaseAdapter->query(
            "INSERT INTO category_composition_members (categoryId, categoryCompositionId) VALUES (".$lastCategoryId.", ".$lastCategoryCompositionId.");",
            Adapter::QUERY_MODE_EXECUTE
        );

        $this->dataBaseAdapter->query(
            "INSERT INTO expenses (userId, categoryCompositionId, price, created, metatext) VALUES (".$userId.", ".$lastCategoryCompositionId.", 10, now(), 'text');",
            Adapter::QUERY_MODE_EXECUTE
        );

        $expensesCount = $this->dataBaseAdapter->query("SELECT COUNT(*) AS c FROM expenses;", Adapter::QUERY_MODE_EXECUTE)->toArray()[0]["c"];
        $categoryCompositionMembersCount = $this->dataBaseAdapter->query("SELECT COUNT(*) AS c FROM category_composition_members;", Adapter::QUERY_MODE_EXECUTE)->toArray()[0]["c"];
        $categoryCompositionsCount = $this->dataBaseAdapter->query("SELECT COUNT(*) AS c FROM category_compositions;", Adapter::QUERY_MODE_EXECUTE)->toArray()[0]["c"];
        $categoriesCount = $this->dataBaseAdapter->query("SELECT COUNT(*) AS c FROM categories;", Adapter::QUERY_MODE_EXECUTE)->toArray()[0]["c"];
        
        $this->assertGreaterThan(0, $expensesCount);
        $this->assertGreaterThan(0, $categoryCompositionMembersCount);
        $this->assertGreaterThan(0, $categoryCompositionsCount);
        $this->assertGreaterThan(0, $categoriesCount);
        
        $backendServiceService->deleteAllEntries();

        $expensesCount = $this->dataBaseAdapter->query("SELECT COUNT(*) AS c FROM expenses;", Adapter::QUERY_MODE_EXECUTE)->toArray()[0]["c"];
        $categoryCompositionMembersCount = $this->dataBaseAdapter->query("SELECT COUNT(*) AS c FROM category_composition_members;", Adapter::QUERY_MODE_EXECUTE)->toArray()[0]["c"];
        $categoryCompositionsCount = $this->dataBaseAdapter->query("SELECT COUNT(*) AS c FROM category_compositions;", Adapter::QUERY_MODE_EXECUTE)->toArray()[0]["c"];
        $categoriesCount = $this->dataBaseAdapter->query("SELECT COUNT(*) AS c FROM categories;", Adapter::QUERY_MODE_EXECUTE)->toArray()[0]["c"];

        $this->assertEquals(0, $expensesCount);
        $this->assertEquals(0, $categoryCompositionMembersCount);
        $this->assertEquals(0, $categoryCompositionsCount);
        $this->assertEquals(0, $categoriesCount);
    }

    public function testWhetherDeletedAllTables(): void
    {
        $backendServiceService = new BackendServiceService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());
        $backendServiceService->createTableStruct();
        $backendServiceService->createUser("testuser", "unit1234test", "theTestUser");
        $backendServiceService->createInitialContent("testuser");

        $resultContent = $this->dataBaseAdapter->query(
            "SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA = '".$this->dataBaseAdapter->getCurrentSchema()."'",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray();

        $this->assertEquals(5, count($resultContent));

        $backendServiceService->deleteAllTables();

        $resultContent = $this->dataBaseAdapter->query(
            "SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA = '".$this->dataBaseAdapter->getCurrentSchema()."'",
            Adapter::QUERY_MODE_EXECUTE
        )->toArray();

        $this->assertEquals(0, count($resultContent));
    }
}