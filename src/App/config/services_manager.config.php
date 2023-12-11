<?php

use App\Service\LoggerFactory;
use App\Handler;
use Psr\Log\LoggerInterface;

return [
    // Key -> Factory
    'factories' => [
        
        // Handlers
        Handler\UserHandler::class => Handler\UserHandlerFactory::class,
        Handler\CategoryDataHandler::class => Handler\CategoryDataHandlerFactory::class,
        Handler\ExpensesDataHandler::class => Handler\ExpensesDataHandlerFactory::class,
        Handler\BackendServiceHandler::class => Handler\BackendServiceHandlerFactory::class,
        
        // Services
        LoggerInterface::class => LoggerFactory::class,
        \App\Service\UserDataService::class => \App\Service\UserDataServiceFactory::class,
        \App\Service\CategoryDataService::class => \App\Service\CategoryDataServiceFactory::class,
        \App\Service\ExpensesDataService::class => \App\Service\ExpensesDataServiceFactory::class,
        \App\Service\UtilitiesService::class => \App\Service\UtilitiesServiceFactory::class,
        \App\Service\BackendServiceService::class => \App\Service\BackendServiceServiceFactory::class,
        
        // Middleware
        \App\Middleware\LoginInfoMiddleware::class => \App\Middleware\LoginInfoMiddlewareFactory::class,
        \App\Middleware\BackendServiceAllowMiddleware::class => \App\Middleware\BackendServiceAllowMiddlewareFactory::class,
    ],
    'aliases' => [

    ]
];