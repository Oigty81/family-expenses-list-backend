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
}
