<?php

declare(strict_types=1);

use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;

return static function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
        
    $app->route('/api/v1/user/{action:\w+}', [
        \Mezzio\Helper\BodyParams\BodyParamsMiddleware::class,
        App\Handler\UserHandler::class,
    ],  ["GET", "POST"],'api.v1.user');

    $app->route('/api/v1/category/{action:\w+}', [
        \Mezzio\Helper\BodyParams\BodyParamsMiddleware::class,
        App\Middleware\LoginInfoMiddleware::class,
        App\Handler\CategoryDataHandler::class,
    ],  ["GET", "POST"],'api.v1.category');

    $app->route('/api/v1/expenses/{action:\w+}', [
        \Mezzio\Helper\BodyParams\BodyParamsMiddleware::class,
        App\Middleware\LoginInfoMiddleware::class,
        App\Handler\ExpensesDataHandler::class,
    ],  ["GET", "POST"],'api.v1.expenses');

    $app->route('/api/v1/backendservice/{action:\w+}', [
        \Mezzio\Helper\BodyParams\BodyParamsMiddleware::class,
        App\Middleware\BackendServiceAllowMiddleware::class,
        App\Handler\BackendServiceHandler::class,
    ],  ["GET", "POST"],'api.v1.backendservice');
};
