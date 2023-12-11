<?php

declare(strict_types=1);

namespace AppTest\Middleware;

use DateTimeImmutable;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

use Psr\Log\LoggerInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\TextResponse;
use Firebase\JWT\JWT;

use App\Middleware\LoginInfoMiddleware;

class LoginInfoMiddlewareTest extends TestCase
{
    use ProphecyTrait;

    protected $config;

    /** @var LoggerInterface|ObjectProphecy */
    protected $loggerInterface;

    /** @var ServerRequestInterface|ObjectProphecy */
    protected $serverRequest;

    /** @var RequestHandlerInterface|ObjectProphecy */
    protected $requestHandler;

    /** @var ResponseInterface|ObjectProphecy */
    protected $response;

    /** @var LoginInfoMiddleware */
    private $loginInfoMiddleware;

    protected function setUp() : void
    {
        $this->config = require "Test/AppTest/_helper/ConfigLoader.php";

        $this->loggerInterface  = $this->prophesize(LoggerInterface::class);
        $this->serverRequest = $this->prophesize(ServerRequestInterface::class);
                
        $this->requestHandler = $this->prophesize(RequestHandlerInterface::class);
               
        $this->response = $this->prophesize(ResponseInterface::class);
                
        $this->loginInfoMiddleware = new LoginInfoMiddleware($this->config, $this->loggerInterface->reveal()); 
    }

    public function testItWhenNoBearerTokenIsAvailable() : void 
    {
        $this->serverRequest->getHeaders()->willReturn([]);
        $this->requestHandler->handle($this->serverRequest)->willReturn(new TextResponse("Default Text...", 200));

        /** @var TextResponse $result */
        $result = $this->loginInfoMiddleware->process($this->serverRequest->reveal(), $this->requestHandler->reveal());
                
        $this->assertEquals(403, $result->getStatusCode());
        $this->assertEquals("Bearer realm=\"\"", $result->getHeaders()["WWW-Authenticate"][0]);
        $this->assertEquals("no or invalid access token!", $result->getBody()->getContents()); 
    }

    public function testItWhetherCurrentTokenIsExpired() : void 
    {
        $privateKey = file_get_contents($this->config["jwtSecretPrivateKeyFile"]);
        $tokenId = base64_encode(random_bytes(16));
        $issuedAt   = new DateTimeImmutable();
        $expire = $issuedAt->modify(' -1 seconds')->getTimestamp();

        $payload = [
            'iat'  => $issuedAt->getTimestamp(),    
            'jti'  => $tokenId,                     
            'iss'  => "xx.yy.zz",                 
            'nbf'  => $issuedAt->getTimestamp(),    
            'exp'  => $expire,                      
            'data' => [                             
                'username' => "test",   
                'userId' => 0,
                'displayname' => "testD"
            ]
        ];

        $accessToken = JWT::encode($payload, $privateKey, 'RS256');
        
        $this->serverRequest->getHeaders()->willReturn(["authorization"=> ["Bearer ".$accessToken]]);

        $this->requestHandler->handle($this->serverRequest)->willReturn(new TextResponse("Default Text...", 200));

        /** @var TextResponse $result */
        $result = $this->loginInfoMiddleware->process($this->serverRequest->reveal(), $this->requestHandler->reveal());
        $this->assertEquals(401, $result->getStatusCode());
        $this->assertEquals("JWT is expired", $result->getBody()->getContents());   
    }

    public function testItWhetherCurrentTokenIsNotExpired() : void 
    {
        $privateKey = file_get_contents($this->config["jwtSecretPrivateKeyFile"]);
        $tokenId = base64_encode(random_bytes(16));
        $issuedAt   = new DateTimeImmutable();
        $expire = $issuedAt->modify(' +60 seconds')->getTimestamp();

        $payload = [
            'iat'  => $issuedAt->getTimestamp(),    
            'jti'  => $tokenId,                     
            'iss'  => "xx.yy.zz",                 
            'nbf'  => $issuedAt->getTimestamp(),    
            'exp'  => $expire,                      
            'data' => [                             
                'username' => "test",   
                'userId' => 0,
                'displayname' => "testD"
            ]
        ];

        $accessToken = JWT::encode($payload, $privateKey, 'RS256');

        $this->serverRequest->getHeaders()->willReturn(["authorization"=> ["Bearer ".$accessToken]]);
        $this->serverRequest->withAttribute("username", "test")->willReturn($this->serverRequest);
        $this->serverRequest->withAttribute("userId", 0)->willReturn($this->serverRequest);

        $this->requestHandler->handle($this->serverRequest)->willReturn(new TextResponse("TokenIsNotExpired OK"));

        /** @var TextResponse $result */
        $result = $this->loginInfoMiddleware->process($this->serverRequest->reveal(), $this->requestHandler->reveal());
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals("TokenIsNotExpired OK", $result->getBody()->getContents());   
    }
}
