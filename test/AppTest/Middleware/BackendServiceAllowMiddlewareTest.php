<?php

/**
 * @suppresswarnings PHP0418
 */

declare(strict_types=1);

namespace AppTest\Middleware;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

use Psr\Log\LoggerInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;

use App\Middleware\BackendServiceAllowMiddleware;

class BackendServiceAllowMiddlewareTest extends TestCase
{
    use ProphecyTrait;

    /** @var LoggerInterface|ObjectProphecy */
    protected $loggerInterface;

    /** @var ServerRequestInterface|ObjectProphecy */
    protected $serverRequest;

    /** @var RequestHandlerInterface|ObjectProphecy */
    protected $requestHandler;

    /** @var ResponseInterface|ObjectProphecy */
    protected $response;

    protected function setUp() : void
    {
        $this->loggerInterface  = $this->prophesize(LoggerInterface::class);
        $this->serverRequest = $this->prophesize(ServerRequestInterface::class);
        $this->requestHandler = $this->prophesize(RequestHandlerInterface::class);
        
        $this->response = $this->prophesize(ResponseInterface::class);
        
        $this->requestHandler->handle($this->serverRequest)->willReturn(new JsonResponse(['msg' => 'next'], 200));
    }

    public function testIsBackendServiceAllow() : void
    {
        $config = ["backendServiceEnabled" => true ];
        $backendServiceAllowMiddleware = new BackendServiceAllowMiddleware($config, $this->loggerInterface->reveal());
        /** @var JsonResponse $result */
        $result = $backendServiceAllowMiddleware->process($this->serverRequest->reveal(), $this->requestHandler->reveal());
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertArrayHasKey("msg", $result->getPayload());
    }

    public function testIsBackendServiceNotAllow() : void
    {
        $config = ["backendServiceEnabled" => false ];
        $backendServiceAllowMiddleware = new BackendServiceAllowMiddleware($config, $this->loggerInterface->reveal());
        /** @var JsonResponse $result */
        $result = $backendServiceAllowMiddleware->process($this->serverRequest->reveal(), $this->requestHandler->reveal());
        $this->assertEquals(403, $result->getStatusCode());
        $this->assertArrayHasKey("err", $result->getPayload());
    }
}