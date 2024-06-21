<?php

declare(strict_types=1);

namespace App\Handler;

use App\Service\BackendServiceService;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class BackendServiceHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new BackendServiceHandler(
            $container->get("config")["config"],
            $container->get(LoggerInterface::class),
            $container->get(BackendServiceService::class),
        );
    }
}
