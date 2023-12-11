<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class UserDataServiceFactory
{
    public function __invoke(ContainerInterface $container): UserDataService
    {
        return new UserDataService(
            $container->get("config")["config"],
            $container->get("mainDb"),
            $container->get(LoggerInterface::class)
        );
    }
}