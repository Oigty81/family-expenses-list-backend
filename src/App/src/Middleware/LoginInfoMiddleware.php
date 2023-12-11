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
        $requestHeader = $request->getHeaders();
        $token = null;
        $username = "";
        $userId = 0;

        if(array_key_exists('authorization', $requestHeader)) {
            $at = $requestHeader["authorization"][0];
            $accessToken = str_replace("Bearer ","", $at);

            try {
                $publicKey = file_get_contents($this->config["jwtSecretPublicKeyFile"]);
                $token = JWT::decode($accessToken, new Key($publicKey, 'RS256'));
            } catch(ExpiredException $e) {
                return new TextResponse("JWT is expired" , 401); 
            } catch(Exception $e) {
                return new TextResponse($e->getMessage() , 400); 
            }

            
            if($token != null) {
                $td = $token->data;
                $username = $td->username;
                $userId = $td->userId;
            }

            return $handler->handle($request
                    ->withAttribute("username", $username)
                    ->withAttribute("userId", $userId)
                );

        } else {
            return new TextResponse("no or invalid access token!" , 403, ['WWW-Authenticate' => 'Bearer realm=""']); 
        }
    }
}