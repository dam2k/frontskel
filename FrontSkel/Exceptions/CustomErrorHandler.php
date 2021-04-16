<?php
// vim: expandtab:ts=4:sw=4

/**
 * FrontSkel: PHP slim based micro framework and WEB frontend
 * License: MIT
 * Author: Dino Ciuffetti <dam2000@gmail.com>
 */

declare(strict_types=1);

namespace FrontSkel\Exceptions;

use Slim\Handlers\ErrorHandler;
use Slim\Interfaces\CallableResolverInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;


class CustomErrorHandler extends ErrorHandler
{
    private ContainerInterface $c;
    private LoggerInterface $log;
    
    public function __construct(CallableResolverInterface $callableResolver, ResponseFactoryInterface $responseFactory, ContainerInterface $c) {
        $this->c = $c;
        $this->log = $c->get(LoggerInterface::class);
        parent::__construct($callableResolver, $responseFactory);
    }
    
    protected function logError(string $error): void {
        $etadd=""; $etaddpre="";
        $et=get_class($this->exception);
        if(substr($et, 0, strlen('Slim\Exception'))=='Slim\Exception') { // it's a slim exception
            $etadd=$this->exception->getTitle();
        } elseif($et=="Exception") {
            $etaddpre="Generic ";
            $etadd=$this->exception->getMessage();
        } else {
            $etadd=$this->exception->getMessage();
        }
        $this->log->error("$etaddpre$et (" . $etadd . " on ".$this->exception->getFile().":".$this->exception->getLine()."): " . $this->request->getMethod() . ' '. $this->request->getUri()->getPath());
    }

}
