<?php

declare(strict_types=1);

namespace App\Handler;

use App\Service\CategoryDataService;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

use Laminas\Diactoros\Response\TextResponse;

class CategoryDataHandler implements RequestHandlerInterface
{
    use HandlerActionMapping;
    /**
     * @var array
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CategoryDataService
     */
    private $categoryDataService;

    
    public function __construct(
        array $config,
        LoggerInterface $logger,
        CategoryDataService $categoryDataService,
    )
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->categoryDataService = $categoryDataService;
    }

    public function getCategoriesAction(ServerRequestInterface $request): ResponseInterface
    {
        $serviceResult = $this->categoryDataService->getCategories();
        $error = $this->checkServiceErrorForResponse($serviceResult);
        return $error == null ? new JsonResponse($serviceResult) : $error;
    }

    /**
     * @RequestParams(title)
     */
    public function putCategoryAction(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->getParameter($request);

        $userId = $request->getAttribute("userId");

        $serviceResult = $this->categoryDataService->storeCategory($userId, $params["title"]);
        $error = $this->checkServiceErrorForResponse($serviceResult);
        return $error == null ? new JsonResponse($serviceResult) : $error;
    }

    /**
     * @RequestParams(categoryIds)
     */
    public function putCategoryCompositionAction(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->getParameter($request);

        $userId = $request->getAttribute("userId");

        $serviceResult = $this->categoryDataService->storeCategoryComposition($userId, $params["categoryIds"]);
        $error = $this->checkServiceErrorForResponse($serviceResult);
        return $error == null ? new JsonResponse($serviceResult) : $error;
    }
}
