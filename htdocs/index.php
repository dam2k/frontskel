<?php
// vim: expandtab:ts=4:sw=4

/**
 * FrontSkel: PHP slim based micro framework and WEB frontend
 * License: MIT
 * Author: Dino Ciuffetti <dam2000@gmail.com>
 */

declare(strict_types=1);

namespace FrontSkel;

// autoload provided by composer
require __DIR__ . '/../vendor/autoload.php';

$app=new App(__DIR__ .'/../etc/config.php');
