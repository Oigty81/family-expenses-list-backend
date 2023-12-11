<?php

declare(strict_types=1);

namespace App\Service;

use Throwable;
use DateTimeImmutable;
use Laminas\Db\Adapter\Adapter;
use Psr\Log\LoggerInterface;
use Firebase\JWT\JWT;

class UserDataService{

    /**
     * @var array
     */
    private $config;
    /**
     * @var Adapter
     */
    private $db;
        /**
     * @var LoggerInterface
     */
    private $logger;

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