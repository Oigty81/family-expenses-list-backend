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

    
}
