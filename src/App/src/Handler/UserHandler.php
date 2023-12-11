<?php

declare(strict_types=1);

namespace App\Handler;

use App\Service\UserDataService;
use App\Service\UtilitiesService;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class UserHandler implements RequestHandlerInterface
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
     * @var UserDataService
     */
    private $userDataService;

    /**
     * @var UtilitiesService
     */
    private $utilitiesService;

    public function __construct(
        array $config,
        LoggerInterface $logger,
        UserDataService $userDataService,
        UtilitiesService $utilitiesService
    )
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->userDataService = $userDataService;
        $this->utilitiesService = $utilitiesService;
    }
}