<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\ExpensesDataHandler;
use App\Service\ExpensesDataService;


use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

use Psr\Http\Message\ServerRequestInterface;

use Psr\Log\LoggerInterface;

class ExpensesDataHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @var array|ObjectProphecy */
    protected $config;

     /** @var LoggerInterface|ObjectProphecy */
     protected $loggerInterface;

     /** @var ExpensesDataService|ObjectProphecy */
     protected $expensesDataService;

    /** @var ServerRequestInterface|ObjectProphecy */
    protected $serverRequest;

    protected function setUp() : void
    {
        $this->config = [];
        $this->loggerInterface  = $this->prophesize(LoggerInterface::class);
        $this->expensesDataService = $this->prophesize(ExpensesDataService::class);
        $this->serverRequest = $this->prophesize(ServerRequestInterface::class);
    }

    public function testThatHandlerMethodGetExpensesActionCallsItsServiceMethodsCorrectly(): void
    {        
        $this->expensesDataService->getExpenses(Argument::any())->willReturn(["TestCall" => 1])->shouldBeCalledTimes(1);

        $expensesDataHandler = new ExpensesDataHandler(
            $this->config,
            $this->loggerInterface->reveal(),
            $this->expensesDataService->reveal(),
        );

        $this->serverRequest->getQueryParams()->willReturn([]);
        $this->serverRequest->getParsedBody()->willReturn([]);
        
        $result = $expensesDataHandler->getExpensesAction($this->serverRequest->reveal());

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals("{\"TestCall\":1}", $result->getBody()->getContents());
    }

    public function testThatHandlerMethodPutExpensesActionCallsItsServiceMethodsCorrectly(): void
    {        
        $this->expensesDataService->insertExpenses(Argument::any(), Argument::any(), Argument::any(), Argument::any())->willReturn(["TestCall" => 1])->shouldBeCalledTimes(1);

        $expensesDataHandler = new ExpensesDataHandler(
            $this->config,
            $this->loggerInterface->reveal(),
            $this->expensesDataService->reveal(),
        );

        $this->serverRequest->getQueryParams()->willReturn(["categoryCompositionId" => 1, "price" => 2, "metatext" => "meta..."]);
        $this->serverRequest->getParsedBody()->willReturn([]);
        $this->serverRequest->getAttribute("userId")->willReturn(1);

        $result = $expensesDataHandler->putExpensesAction($this->serverRequest->reveal());

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals("{\"TestCall\":1}", $result->getBody()->getContents());
    }

    public function testThatHandlerMethodUpdateExpensesActionCallsItsServiceMethodsCorrectly(): void
    {        
        $this->expensesDataService->updateExpenses(999, Argument::any(), Argument::any())->willReturn(["TestCall" => 1])->shouldBeCalledTimes(1);

        $expensesDataHandler = new ExpensesDataHandler(
            $this->config,
            $this->loggerInterface->reveal(),
            $this->expensesDataService->reveal(),
        );

        $this->serverRequest->getQueryParams()->willReturn(["id" => "1", "data" => "anydata"]);
        $this->serverRequest->getParsedBody()->willReturn([]);
        $this->serverRequest->getAttribute("userId")->willReturn(999);

        $result = $expensesDataHandler->updateExpensesAction($this->serverRequest->reveal());

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals("{\"TestCall\":1}", $result->getBody()->getContents());
    }

    public function testThatHandlerMethodDeleteExpensesActionCallsItsServiceMethodsCorrectly(): void
    {        
        $this->expensesDataService->deleteExpenses(Argument::any())->willReturn(["TestCall" => 1])->shouldBeCalledTimes(1);

        $expensesDataHandler = new ExpensesDataHandler(
            $this->config,
            $this->loggerInterface->reveal(),
            $this->expensesDataService->reveal(),
        );

        $this->serverRequest->getQueryParams()->willReturn(["id" => "1"]);
        $this->serverRequest->getParsedBody()->willReturn([]);
    
        $result = $expensesDataHandler->deleteExpensesAction($this->serverRequest->reveal());

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals("{\"TestCall\":1}", $result->getBody()->getContents());
    }
}