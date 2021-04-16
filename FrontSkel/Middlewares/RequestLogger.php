<?php
// vim: expandtab:ts=4:sw=4

/**
 * FrontSkel: PHP slim based micro framework and WEB frontend
 * License: MIT
 * Author: Dino Ciuffetti <dam2000@gmail.com>
 */

declare(strict_types=1);

namespace FrontSkel\Middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;

class RequestLogger extends GenericMiddleware implements MiddlewareInterface
{
    //private LoggerInterface $log;
    
    /**
     * Remove port from IPV4 address if it exist and leaves IPV6 addresses alone
     */
    private function extractIpAddress($ipAddress): string {
        $parts = explode(':', $ipAddress);
        if(count($parts) == 2) {
            if(filter_var($parts[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
                return $parts[0];
            }
        }
        
        return $ipAddress;
    }
    
    public function __construct(/*ContainerInterface $c, */LoggerInterface $log) {
        //$this->log = $log;
        parent::__construct($log);
    }
    
    public function process(Request $request, RequestHandler $handler): Response {
        $clientip=$this->extractIpAddress($request->getServerParams()['REMOTE_ADDR']);
        $request = $request->withAttribute(self::class . "-ClientIP", $clientip);
        $this->log->info("[$clientip] ".$request->getMethod().' '.$request->getUri()->getPath());
        
        $response = $handler->handle($request);
        return $response;
    }
}
