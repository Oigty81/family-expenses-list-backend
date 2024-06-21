<?php

declare(strict_types=1);

namespace AppTest\Handler;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use App\Service\UserDataService;
use App\Handler\UserHandler;
use App\Handler\UserHandlerFactory;

class UserHandlerFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        
        $logger = $this->prophesize(LoggerInterface::class);
        $userDataService = $this->prophesize(userDataService::class);
        
        $this->container->get("config")->willReturn(array("config" => []));
        $this->container->get(LoggerInterface::class)->willReturn($logger);
        $this->container->get(UserDataService::class)->willReturn($userDataService);
    }

    public function testFactoryInvoke() : void
    {
        $factory = new UserHandlerFactory();

        $userHandler = $factory($this->container->reveal());

        $this->assertInstanceOf(UserHandler::class, $userHandler);
    }
}