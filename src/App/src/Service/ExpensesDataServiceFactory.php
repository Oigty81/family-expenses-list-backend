<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class ExpensesDataServiceFactory
{
    public function __invoke(ContainerInterface $container): ExpensesDataService
    {
        return new ExpensesDataService(
            $container->get("config")["config"],
            $container->get("mainDb"),
            $container->get(LoggerInterface::class),
            $container->get(CategoryDataService::class),
        );
    }
}