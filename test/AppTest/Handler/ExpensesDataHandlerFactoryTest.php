<?php

declare(strict_types=1);

namespace AppTest\Handler;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use App\Service\ExpensesDataService;
use App\Handler\ExpensesDataHandler;
use App\Handler\ExpensesDataHandlerFactory;

class ExpensesDataHandlerFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        
        $logger = $this->prophesize(LoggerInterface::class);
        $expensesDataService = $this->prophesize(ExpensesDataService::class);
        
        $this->container->get("config")->willReturn(array("config" => []));
        $this->container->get(LoggerInterface::class)->willReturn($logger);
        $this->container->get(ExpensesDataService::class)->willReturn($expensesDataService);
    }

    public function testFactoryInvoke() : void
    {
        $factory = new ExpensesDataHandlerFactory();

        $expensesDataHandler = $factory($this->container->reveal());

        $this->assertInstanceOf(ExpensesDataHandler::class, $expensesDataHandler);
    }
}