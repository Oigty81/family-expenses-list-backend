<?php

declare(strict_types=1);

namespace AppTest\Service;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use App\Service\CategoryDataService;
use App\Service\ExpensesDataService;
use App\Service\ExpensesDataServiceFactory;

class ExpensesDataServiceFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp() : void
    {
        $dataBaseAdapter = require "Test/AppTest/_helper/DbAdapterLoader.php";

        $this->container = $this->prophesize(ContainerInterface::class);
        
        $logger = $this->prophesize(LoggerInterface::class);
        $categoryDataService = $this->prophesize(CategoryDataService::class);
               
        $this->container->get("config")->willReturn(array("config" => []));
        $this->container->get("mainDb")->willReturn($dataBaseAdapter);
        $this->container->get(LoggerInterface::class)->willReturn($logger);
        $this->container->get(CategoryDataService::class)->willReturn($categoryDataService);
    }

    public function testFactoryInvoke() : void
    {
        $factory = new ExpensesDataServiceFactory();

        $expensesDataService = $factory($this->container->reveal());

        $this->assertInstanceOf(ExpensesDataService::class, $expensesDataService);
    }
}