<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class UtilitiesServiceFactory
{
    public function __invoke(ContainerInterface $container): UtilitiesService
    {
        return new UtilitiesService(
            $container->get("config")["config"],
            $container->get(LoggerInterface::class)
        );
    }
}