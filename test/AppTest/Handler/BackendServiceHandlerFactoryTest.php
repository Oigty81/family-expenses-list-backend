<?php

declare(strict_types=1);

namespace AppTest\Handler;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use App\Service\BackendServiceService;
use App\Handler\BackendServiceHandler;
use App\Handler\BackendServiceHandlerFactory;

class BackendServiceHandlerFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        
        $logger = $this->prophesize(LoggerInterface::class);
        $backendServiceService = $this->prophesize(BackendServiceService::class);

        $this->container->get("config")->willReturn(array("config" => []));
        $this->container->get(LoggerInterface::class)->willReturn($logger);
        $this->container->get(BackendServiceService::class)->willReturn($backendServiceService);
    }

    public function testFactoryInvoke() : void
    {
        $factory = new BackendServiceHandlerFactory();

        $backendServiceHandler = $factory($this->container->reveal());

        $this->assertInstanceOf(BackendServiceHandler::class, $backendServiceHandler);
    }
}