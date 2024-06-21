<?php
namespace App\Handler;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use ReflectionClass;

trait HandlerActionMapping{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $action = $request->getAttribute('action');

        if($action === null) {
            return new JsonResponse([], 404);
        }

        $params = $this->getParameter($request);
        $actionName = $action."Action";

        $checkAnnotationsResult = $this->checkMethodAnnotationsForParams($actionName, $params);
        
        if($checkAnnotationsResult != null) {
            return $checkAnnotationsResult;
        }

        if(method_exists($this, $actionName)) {
            return $this->$actionName($request);
        }

        return new JsonResponse([], 404);
    }

    public function checkMethodAnnotationsForParams($actionName, $params) : null | JsonResponse
    {
        $rc = new ReflectionClass(__CLASS__);
        $methods = $rc->getMethods();
        foreach($methods as $method){
            if(strcmp($method->name, $actionName) == 0) {
                $methodDocumentation = $method->getDocComment();
                
                //NOTE: check annotation params
                $annotationsParamsRaw = null;
                $annotationsParams = null;
                
                preg_match_all("#@RequestParams[(](.*?)[)]#s", $methodDocumentation, $annotationsParamsRaw);

                if(count($annotationsParamsRaw) > 1) {
                    $p = $annotationsParamsRaw[1];
                    if(count($p) > 0) {
                        $annotationsParams = explode( '|', str_replace(" ", "", $p)[0]);
                    }
                }

                if($annotationsParams != null) {
                    foreach($annotationsParams as $annotationsParam) {
                        $paramFound = false;
                        foreach(array_keys($params) as $paramName) {
                            if(strcmp($paramName, $annotationsParam) == 0) {
                                $paramFound  = true;
                            }
                        }

                        if($paramFound == false) {
                            return new JsonResponse("request parameter '".$annotationsParam."' required for handler method named: ".$actionName."!", 400);
                        }
                    }
                }
                
                return null;
            }
        }

        return new JsonResponse("no handler method named: ".$actionName." found!", 404);
    }

    public function checkParameter(ServerRequestInterface $request, $name): bool
    {
        $params = $this->getParameter($request);

        if($params == null || !is_array($params) || !array_key_exists($name, $params)){
            return false;
        }

        return true;
    }

    public function getParameter(ServerRequestInterface $request): array
    {
        return array_merge($request->getParsedBody(), $request->getQueryParams());
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