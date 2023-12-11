<?php

declare(strict_types=1);

namespace AppTest\Service;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Monolog\Logger;

use App\Service\LoggerFactory;

class LoggerFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ContainerInterface|ObjectProphecy */
    protected $container;

    protected function setUp() : void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryInvoke() : void
    {
        $factory = new LoggerFactory();

        $loggerService = $factory($this->container->reveal());

        $this->assertInstanceOf(Logger::class, $loggerService);
    }
}