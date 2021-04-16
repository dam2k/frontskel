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
use Slim\Middleware\ContentLengthMiddleware;

class ContentLength extends GenericMiddleware implements MiddlewareInterface
{
    //private LoggerInterface $log;
    private ContentLengthMiddleware $clm;
    
    public function __construct(LoggerInterface $log) {
        //$this->log = $log;
        parent::__construct($log);
        $this->clm=new ContentLengthMiddleware();
    }
    
    public function process(Request $request, RequestHandler $handler): Response {
        $response = $this->clm->process($request, $handler);
        return $response;
    }
}
