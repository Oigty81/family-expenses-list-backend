<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\BackendServiceHandler;
use App\Service\BackendServiceService;
use App\Service\UtilitiesService;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

use Psr\Http\Message\ServerRequestInterface;

use Psr\Log\LoggerInterface;

class BackendServiceHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @var array|ObjectProphecy */
    protected $config;

     /** @var LoggerInterface|ObjectProphecy */
     protected $loggerInterface;

     /** @var BackendServiceService|ObjectProphecy */
     protected $backendServiceService;

    /** @var UtilitiesService|ObjectProphecy */
    protected $utilitiesService;

    /** @var ServerRequestInterface|ObjectProphecy */
    protected $serverRequest;

    protected function setUp() : void
    {
        $this->config = [];
        $this->loggerInterface  = $this->prophesize(LoggerInterface::class);
        $this->backendServiceService = $this->prophesize(BackendServiceService::class);
        $this->utilitiesService = $this->prophesize(UtilitiesService::class);
        $this->serverRequest = $this->prophesize(ServerRequestInterface::class);
    }
    
    public function testThatHandlerMethodCreateTableStructActionCallsItsServiceMethodsCorrectly(): void
    {
        $this->backendServiceService->createTableStruct()->willReturn(["TestCall" => 1])->shouldBeCalledTimes(1);

        $backendServiceHandler = new BackendServiceHandler(
            $this->config,
            $this->loggerInterface->reveal(),
            $this->backendServiceService->reveal(),
            $this->utilitiesService->reveal()
        );

        $this->serverRequest->getQueryParams()->willReturn([]);
        $this->serverRequest->getParsedBody()->willReturn([]);
        
        $result = $backendServiceHandler->createTableStructAction($this->serverRequest->reveal());

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals("{\"TestCall\":1}", $result->getBody()->getContents());
    }

    public function testThatHandlerMethodCreateInitialContentActionCallsItsServiceMethodsCorrectly(): void
    {
        $this->utilitiesService->checkWhetherParameterExistAndIsString(Argument::any(), Argument::any())->willReturn(null)->shouldBeCalledTimes(1);
        
        $this->backendServiceService->createInitialContent(Argument::any())->willReturn(["TestCall" => 1])->shouldBeCalledTimes(1);

        $backendServiceHandler = new BackendServiceHandler(
            $this->config,
            $this->loggerInterface->reveal(),
            $this->backendServiceService->reveal(),
            $this->utilitiesService->reveal()
        );

        $this->serverRequest->getQueryParams()->willReturn(["username" => "testuser"]);
        $this->serverRequest->getParsedBody()->willReturn([]);
       
        $result = $backendServiceHandler->createInitialContentAction($this->serverRequest->reveal());

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals("{\"TestCall\":1}", $result->getBody()->getContents());
    }

    public function testThatHandlerMethodDeleteAllEntriesActionCallsItsServiceMethodsCorrectly(): void
    {
        $this->backendServiceService->deleteAllEntries()->willReturn(["TestCall" => 1])->shouldBeCalledTimes(1);

        $backendServiceHandler = new BackendServiceHandler(
            $this->config,
            $this->loggerInterface->reveal(),
            $this->backendServiceService->reveal(),
            $this->utilitiesService->reveal()
        );

        $this->serverRequest->getQueryParams()->willReturn([]);
        $this->serverRequest->getParsedBody()->willReturn([]);
       
        $result = $backendServiceHandler->deleteAllEntriesAction($this->serverRequest->reveal());

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals("{\"TestCall\":1}", $result->getBody()->getContents());
    }

    public function testThatHandlerMethodDeleteAllTablesActionCallsItsServiceMethodsCorrectly(): void
    {
        $this->backendServiceService->deleteAllTables()->willReturn(["TestCall" => 1])->shouldBeCalledTimes(1);

        $backendServiceHandler = new BackendServiceHandler(
            $this->config,
            $this->loggerInterface->reveal(),
            $this->backendServiceService->reveal(),
            $this->utilitiesService->reveal()
        );

        $this->serverRequest->getQueryParams()->willReturn([]);
        $this->serverRequest->getParsedBody()->willReturn([]);
       
        $result = $backendServiceHandler->deleteAllTablesAction($this->serverRequest->reveal());

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals("{\"TestCall\":1}", $result->getBody()->getContents());
    }

    public function testThatHandlerMethodCreateUserActionCallsItsServiceMethodsCorrectly(): void
    {
        $this->utilitiesService->checkWhetherParameterExistAndIsString(Argument::any(), Argument::any())->willReturn(null)->shouldBeCalledTimes(3);
        
        $this->backendServiceService->createUser(Argument::any(), Argument::any(), Argument::any())->willReturn(["TestCall" => 1])->shouldBeCalledTimes(1);

        $backendServiceHandler = new BackendServiceHandler(
            $this->config,
            $this->loggerInterface->reveal(),
            $this->backendServiceService->reveal(),
            $this->utilitiesService->reveal()
        );

        $this->serverRequest->getQueryParams()->willReturn(["username" => "testuser", "password" => "pw123", "displayname" => "testUser"]);
        $this->serverRequest->getParsedBody()->willReturn([]);
       
        $result = $backendServiceHandler->createUserAction($this->serverRequest->reveal());

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals("{\"TestCall\":1}", $result->getBody()->getContents());
    }

    public function testThatHandlerMethodDeleteUserActionCallsItsServiceMethodsCorrectly(): void
    {
        $this->utilitiesService->checkWhetherParameterExistAndIsString(Argument::any(), Argument::any())->willReturn(null)->shouldBeCalledTimes(1);
        
        $this->backendServiceService->deleteUser(Argument::any())->willReturn(["TestCall" => 1])->shouldBeCalledTimes(1);

        $backendServiceHandler = new BackendServiceHandler(
            $this->config,
            $this->loggerInterface->reveal(),
            $this->backendServiceService->reveal(),
            $this->utilitiesService->reveal()
        );

        $this->serverRequest->getQueryParams()->willReturn(["username" => "testuser"]);
        $this->serverRequest->getParsedBody()->willReturn([]);
       
        $result = $backendServiceHandler->deleteUserAction($this->serverRequest->reveal());

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals("{\"TestCall\":1}", $result->getBody()->getContents());
    }
}