<?php

declare(strict_types=1);

namespace App\Handler;

use App\Service\CategoryDataService;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class CategoryDataHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new CategoryDataHandler(
            $container->get("config")["config"],
            $container->get(LoggerInterface::class),
            $container->get(CategoryDataService::class),
        );
    }
}
