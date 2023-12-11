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
}
