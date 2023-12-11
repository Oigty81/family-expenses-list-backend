<?php

declare(strict_types=1);

namespace App\Handler;

use App\Service\BackendServiceService;
use App\Service\UtilitiesService;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class BackendServiceHandler implements RequestHandlerInterface
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
     * @var BackendServiceService
     */
    private $backendServiceService;

    /**
     * @var UtilitiesService
     */
    private $utilitiesService;

    public function __construct(
        array $config,
        LoggerInterface $logger,
        BackendServiceService $backendServiceService,
        UtilitiesService $utilitiesService
    )
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->backendServiceService = $backendServiceService;
        $this->utilitiesService = $utilitiesService;
    }

    public function createTableStructAction(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse($this->backendServiceService->createTableStruct());
    }

    public function createInitialContentAction(ServerRequestInterface $request): ResponseInterface
    {
        
        $params = $this->getParameter($request);

        $check = $this->utilitiesService->checkWhetherParameterExistAndIsString($params, 'username');
        if($check != null) {
            return $check;
        }

        return new JsonResponse($this->backendServiceService->createInitialContent($params['username']));
    }

    public function deleteAllEntriesAction(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse($this->backendServiceService->deleteAllEntries());
    }

    public function deleteAllTablesAction(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse($this->backendServiceService->deleteAllTables());
    }
    
    public function createUserAction(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->getParameter($request);

        $check = $this->utilitiesService->checkWhetherParameterExistAndIsString($params, 'username');
        if($check != null) {
            return $check;
        }

        $check = $this->utilitiesService->checkWhetherParameterExistAndIsString($params, 'password');
        if($check != null) {
            return $check;
        }

        $check = $this->utilitiesService->checkWhetherParameterExistAndIsString($params, 'displayname');
        if($check != null) {
            return $check;
        }
        
        return new JsonResponse($this->backendServiceService->createUser($params['username'], $params['password'], $params['displayname']));
    }

    public function deleteUserAction(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->getParameter($request);

        $check = $this->utilitiesService->checkWhetherParameterExistAndIsString($params, 'username');
        if($check != null) {
            return $check;
        }

        return new JsonResponse($this->backendServiceService->deleteUser($params['username']));
    }
    
}
