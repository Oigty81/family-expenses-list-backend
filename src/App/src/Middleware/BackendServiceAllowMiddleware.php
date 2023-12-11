<?php

declare(strict_types=1);

namespace App\Middleware;

use Laminas\Diactoros\Response\JsonResponse;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Psr\Log\LoggerInterface;

class BackendServiceAllowMiddleware implements MiddlewareInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(
        array $config,
        LoggerInterface $logger,
    )
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {        
        if($this->config["backendServiceEnabled"] == true) {
            return $handler->handle($request);
        } else {
            $this->logger->info("backend service functions are not allowed!");
            return new JsonResponse(['err' => 'backend service functions are not allowed!'], 403);
        }
    }
}