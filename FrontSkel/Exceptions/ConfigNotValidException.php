<?php
// vim: expandtab:ts=4:sw=4

/**
 * FrontSkel: PHP slim based micro framework and WEB frontend
 * License: MIT
 * Author: Dino Ciuffetti <dam2000@gmail.com>
 */

declare(strict_types=1);

namespace FrontSkel\Exceptions;

class ConfigNotValidException extends \Exception
{
    protected $message = 'configuration file is corrupted or there is somethong not valid with it';   // exception message
    
    /*
    public function __construct($message = null, $code = 0, Exception $previous = null) {
    }
    */
}
