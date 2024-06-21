<?php

declare(strict_types=1);

namespace App\Handler;

use App\Service\UserDataService;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class UserHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new UserHandler(
            $container->get("config")["config"],
            $container->get(LoggerInterface::class),
            $container->get(UserDataService::class),
        );
    }
}
