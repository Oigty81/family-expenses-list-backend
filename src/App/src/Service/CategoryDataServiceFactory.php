<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class CategoryDataServiceFactory
{
    public function __invoke(ContainerInterface $container): CategoryDataService
    {
        return new CategoryDataService(
            $container->get("config")["config"],
            $container->get("mainDb"),
            $container->get(LoggerInterface::class)
        );
    }
}