<?php
// vim: expandtab:ts=4:sw=4

/**
 * FrontSkel: PHP slim based micro framework and WEB frontend
 * License: MIT
 * Author: Dino Ciuffetti <dam2000@gmail.com>
 */

declare(strict_types=1);

namespace FrontSkel\Exceptions;

use Slim\Interfaces\ErrorRendererInterface;
use Smarty;

class CustomErrorRenderer implements ErrorRendererInterface
{
    private Smarty $smarty;
    
    public function __construct(Smarty $smarty) {
        $this->smarty=$smarty;
    }
    
    public function __invoke(\Throwable $exception, bool $displayErrorDetails): string
    {
        if(is_a($exception, 'Slim\Exception\HttpNotFoundException')) return $this->smarty->fetch('error404.tpl');
        $this->smarty->assign('exception', $exception);
        return $this->smarty->fetch('error.tpl');
    }
}
