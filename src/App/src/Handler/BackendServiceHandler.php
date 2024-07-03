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

    public function __construct(
        array $config,
        LoggerInterface $logger,
        BackendServiceService $backendServiceService,
    )
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->backendServiceService = $backendServiceService;
    }

    public function phpInfoAction(ServerRequestInterface $request): ResponseInterface
    {
        phpinfo();
        die();
    }

    public function getSqlTableQuerysAction(ServerRequestInterface $request): ResponseInterface
    {
        return new TextResponse($this->backendServiceService->getSqlTableQuerys());
    }

    public function createTableStructAction(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse($this->backendServiceService->createTableStruct());
    }

    /**
     * @RequestParams(username)
     */
    public function createInitialContentAction(ServerRequestInterface $request): ResponseInterface
    {
        
        $params = $this->getParameter($request);
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
    
    /**
     * @RequestParams(username | password | displayname)
     */
    public function createUserAction(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->getParameter($request);
        return new JsonResponse($this->backendServiceService->createUser($params['username'], $params['password'], $params['displayname']));
    }

    /**
     * @RequestParams(username)
     */
    public function deleteUserAction(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->getParameter($request);
        return new JsonResponse($this->backendServiceService->deleteUser($params['username']));
    }
    
}
