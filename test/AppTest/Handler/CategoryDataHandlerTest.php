<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\CategoryDataHandler;
use App\Service\CategoryDataService;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

use Psr\Http\Message\ServerRequestInterface;

use Psr\Log\LoggerInterface;


class CategoryDataHandlerTest extends TestCase
{
    use ProphecyTrait;

    /** @var array|ObjectProphecy */
    protected $config;

     /** @var LoggerInterface|ObjectProphecy */
     protected $loggerInterface;

     /** @var CategoryDataService|ObjectProphecy */
     protected $categoryDataService;

    /** @var ServerRequestInterface|ObjectProphecy */
    protected $serverRequest;

    protected function setUp() : void
    {
        $this->config = [];
        $this->loggerInterface  = $this->prophesize(LoggerInterface::class);
        $this->categoryDataService = $this->prophesize(CategoryDataService::class);
        $this->serverRequest = $this->prophesize(ServerRequestInterface::class);
    }

    
    public function testThatHanderMethodGetCategoriesActionCallsItsServiceMethodsCorrectly(): void
    {
        $this->categoryDataService->getCategories()->willReturn(["Call" => 1]);
        
        $categoryDataHandler = new CategoryDataHandler(
            $this->config,
            $this->loggerInterface->reveal(),
            $this->categoryDataService->reveal(),
        );

        $result = $categoryDataHandler->getCategoriesAction($this->serverRequest->reveal());

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals("{\"Call\":1}", $result->getBody()->getContents()); 
    }

    public function testThatHandlerMethodPutCategoryActionCallsItsServiceMethodsCorrectly(): void
    {     
        $this->categoryDataService->storeCategory(1, "test")->willReturn(["TestCall" => 1])->shouldBeCalledTimes(1);
        
        $categoryDataHandler = new CategoryDataHandler(
            $this->config,
            $this->loggerInterface->reveal(),
            $this->categoryDataService->reveal(),
        );

        $this->serverRequest->getQueryParams()->willReturn(["title" => "test"]);
        $this->serverRequest->getParsedBody()->willReturn([]);
        $this->serverRequest->getAttribute("userId")->willReturn(1);

        $result = $categoryDataHandler->putCategoryAction($this->serverRequest->reveal());

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals("{\"TestCall\":1}", $result->getBody()->getContents());
    }

    public function testThatHandlerMethodPutCategoryCompositionActionCallsItsServiceMethodsCorrectly(): void
    {
        $this->categoryDataService->storeCategoryComposition(Argument::any(), Argument::any())->willReturn(["TestCall" => 1])->shouldBeCalledTimes(1);

        $categoryDataHandler = new CategoryDataHandler(
            $this->config,
            $this->loggerInterface->reveal(),
            $this->categoryDataService->reveal(),
        );

        $this->serverRequest->getQueryParams()->willReturn(["categoryIds" => [1, 2]]);
        $this->serverRequest->getParsedBody()->willReturn([]);
        $this->serverRequest->getAttribute("userId")->willReturn(1);

        $result = $categoryDataHandler->putCategoryCompositionAction($this->serverRequest->reveal());

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals("{\"TestCall\":1}", $result->getBody()->getContents());
    }
}
