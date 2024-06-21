<?php

declare(strict_types=1);

namespace App\Handler;

use App\Service\UserDataService;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class UserHandler implements RequestHandlerInterface
{
    use HandlerActionMapping;

    /**
     * @var array
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UserDataService
     */
    private $userDataService;

    public function __construct(
        array $config,
        LoggerInterface $logger,
        UserDataService $userDataService,
    )
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->userDataService = $userDataService;
    }

    /**
     * @RequestParams(username | password)
     */
    public function authAction(ServerRequestInterface $request): ResponseInterface
    {
        $params = $this->getParameter($request);
        
        $longExpireTime = false;

        if(array_key_exists('longExpireTime', $params)) {
            $longExpireTime = true;
        }
        
        $serviceResult = $this->userDataService->authFlow($params['username'], $params['password'], $longExpireTime);

        if(array_key_exists('error', $serviceResult)) {
            if($serviceResult["error"] == -1) {
                return new JsonResponse($serviceResult["errormsg"], 400);
            } else if($serviceResult["error"] == -2) {
                return new JsonResponse($serviceResult["errormsg"], 400);
            } else {
                return new JsonResponse($serviceResult["errormsg"], 500);
            }
        } else {
            return new JsonResponse($serviceResult);
        }  
    }
}