<?php
namespace App\Handler;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

trait HandlerActionMapping{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $action = $request->getAttribute('action');

        if($action === null) {
            return new JsonResponse([], 404);
        }

        $actionName = $action."Action";
        if(method_exists($this, $actionName)) {
            return $this->$actionName($request);
        }

        return new JsonResponse([], 404);
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
}