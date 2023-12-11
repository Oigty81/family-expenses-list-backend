<?php

declare(strict_types=1);

namespace App\Service;

use Throwable;
use Laminas\Db\Adapter\Adapter;
use Psr\Log\LoggerInterface;

class BackendServiceService
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

    public function __construct(
        array $config,
        Adapter $db,
        LoggerInterface $logger
    )
    {
        $this->config = $config;
        $this->db = $db;
        $this->logger = $logger;
    }
}
