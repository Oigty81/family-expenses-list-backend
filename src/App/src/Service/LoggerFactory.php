<?php
namespace App\Service;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;

class LoggerFactory
{
    public function __invoke(ContainerInterface $container) : LoggerInterface
    {
        $logger = new Logger("APP:");
        $logger->pushHandler(new StreamHandler(
            'php://stdout',
            Logger::INFO,
            $bubble = true,
            $expandNewLines = true
        ));
        $logger->pushProcessor(new PsrLogMessageProcessor());
        return $logger;
    }
}