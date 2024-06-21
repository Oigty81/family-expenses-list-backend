<?php

declare(strict_types=1);

namespace AppTest\Handler;


use App\Handler\HandlerActionMapping;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;


use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;


class HandlerActionMappingTest extends TestCase
{
    use ProphecyTrait;

    /** @var ServerRequestInterface|ObjectProphecy */
    protected $serverRequest;

    /** @var object */
    protected $traitTestInstance;

    /** @var object */
    protected $traitTestInstanceWithMethodAnnotations;

    protected function setUp() : void
    {
        $this->serverRequest = $this->prophesize(ServerRequestInterface::class);

        $this->traitTestInstance = new class {
            use HandlerActionMapping;

            public function destinationMethodAction(ServerRequestInterface $request): ResponseInterface
            {
                return new JsonResponse(["Call" => "destinationMethod for unit test"],200);
            }
        };

        $this->traitTestInstanceWithMethodAnnotations = new class {
            use HandlerActionMapping;

            /**
             * @RequestParams(from | to | data)
            */
            public function destinationMethodWithAnnotationsAction(ServerRequestInterface $request): ResponseInterface
            {
                return new JsonResponse(["Call" => "destinationMethod with annotations for unit test"],200);
            }
        };
    }

    public function testWhetherHandlerCallAnExistedAction(): void
    {
        $this->serverRequest->getParsedBody()->willReturn([]);
        $this->serverRequest->getQueryParams()->willReturn([]);

        $this->serverRequest->getAttribute('action')->willReturn("destinationMethod");
        
        $result = $this->traitTestInstance->handle($this->serverRequest->reveal());
        
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals("{\"Call\":\"destinationMethod for unit test\"}", $result->getBody()->getContents()); 
    }

    public function testWhetherHandlerReturns404WhenNoActionIsDefined(): void
    {
        $this->serverRequest->getAttribute('action')->willReturn(null);
               
        $result = $this->traitTestInstance->handle($this->serverRequest->reveal());
        
        $this->assertEquals(404, $result->getStatusCode());
    }

    public function testWhetherHandlerReturns404WhenActionIsUnknown(): void
    {
        $this->serverRequest->getParsedBody()->willReturn([]);
        $this->serverRequest->getQueryParams()->willReturn([]);
        
        $this->serverRequest->getAttribute('action')->willReturn("unknownMethod");
               
        $result = $this->traitTestInstance->handle($this->serverRequest->reveal());
        
        $this->assertEquals(404, $result->getStatusCode());
    }

    public function testWhetherMethodCheckParameterIsWorks(): void
    {
        $this->serverRequest->getParsedBody()->willReturn(["paramBody1" => "123", "paramBody2" => 321]);
        $this->serverRequest->getQueryParams()->willReturn(["paramQuery1" => "789", "paramQuery2" => 987]);

        $this->assertEquals(true, $this->traitTestInstance->checkParameter($this->serverRequest->reveal(), "paramBody1"));
        $this->assertEquals(true, $this->traitTestInstance->checkParameter($this->serverRequest->reveal(), "paramBody2"));
        $this->assertEquals(true, $this->traitTestInstance->checkParameter($this->serverRequest->reveal(), "paramQuery1"));
        $this->assertEquals(true, $this->traitTestInstance->checkParameter($this->serverRequest->reveal(), "paramQuery2"));

        $this->assertEquals(false, $this->traitTestInstance->checkParameter($this->serverRequest->reveal(), "paramBody3"));
        $this->assertEquals(false, $this->traitTestInstance->checkParameter($this->serverRequest->reveal(), "paramQuery3"));
    }

    public function testWhetherMethodGetParameterIsWorks(): void
    {
        $this->serverRequest->getParsedBody()->willReturn(["paramBody1" => "123", "paramBody2" => 321]);
        $this->serverRequest->getQueryParams()->willReturn(["paramQuery1" => "789", "paramQuery2" => 987]);

        $result = $this->traitTestInstance->getParameter($this->serverRequest->reveal());

        $this->assertEquals(["paramBody1" => "123", "paramBody2" => 321, "paramQuery1" => "789", "paramQuery2" => 987], $result);
    }

    public function testWhetherMethodAnnotationParamsWorks(): void
    {
        $this->serverRequest->getParsedBody()->willReturn(["from" => "a", "to" => "z"]);
        $this->serverRequest->getQueryParams()->willReturn(["data" => "123"]);

        $this->serverRequest->getAttribute('action')->willReturn("destinationMethodWithAnnotations");
        
        $result = $this->traitTestInstanceWithMethodAnnotations->handle($this->serverRequest->reveal());
        
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals("{\"Call\":\"destinationMethod with annotations for unit test\"}", $result->getBody()->getContents()); 
    }

    public function testWhetherMethodAnnotationParamsWorksWhenParamsNotFound(): void
    {
        $this->serverRequest->getParsedBody()->willReturn(["from" => "a"]);
        $this->serverRequest->getQueryParams()->willReturn(["data" => "123"]);

        $this->serverRequest->getAttribute('action')->willReturn("destinationMethodWithAnnotations");
        
        $result = $this->traitTestInstanceWithMethodAnnotations->handle($this->serverRequest->reveal());
        
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals("\"request parameter \u0027to\u0027 required for handler method named: destinationMethodWithAnnotationsAction!\"", $result->getBody()->getContents());
    }

}