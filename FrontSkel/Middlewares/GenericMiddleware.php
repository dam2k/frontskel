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
//use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;

class GenericMiddleware
{
    protected LoggerInterface $log;
    
    public function __construct(LoggerInterface $log) {
        $this->log=$log;
        //$this->log->debug("MW: ".get_called_class());
    }
    
    public function __invoke(Request $request, RequestHandler $handler) : Response {
        $this->log->debug("MW: ".get_called_class()." BEGIN");
        $response=$this->process($request, $handler);
        $this->log->debug("MW: ".get_called_class()." END");
        return $response;
    }
}
