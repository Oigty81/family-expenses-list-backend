<?php

declare(strict_types=1);

namespace AppTest\Service;

use Exception;

use App\Service\UserDataService;
use App\Service\BackendServiceService;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

use Laminas\Db\Adapter\Adapter;
use Psr\Log\LoggerInterface;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UserDataServiceTest extends TestCase
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

    public function testAuthFlowWhenUserIsNotExist(): void
    {
        $backendServiceService = new BackendServiceService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());
        $backendServiceService->createTableStruct();
        $backendServiceService->createUser("testuser", "unit1234test", "theTestUser");

        $userDataService = new UserDataService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());

        $result = $userDataService->authFlow("unknownuser", "123", false);

        $this->assertEquals(-1, $result["error"]);
        $this->assertEquals("user: unknownuser not found!", $result["errorText"]);
    }

    public function testAuthFlowWhenUserPasswordIsWrong(): void
    {
        $backendServiceService = new BackendServiceService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());
        $backendServiceService->createTableStruct();
        $backendServiceService->createUser("testuser", "unit1234test", "theTestUser");

        $userDataService = new UserDataService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());

        $result = $userDataService->authFlow("testuser", "123", false);
        
        $this->assertEquals(-2, $result["error"]);
        $this->assertEquals("wrong password!", $result["errorText"]);
    }

    public function testAuthFlowWhenUserAuthenticationWasSuccessful(): void
    {
        $publicKey = file_get_contents($this->config["jwtSecretPublicKeyFile"]);
        $backendServiceService = new BackendServiceService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());
        $backendServiceService->createTableStruct();
        $backendServiceService->createUser("testuser", "unit1234test", "theTestUser");

        $userDataService = new UserDataService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());

        $result = $userDataService->authFlow("testuser", "unit1234test", false);
        
        $this->assertEquals(true, array_key_exists("token", $result));
        $jwtPayload = JWT::decode($result["token"], new Key($publicKey, 'RS256'));

        $iat = $jwtPayload->iat;
        $exp = $jwtPayload->exp;
        
        $this->assertEquals("testuser", $jwtPayload->data->username);
        $this->assertEquals("theTestUser", $jwtPayload->data->displayname);
        $this->assertLessThanOrEqual($this->config["shortExpireTimeAccessToken"], $exp - $iat);
    }

    public function testAuthFlowWhenUserAuthenticationWasSuccessfulWithLongExpireTime(): void
    {
        $publicKey = file_get_contents($this->config["jwtSecretPublicKeyFile"]);
        $backendServiceService = new BackendServiceService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());
        $backendServiceService->createTableStruct();
        $backendServiceService->createUser("testuser", "unit1234test", "theTestUser");

        $userDataService = new UserDataService($this->config, $this->dataBaseAdapter, $this->loggerInterface->reveal());

        $result = $userDataService->authFlow("testuser", "unit1234test", true);
        
        $this->assertEquals(true, array_key_exists("token", $result));
        $jwtPayload = JWT::decode($result["token"], new Key($publicKey, 'RS256'));

        $iat = $jwtPayload->iat;
        $exp = $jwtPayload->exp;
        
        $this->assertEquals("testuser", $jwtPayload->data->username);
        $this->assertEquals("theTestUser", $jwtPayload->data->displayname);
        $this->assertGreaterThan($this->config["shortExpireTimeAccessToken"], $exp - $iat);
        $this->assertLessThanOrEqual($this->config["longExpireTimeAccessToken"], $exp - $iat);
    }

    public function testAuthFlowWhenDataBaseAdapterQueryThrowAnException() 
    {
        $dbAdapterMock = $this->prophesize(Adapter::class);
        $dbAdapterMock->query(Argument::any(), Argument::any())->willThrow(new Exception("Unit Test Exception Test"));
        
        $userDataService = new UserDataService($this->config, $dbAdapterMock->reveal(), $this->loggerInterface->reveal());
        $result = $userDataService->authFlow("testuser", "unit1234test", false);

        $this->assertEquals(-99, $result["error"]);
        $this->assertEquals("error in user__authFlow.. Unit Test Exception Test", $result["errorText"]);
    }
}