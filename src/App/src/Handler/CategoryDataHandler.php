<?php

declare(strict_types=1);

namespace App\Handler;

use App\Service\CategoryDataService;
use App\Service\UtilitiesService;

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

    /**
     * @var UtilitiesService
     */
    private $utilitiesService;
    
    public function __construct(
        array $config,
        LoggerInterface $logger,
        CategoryDataService $categoryDataService,
        UtilitiesService $utilitiesService
    )
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->categoryDataService = $categoryDataService;
        $this->utilitiesService = $utilitiesService;
    }

    public function getCategoriesAction(ServerRequestInterface $request): ResponseInterface
    {
        $serviceResult = $this->categoryDataService->getCategories();
        $error = $this->utilitiesService->checkServiceErrorForResponse($serviceResult);
        return $error == null ? new JsonResponse($serviceResult) : $error;
    }

    public function putCategoryAction(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->getParameter($request);

        $check = $this->utilitiesService->checkWhetherParameterExistAndIsString($params, 'title');
        if($check != null) {
            return $check;
        }

        $userId = $request->getAttribute("userId");

        $serviceResult = $this->categoryDataService->storeCategory($userId, $params["title"]);
        $error = $this->utilitiesService->checkServiceErrorForResponse($serviceResult);
        return $error == null ? new JsonResponse($serviceResult) : $error;
    }

    public function putCategoryCompositionAction(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->getParameter($request);

        $check = $this->utilitiesService->checkWhetherParameterExistAndIsArray($params, 'categoryIds');
        if($check != null) {
            return $check;
        }

        $userId = $request->getAttribute("userId");

        $serviceResult = $this->categoryDataService->storeCategoryComposition($userId, $params["categoryIds"]);
        $error = $this->utilitiesService->checkServiceErrorForResponse($serviceResult);
        return $error == null ? new JsonResponse($serviceResult) : $error;
    }
}
