<?php

declare(strict_types=1);

namespace App\Service;

use Laminas\Db\Adapter\Adapter;
use Psr\Log\LoggerInterface;

use App\Exception\ArrayContentException;

use Exception;

class CategoryDataService
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
        $this->logger = $logger;
        $this->db = $db;
    }
}