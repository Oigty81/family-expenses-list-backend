<?php

declare(strict_types=1);

namespace App\Handler;

use App\Service\ExpensesDataService;
use App\Service\UtilitiesService;
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

    /**
     * @var UtilitiesService
     */
    private $utilitiesService;

    public function __construct(
        array $config,
        LoggerInterface $logger,
        ExpensesDataService $expensesDataService,
        UtilitiesService $utilitiesService
    )
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->expensesDataService = $expensesDataService;
        $this->utilitiesService = $utilitiesService;
    }

    public function getExpensesPeriodAction(ServerRequestInterface $request): ResponseInterface
    {
       
        $params = $this->getParameter($request);
           
        $check = $this->utilitiesService->checkWhetherParameterExistAndIsString($params, 'from');
        if($check != null) {
            return $check;
        }

        $check = $this->utilitiesService->checkWhetherParameterExistAndIsString($params, 'to');
        if($check != null) {
            return $check;
        }

        $serviceResult = $this->expensesDataService->fetchExpensesPeriod($params['from'], $params['to']);
        $error = $this->utilitiesService->checkServiceErrorForResponse($serviceResult);
        return $error == null ? new JsonResponse($serviceResult) : $error;
    }
    
    public function putExpensesAction(ServerRequestInterface $request): ResponseInterface
    {
       
        $userId = $request->getAttribute("userId");

        $params = $this->getParameter($request);
           
        $check = $this->utilitiesService->checkWhetherParameterExistAndIsNumeric($params, 'categoryCompositionId');
        if($check != null) {
            return $check;
        }

        $check = $this->utilitiesService->checkWhetherParameterExistAndIsNumeric($params, 'price');
        if($check != null) {
            return $check;
        }
        
        $check = $this->utilitiesService->checkWhetherParameterExistAndIsString($params, 'metatext');
        if($check != null) {
            return $check;
        }

        $serviceResult = $this->expensesDataService->insertExpenses($userId, $params['categoryCompositionId'], $params['price'], $params['metatext']);
        $error = $this->utilitiesService->checkServiceErrorForResponse($serviceResult);
        return $error == null ? new JsonResponse($serviceResult) : $error;
    }
}
