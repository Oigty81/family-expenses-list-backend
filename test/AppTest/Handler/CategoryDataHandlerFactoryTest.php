<?php

declare(strict_types=1);

namespace AppTest\Handler;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use App\Service\CategoryDataService;
use App\Handler\CategoryDataHandler;
use App\Handler\CategoryDataHandlerFactory;

class CategoryDataHandlerFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        
        $logger = $this->prophesize(LoggerInterface::class);
        $categoryDataService = $this->prophesize(CategoryDataService::class);

        $this->container->get("config")->willReturn(array("config" => []));
        $this->container->get(LoggerInterface::class)->willReturn($logger);
        $this->container->get(CategoryDataService::class)->willReturn($categoryDataService);
    }

    public function testFactoryInvoke() : void
    {
        $factory = new CategoryDataHandlerFactory();

        $categoryDataHandler = $factory($this->container->reveal());

        $this->assertInstanceOf(CategoryDataHandler::class, $categoryDataHandler);
    }
}