<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\UserHandler;
use App\Service\UserDataService;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

use Psr\Http\Message\ServerRequestInterface;

use Psr\Log\LoggerInterface;
class UserHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @var array|ObjectProphecy */
    protected $config;

     /** @var LoggerInterface|ObjectProphecy */
     protected $loggerInterface;

     /** @var UserDataService|ObjectProphecy */
     protected $userDataService;

    /** @var ServerRequestInterface|ObjectProphecy */
    protected $serverRequest;

    protected function setUp() : void
    {
        $this->config = [];
        $this->loggerInterface  = $this->prophesize(LoggerInterface::class);
        $this->userDataService = $this->prophesize(UserDataService::class);
        $this->serverRequest = $this->prophesize(ServerRequestInterface::class);
    }

    public function testThatHandlerMethodGetSpendingsPeriodActionCallsItsServiceMethodsCorrectlyWithoutLongExpireTime(): void
    {        
        $this->userDataService->authFlow(Argument::any(), Argument::any(), false)->willReturn(["TestCall" => 1])->shouldBeCalledTimes(1);

        $userHandler = new UserHandler(
            $this->config,
            $this->loggerInterface->reveal(),
            $this->userDataService->reveal(),
        );

        $this->serverRequest->getQueryParams()->willReturn(["username" => "testu", "password" => "testpw"]);
        $this->serverRequest->getParsedBody()->willReturn([]);
        
        $result = $userHandler->authAction($this->serverRequest->reveal());

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals("{\"TestCall\":1}", $result->getBody()->getContents());
    }

    public function testThatHandlerMethodAuthActionCallsItsServiceMethodsCorrectlyWithLongExpireTime(): void
    {        
        $this->userDataService->authFlow(Argument::any(), Argument::any(), true)->willReturn(["TestCall" => 1])->shouldBeCalledTimes(1);

        $userHandler = new UserHandler(
            $this->config,
            $this->loggerInterface->reveal(),
            $this->userDataService->reveal(),
        );

        $this->serverRequest->getQueryParams()->willReturn(["username" => "testu", "password" => "testpw", "longExpireTime" => "dummy"]);
        $this->serverRequest->getParsedBody()->willReturn([]);
        
        $result = $userHandler->authAction($this->serverRequest->reveal());

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals("{\"TestCall\":1}", $result->getBody()->getContents());
    }

    public function testThatHandlerMethodAuthActionCallsItsServiceMethodsCorrectlyWhenServiceReturnsErrorFor400_1(): void
    {        
        $this->userDataService->authFlow(Argument::any(), Argument::any(), true)->willReturn(["TestCall" => 1, "error" => -1, "errormsg" => "error test for -1"])->shouldBeCalledTimes(1);

        $userHandler = new UserHandler(
            $this->config,
            $this->loggerInterface->reveal(),
            $this->userDataService->reveal(),
        );

        $this->serverRequest->getQueryParams()->willReturn(["username" => "testu", "password" => "testpw", "longExpireTime" => "dummy"]);
        $this->serverRequest->getParsedBody()->willReturn([]);
        
        $result = $userHandler->authAction($this->serverRequest->reveal());

        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals("\"error test for -1\"", $result->getBody()->getContents());
    }

    public function testThatHandlerMethodAuthActionCallsItsServiceMethodsCorrectlyWhenServiceReturnsErrorFor400_2(): void
    {        
        $this->userDataService->authFlow(Argument::any(), Argument::any(), true)->willReturn(["TestCall" => 1, "error" => -2, "errormsg" => "error test for -2"])->shouldBeCalledTimes(1);

        $userHandler = new UserHandler(
            $this->config,
            $this->loggerInterface->reveal(),
            $this->userDataService->reveal(),
        );

        $this->serverRequest->getQueryParams()->willReturn(["username" => "testu", "password" => "testpw", "longExpireTime" => "dummy"]);
        $this->serverRequest->getParsedBody()->willReturn([]);
        
        $result = $userHandler->authAction($this->serverRequest->reveal());

        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals("\"error test for -2\"", $result->getBody()->getContents());
    }

    public function testThatHandlerMethodAuthActionCallsItsServiceMethodsCorrectlyWhenServiceReturnsErrorFor500(): void
    {        
        $this->userDataService->authFlow(Argument::any(), Argument::any(), true)->willReturn(["TestCall" => 1, "error" => -99, "errormsg" => "error test for any"])->shouldBeCalledTimes(1);

        $userHandler = new UserHandler(
            $this->config,
            $this->loggerInterface->reveal(),
            $this->userDataService->reveal(),
        );

        $this->serverRequest->getQueryParams()->willReturn(["username" => "testu", "password" => "testpw", "longExpireTime" => "dummy"]);
        $this->serverRequest->getParsedBody()->willReturn([]);
        
        $result = $userHandler->authAction($this->serverRequest->reveal());

        $this->assertEquals(500, $result->getStatusCode());
        $this->assertEquals("\"error test for any\"", $result->getBody()->getContents());
    }

}
