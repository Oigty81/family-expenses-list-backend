<?php

declare(strict_types=1);

namespace AppTest\Service;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use App\Service\BackendServiceService;
use App\Service\BackendServiceServiceFactory;

class BackendServiceServiceFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp() : void
    {
        $dataBaseAdapter = require "Test/AppTest/_helper/DbAdapterLoader.php";

        $this->container = $this->prophesize(ContainerInterface::class);
        
        $logger = $this->prophesize(LoggerInterface::class);
       
        $this->container->get("config")->willReturn(array("config" => []));
        $this->container->get("mainDb")->willReturn($dataBaseAdapter);
        $this->container->get(LoggerInterface::class)->willReturn($logger);
    }

    public function testFactoryInvoke() : void
    {
        $factory = new BackendServiceServiceFactory();

        $backendServiceService = $factory($this->container->reveal());

        $this->assertInstanceOf(BackendServiceService::class, $backendServiceService);
    }
}