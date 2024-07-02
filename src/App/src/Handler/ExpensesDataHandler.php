<?php

declare(strict_types=1);

namespace App\Handler;

use App\Service\ExpensesDataService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class ExpensesDataHandler implements RequestHandlerInterface
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
     * @var ExpensesDataService
     */
    private $expensesDataService;

    public function __construct(
        array $config,
        LoggerInterface $logger,
        ExpensesDataService $expensesDataService,
    )
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->expensesDataService = $expensesDataService;
    }

    public function getExpensesAction(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->getParameter($request); 
        $serviceResult = $this->expensesDataService->getExpenses($params);
        $error = $this->checkServiceErrorForResponse($serviceResult);
        return $error == null ? new JsonResponse($serviceResult) : $error;
    }
    
    /**
     * @RequestParams(categoryCompositionId | price | created | metatext)
     */
    public function putExpensesAction(ServerRequestInterface $request): ResponseInterface
    {
        $userId = $request->getAttribute("userId");
        $params = $this->getParameter($request);
           
        $serviceResult = $this->expensesDataService->insertExpenses($userId, $params['categoryCompositionId'], $params['price'], $params['created'], $params['metatext']);
        $error = $this->checkServiceErrorForResponse($serviceResult);
        return $error == null ? new JsonResponse($serviceResult) : $error;
    }

    /**
     * @RequestParams(id | data)
     */
    public function updateExpensesAction(ServerRequestInterface $request): ResponseInterface
    {
        $userId = $request->getAttribute("userId");
        $params = $this->getParameter($request);
           
        $serviceResult = $this->expensesDataService->updateExpenses($userId, $params["id"], $params["data"]);
        $error = $this->checkServiceErrorForResponse($serviceResult);
        return $error == null ? new JsonResponse($serviceResult) : $error;
    }

    /**
     * @RequestParams(id)
     */
    public function deleteExpensesAction(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->getParameter($request);
           
        $serviceResult = $this->expensesDataService->deleteExpenses($params['id']);
        $error = $this->checkServiceErrorForResponse($serviceResult);
        return $error == null ? new JsonResponse($serviceResult) : $error;
    }
}
