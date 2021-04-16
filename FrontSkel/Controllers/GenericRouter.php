<?php
// vim: expandtab:ts=4:sw=4

/**
 * FrontSkel: PHP slim based micro framework and WEB frontend
 * License: MIT
 * Author: Dino Ciuffetti <dam2000@gmail.com>
 */

declare(strict_types=1);

namespace FrontSkel\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Smarty;

class GenericRouter
{
    protected ContainerInterface $c;
    protected LoggerInterface $log;
    protected Smarty $smarty;
    
    public function __construct(ContainerInterface $c, LoggerInterface $log) {
        $this->c=$c;
        $this->log=$log;
        $this->log->debug("RT: ".get_called_class());
    }
    
    /**
     * Any route can use this one to get Smarty instance
     */
    protected function requireSmarty(): Smarty {
        $this->smarty=$this->c->get(Smarty::class);
        return $this->smarty;
    }
    
    /**
     * Redirect the user to another URL
     */
    protected function redirectUser(Response &$response, string $uri, int $internalUri=1, int $redirectCode=302): Response {
        if($internalUri) {
            $uri=$this->c->get('settings')['basePath'].$uri;
        }
        $this->log->info("Redirecting to " . $uri);
        return $response->withHeader('Location', $uri)->withStatus($redirectCode);
    }
    
    /**
     * Render a smarty template
     */
    protected function smartyRender(Response &$response, string $template): void {
        $this->log->debug('Rendering smarty template "'.$this->c->get('settings')['smarty']['TemplateName'].'/'.$template.'"');
        if($this->smarty==null) $this->requireSmarty();
        $response->GETBody()->write($this->smarty->fetch($template));
    }
}
