<?php

declare(strict_types=1);

namespace App\Service;

use Laminas\Db\Adapter\Adapter;
use Psr\Log\LoggerInterface;

use Exception;

class ExpensesDataService
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Adapter
     */
    private $db;

    /**
     * @var CategoryDataService
     */
    private $categoryDataService;

    public function __construct(
        array $config,
        Adapter $db,
        LoggerInterface $logger,
        CategoryDataService $categoryDataService,
    )
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->db = $db;
        $this->categoryDataService = $categoryDataService;
    }
}