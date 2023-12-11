<?php

declare(strict_types=1);

namespace App\Middleware;

use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\Response\RedirectResponse;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use Psr\Log\LoggerInterface;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

use Exception;

class LoginInfoMiddleware implements MiddlewareInterface
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
        return $handler->handle($request);
    }
}