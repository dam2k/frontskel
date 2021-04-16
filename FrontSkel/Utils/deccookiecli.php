#!/usr/bin/env php
<?php
// vim: expandtab:ts=4:sw=4

/**
 * FrontSkel: PHP slim based micro framework and WEB frontend
 * License: MIT
 * Author: Dino Ciuffetti <dam2000@gmail.com>
 */

declare(strict_types=1);

namespace FrontSkel;

use Utils\StringCrypt;
use \Firebase\JWT\JWT;

// autoload provided by composer
require __DIR__ . '/../../vendor/autoload.php';

$cookievalue='Dc09spNAAADg8QCWNo43oAh/IcSOsEAg7EKQn7ztCNkNIYGXBR5kKZ6dr3jjGaycsdXSXsfOk3gAO/0u8LlfPj/+evHq69Ob5ctL1i6imk7VQ76yVk5zm3eQMS9xIgSJH/vnRF3gvX2zjDOIUTI1t6YXhNUpPVKv8HjCTkxy1MCZ5l59GCNNYcvNAxVNS7nz9HDasvi0X4yzbbpxjNpSK3rPyRViWmqq4Wq5sib/WxWb8a1rK0DykBijorVmEvWZfi0d5SIQBy8PtiVXkVRcxUKKJha6KQwplEdZnCGxW/maSCnkhTzBU6DCrt1MJIzXAlaRHqA51mFzWSx9mzXqTHEjI0nqo1sGEhcMvt8UpkcOpWicikwFEiJlVAXdjN7jCMZtwIUxNIeGBF09Ep73O5RfzDkf6gO46qzPba5k5tytpxjTGgJhpKTnZW5oZN3d+LEezhkAmeMDb2EEK/m8tvVdywoWyqgAW20offMyMssdAkdvQYumIgPnFNpoeZTYO4pMENXJhhKQYjn05bsd5gKWoBTvaRXOjsUV5fagvn3/6Tf4+efb6+8f0r/PPz7Cfw==';


$conf=require(__DIR__ . '/../../etc/config.php');
$cipher="aes-128-gcm";
$hashalg="blake2b512";
$key=$conf['settings']['login_cookie']['key'];
$salt=$conf['settings']['login_cookie']['salt'];
$cleartext=StringCrypt::decryptString($cookievalue, $cipher, $hashalg, $key, $salt, true, true);
echo "Clear text containing refresh and access tokens: ".$cleartext."\n";

$tokens=json_decode($cleartext, true);
print_r($tokens);

$jwtrt=$tokens['rt'];  // refresh token
$jwtat=$tokens['at'];  // access token
$cookieOptions=$tokens['ca'];  // cookie attributes
try {
$rts = JWT::decode($jwtrt, $conf['settings']['jwt_refresh_token']['key'], (array)$conf['settings']['jwt_refresh_token']['algorithm']);
print_r($rts);
} catch (\Firebase\JWT\ExpiredException $e) {
    echo "Refresh token is expired\n";
}
try {
$ats = JWT::decode($jwtat, $conf['settings']['jwt_access_token']['key'], (array)$conf['settings']['jwt_access_token']['algorithm']);
print_r($ats);
} catch (\Firebase\JWT\ExpiredException $e) {
    echo "Access token is expired\n";
}
