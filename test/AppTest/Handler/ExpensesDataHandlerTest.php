<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\ExpensesDataHandler;
use App\Service\ExpensesDataService;
use App\Service\UtilitiesService;

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

    /** @var UtilitiesService|ObjectProphecy */
    protected $utilitiesService;

    /** @var ServerRequestInterface|ObjectProphecy */
    protected $serverRequest;

    protected function setUp() : void
    {
        $this->config = [];
        $this->loggerInterface  = $this->prophesize(LoggerInterface::class);
        $this->expensesDataService = $this->prophesize(ExpensesDataService::class);
        $this->utilitiesService = $this->prophesize(UtilitiesService::class);
        $this->serverRequest = $this->prophesize(ServerRequestInterface::class);
    }

    public function testThatHandlerMethodGetExpensesPeriodActionCallsItsServiceMethodsCorrectly(): void
    {
        $this->utilitiesService->checkWhetherParameterExistAndIsString(Argument::any(), Argument::any())->willReturn(null)->shouldBeCalledTimes(2);
        $this->utilitiesService->checkServiceErrorForResponse(Argument::any())->willReturn(null)->shouldBeCalledTimes(1);
        
        $this->expensesDataService->fetchExpensesPeriod(Argument::any(), Argument::any())->willReturn(["TestCall" => 1])->shouldBeCalledTimes(1);

        $expensesDataHandler = new ExpensesDataHandler(
            $this->config,
            $this->loggerInterface->reveal(),
            $this->expensesDataService->reveal(),
            $this->utilitiesService->reveal()
        );

        $this->serverRequest->getQueryParams()->willReturn(["from" => "1", "to" => "2"]);
        $this->serverRequest->getParsedBody()->willReturn([]);
        $this->serverRequest->getAttribute("userId")->willReturn(1);

        $result = $expensesDataHandler->getExpensesPeriodAction($this->serverRequest->reveal());

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals("{\"TestCall\":1}", $result->getBody()->getContents());
    }

    public function testThatHandlerMethodPutExpensesActionCallsItsServiceMethodsCorrectly(): void
    {
        $this->utilitiesService->checkWhetherParameterExistAndIsString(Argument::any(), Argument::any())->willReturn(null)->shouldBeCalledTimes(1);
        $this->utilitiesService->checkWhetherParameterExistAndIsNumeric(Argument::any(), Argument::any())->willReturn(null)->shouldBeCalledTimes(2);
        $this->utilitiesService->checkServiceErrorForResponse(Argument::any())->willReturn(null)->shouldBeCalledTimes(1);
        
        $this->expensesDataService->insertExpenses(Argument::any(), Argument::any(), Argument::any(), Argument::any())->willReturn(["TestCall" => 1])->shouldBeCalledTimes(1);

        $expensesDataHandler = new ExpensesDataHandler(
            $this->config,
            $this->loggerInterface->reveal(),
            $this->expensesDataService->reveal(),
            $this->utilitiesService->reveal()
        );

        $this->serverRequest->getQueryParams()->willReturn(["categoryCompositionId" => 1, "price" => 2, "metatext" => "meta..."]);
        $this->serverRequest->getParsedBody()->willReturn([]);
        $this->serverRequest->getAttribute("userId")->willReturn(1);

        $result = $expensesDataHandler->putExpensesAction($this->serverRequest->reveal());

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals("{\"TestCall\":1}", $result->getBody()->getContents());
    }
}