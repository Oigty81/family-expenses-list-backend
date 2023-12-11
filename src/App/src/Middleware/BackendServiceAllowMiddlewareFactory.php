<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Log\LoggerInterface;

class BackendServiceAllowMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): MiddlewareInterface
    {
        return new BackendServiceAllowMiddleware(
            $container->get("config")["config"],
            $container->get(LoggerInterface::class),
        );
    }
}