<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Log\LoggerInterface;
use App\Service\ExpensesDataService;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ExpensesDataHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new ExpensesDataHandler(
            $container->get("config")["config"],
            $container->get(LoggerInterface::class),
            $container->get(ExpensesDataService::class),
        );
    }
}
