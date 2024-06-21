<?php

declare(strict_types=1);

namespace App\Service;

use Throwable;
use DateTimeImmutable;
use Laminas\Db\Adapter\Adapter;
use Psr\Log\LoggerInterface;
use Firebase\JWT\JWT;

class UserDataService
{
    /**
     * @var array
     */
    private $config;
    /**
     * @var Adapter
     */
    private $db;
        /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        array $config,
        Adapter $db,
        LoggerInterface $logger
    )
    {
        $this->config = $config;
        $this->db = $db;
        $this->logger = $logger;
    }

    public function authFlow($username, $password, $longExpireTime)
    {
        $userQuery = <<<SQLQUERY
        SELECT id, password, displayname FROM users WHERE username = ?;
        SQLQUERY;

        try {
            $userResult = $this->db->query($userQuery, [ $username ])->toArray();

            if(count($userResult) != 1) {
                return [ "error" => -1, "errormsg" => "user: ".$username." not found!" ];
            }

            $passwordHash = $userResult[0]["password"];
            $passwordCheck = password_verify($password, $passwordHash);

            if($passwordCheck) {

                $privateKey = file_get_contents($this->config["jwtSecretPrivateKeyFile"]);
                $tokenId = base64_encode(random_bytes(16));
                $issuedAt   = new DateTimeImmutable();

                if($longExpireTime) {
                    $expire = $issuedAt->modify('+'.$this->config["longExpireTimeAccessToken"].' seconds')->getTimestamp();
                } else {
                    $expire = $issuedAt->modify('+'.$this->config["shortExpireTimeAccessToken"].' seconds')->getTimestamp(); 
                }
                
                $serverName = $this->config["serverName"];
               
                $payload = [
                    'iat'  => $issuedAt->getTimestamp(),
                    'jti'  => $tokenId,              
                    'iss'  => $serverName,           
                    'nbf'  => $issuedAt->getTimestamp(),
                    'exp'  => $expire,                      
                    'data' => [
                        'username' => $username,   
                        'userId' => $userResult[0]["id"],
                        'displayname' => $userResult[0]["displayname"]
                    ]
                ];

                $accessToken = JWT::encode($payload, $privateKey, 'RS256');

                $this->db->query("UPDATE users SET lastLogin = now() WHERE id = ?;", [ $userResult[0]["id"] ]);

            } else {
                return [ "error" => -2, "errormsg" => "wrong password!" ];
            }
            
            return [ "token" => $accessToken];

        } catch (Throwable $e) {
            return [ "error" => -99, "errormsg" => "error in user__authFlow.. ".$e->getMessage() ];
        }     
    }
}