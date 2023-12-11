<?php

declare(strict_types=1);

namespace AppTest\Service;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

use Psr\Log\LoggerInterface;
use Laminas\Diactoros\Response\JsonResponse;

use App\Service\UtilitiesService;

class UtilitiesServiceTest extends TestCase
{
    use ProphecyTrait;
    /** @var array */
    private $testParams;

    /** @var LoggerInterface | ObjectProphecy */
    private $logger;

    /** @var UtilitiesService */
    private $utilitiesService;

    protected function setUp() : void
    {
        $config = [ ];
        $this->testParams = ["AAA_Text" => "text", "BBB_Number" => 100, "CCC_BOOL" => false, "DDD_ARRAY" => ["a" => 100, "b" => 200 ] ];
        
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->utilitiesService = new UtilitiesService($config, $this->logger->reveal());              
    }

    //// ------------------------------------------------------------------------------------------------------

    public function testItcheckWhetherParameterExistWhenParameterIsNotAvailable() : void 
    {
        /** @var JsonResponse $result */
        $result = $this->utilitiesService->checkWhetherParameterExist($this->testParams, "BB");
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals("no parameter like \"BB\" available", $result->getPayload()["err"]);
    }

    public function testItcheckWhetherParameterExistWhenIsAvailable() : void 
    {
        /** @var JsonResponse $result */
        $result = $this->utilitiesService->checkWhetherParameterExist($this->testParams, "AAA_Text");
        $this->assertEquals(null, $result);
    }

    //// ------------------------------------------------------------------------------------------------------

    public function testItcheckWhetherParameterExistAndIsNumericWhenParameterIsNotAvailable() : void 
    {
        /** @var JsonResponse $result */
        $result = $this->utilitiesService->checkWhetherParameterExistAndIsNumeric($this->testParams, "BB");
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals("no parameter like \"BB\" available", $result->getPayload()["err"]);
    }

    public function testItCheckWhetherParameterExistAndIsNumericWhenIsNotNumeric() : void 
    {
        /** @var JsonResponse $result */
        $result = $this->utilitiesService->checkWhetherParameterExistAndIsNumeric($this->testParams, "AAA_Text");
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals("parameter \"AAA_Text\" is not from type numeric", $result->getPayload()["err"]);
    }

    public function testItCheckWhetherParameterExistAndIsNumericWhenParameterIsAvailableAndIsNumeric() : void 
    {
        $result = $this->utilitiesService->checkWhetherParameterExistAndIsNumeric($this->testParams, "BBB_Number");
        $this->assertEquals(null, $result);
    }

    //// ------------------------------------------------------------------------------------------------------

    public function testItCheckWhetherParameterExistAndIsStringWhenParameterIsNotAvailable() : void 
    {
        /** @var JsonResponse $result */
        $result = $this->utilitiesService->checkWhetherParameterExistAndIsString($this->testParams, "AA");
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals("no parameter like \"AA\" available", $result->getPayload()["err"]);
    }

    public function testItCheckWhetherParameterExistAndIsStringWhenIsNotString() : void 
    {
        /** @var JsonResponse $result */
        $result = $this->utilitiesService->checkWhetherParameterExistAndIsString($this->testParams, "BBB_Number");
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals("parameter \"BBB_Number\" is not from type string", $result->getPayload()["err"]);
    }

    public function testItCheckWhetherParameterExistAndIsStringWhenParameterIsAvailableAndIsString() : void 
    {
        $result = $this->utilitiesService->checkWhetherParameterExistAndIsString($this->testParams, "AAA_Text");
        $this->assertEquals(null, $result);
    }

    //// ------------------------------------------------------------------------------------------------------

    public function testItCheckWhetherParameterExistAndIsBoolWhenParameterIsNotAvailable() : void 
    {
        /** @var JsonResponse $result */
        $result = $this->utilitiesService->checkWhetherParameterExistAndIsBool($this->testParams, "CC");
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals("no parameter like \"CC\" available", $result->getPayload()["err"]);
    }

    public function testItCheckWhetherParameterExistAndIsBoolhenIsNotBool() : void 
    {
        /** @var JsonResponse $result */
        $result = $this->utilitiesService->checkWhetherParameterExistAndIsBool($this->testParams, "BBB_Number");
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals("parameter \"BBB_Number\" is not from type bool", $result->getPayload()["err"]);
    }

    public function testItCheckWhetherParameterExistAndIsBoolWhenParameterIsAvailableAndIsBool() : void 
    {
        $result = $this->utilitiesService->checkWhetherParameterExistAndIsBool($this->testParams, "CCC_BOOL");
        $this->assertEquals(null, $result);
    }

    //// ------------------------------------------------------------------------------------------------------
    
    public function testItCheckWhetherParameterExistAndIsArrayWhenParameterIsNotAvailable() : void 
    {
        /** @var JsonResponse $result */
        $result = $this->utilitiesService->checkWhetherParameterExistAndIsArray($this->testParams, "DD");
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals("no parameter like \"DD\" available", $result->getPayload()["err"]);
    }

    public function testItCheckWhetherParameterExistAndIsArrayWhenIsNotString() : void 
    {
        /** @var JsonResponse $result */
        $result = $this->utilitiesService->checkWhetherParameterExistAndIsArray($this->testParams, "BBB_Number");
        $this->assertEquals(400, $result->getStatusCode());
        $this->assertEquals("parameter \"BBB_Number\" is not from type array", $result->getPayload()["err"]);
    }

    public function testItCheckWhetherParameterExistAndIsArrayWhenParameterIsAvailableAndIsString() : void 
    {
        $result = $this->utilitiesService->checkWhetherParameterExistAndIsArray($this->testParams, "DDD_ARRAY");
        $this->assertEquals(null, $result);
    }
}


