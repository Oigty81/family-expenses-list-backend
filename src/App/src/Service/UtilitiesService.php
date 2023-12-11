<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use Laminas\Diactoros\Response\JsonResponse;

class UtilitiesService{

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
        LoggerInterface $logger
    )
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function checkWhetherParameterExist($params, $param) : null | JsonResponse
    {
        if (!array_key_exists($param, $params)) {
            return new JsonResponse(['err' => 'no parameter like "'.$param.'" available'], 400);
        }
        
        return null;
    }

    public function checkWhetherParameterExistAndIsNumeric($params, $param) : null | JsonResponse
    {
        if (!array_key_exists($param, $params)) {
            return new JsonResponse(['err' => 'no parameter like "'.$param.'" available'], 400);
        }

        if(!is_numeric($params[$param])) {
            return new JsonResponse(['err' => 'parameter "'.$param.'" is not from type numeric'], 400);
        }
        
        return null;
    }
    
    public function checkWhetherParameterExistAndIsString($params, $param) : null | JsonResponse
    {
        if (!array_key_exists($param, $params)) {
            return new JsonResponse(['err' => 'no parameter like "'.$param.'" available'], 400);
        }

        if(!is_string($params[$param])) {
            return new JsonResponse(['err' => 'parameter "'.$param.'" is not from type string'], 400);
        }
        
        return null;
    }

    public function checkWhetherParameterExistAndIsBool($params, $param) : null | JsonResponse
    { 
        if (!array_key_exists($param, $params)) {
            return new JsonResponse(['err' => 'no parameter like "'.$param.'" available'], 400);
        }

        if(!is_bool($params[$param])) {
            return new JsonResponse(['err' => 'parameter "'.$param.'" is not from type bool'], 400);
        }
        
        return null;
    }

    public function checkWhetherParameterExistAndIsArray($params, $param) : null | JsonResponse
    {
        if (!array_key_exists($param, $params)) {
            return new JsonResponse(['err' => 'no parameter like "'.$param.'" available'], 400);
        }

        if(!is_array($params[$param])) {
            return new JsonResponse(['err' => 'parameter "'.$param.'" is not from type array'], 400);
        }
        
        return null;
    }
    
    public function checkServiceErrorForResponse($serviceResult) : null | JsonResponse
    {
        if(is_array($serviceResult)) {
            if(array_key_exists('error', $serviceResult)) {
                return new JsonResponse($serviceResult["errormsg"], 500);
            }
        }

        return null;
    }
}