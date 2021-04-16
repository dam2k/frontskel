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
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
//use Slim\Psr7\Cookies;

class AddDefaultHeaders extends GenericMiddleware implements MiddlewareInterface
{
    private ContainerInterface $c;
    //private LoggerInterface $log;
    
    public function __construct(ContainerInterface $c, LoggerInterface $log) {
        $this->c = $c;
        //$this->log = $log;
        parent::__construct($log);
    }
    
    public function process(Request $request, RequestHandler $handler): Response {
        // first execute other middlewares
        $response = $handler->handle($request);
        
        // send a encrypted cookie
        //EncryptedCookies::setEncryptedCookie($response, "myCookie", "disgrazieto", "chiave super sicura", "il sale fa male");
        //EncryptedCookies::unsetEncryptedCookie($response, "myCookie");
        
        // then add application headers
        $response = $response->withHeader("X-FrontSkel", "1");
        return $response;
    }
}
