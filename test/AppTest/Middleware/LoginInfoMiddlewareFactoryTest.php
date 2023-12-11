<?php

declare(strict_types=1);

namespace AppTest\Middleware;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use App\Middleware\LoginInfoMiddlewareFactory;
use App\Middleware\LoginInfoMiddleware;

class LoginInfoMiddlewareFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        
        $logger = $this->prophesize(LoggerInterface::class);
       
        $this->container->get("config")->willReturn(array("config" => []));
        $this->container->get(LoggerInterface::class)->willReturn($logger);
    }

    public function testFactoryInvoke() : void
    {
        $factory = new LoginInfoMiddlewareFactory();
        $loginInfoMiddleware = $factory($this->container->reveal());
        $this->assertInstanceOf(LoginInfoMiddleware::class, $loginInfoMiddleware);
    }
}