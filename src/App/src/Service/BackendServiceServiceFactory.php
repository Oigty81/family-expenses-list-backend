<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class BackendServiceServiceFactory
{
    public function __invoke(ContainerInterface $container): BackendServiceService
    {
        return new BackendServiceService(
            $container->get("config")["config"],
            $container->get("mainDb"),
            $container->get(LoggerInterface::class)
        );
    }
}
