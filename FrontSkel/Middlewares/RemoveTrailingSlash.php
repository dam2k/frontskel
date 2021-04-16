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

class RemoveTrailingSlash extends GenericMiddleware implements MiddlewareInterface
{
    //private LoggerInterface $log;
    private ContainerInterface $c;
    
    public function __construct(ContainerInterface $c, LoggerInterface $log) {
        $this->c = $c;
        //$this->log = $log;
        parent::__construct($log);
    }
    
    public function process(Request $request, RequestHandler $handler): Response {
        $uri = $request->getUri();
        $path = $uri->getPath();
        $olduri = $uri->withPath($path);
        
        if ($path != $this->c->get('settings')['basePath'].'/' && substr($path, -1) == '/') {
            // recursively remove slashes when its more than 1 slash
            $path = rtrim($path, '/');
            
            // redirect paths with a trailing slash
            // to their non-trailing counterpart
            $uri = $uri->withPath($path);
            
            if ($request->getMethod() == 'GET') {
                $this->log->info("Redirecting without trailing /: " . $olduri . " => $uri");
                $responseFactory = new \Slim\Psr7\Factory\ResponseFactory;
                $response = $responseFactory->createResponse();
                return $response
                    ->withHeader('Location', (string) $uri)
                    ->withStatus(301);
            } else {
                $request = $request->withUri($uri);
            }
        }
        
        return $handler->handle($request);
    }
}
