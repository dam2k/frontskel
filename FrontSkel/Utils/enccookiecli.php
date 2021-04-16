#!/usr/bin/env php
<?php
// vim: expandtab:ts=4:sw=4

declare(strict_types=1);

namespace ReWeb;

use ReWeb\Utils\StringCrypt;
use \Firebase\JWT\JWT;

// autoload provided by composer
require __DIR__ . '/../../vendor/autoload.php';

$tokens['rt']='eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhdXRoIiwiYXVkIjoiUlQiLCJzdWIiOiIxIiwiaWF0IjoxNjE3Mzk1MzgxLCJuYmYiOjE2MTczOTUzNDgsImV4cCI6MTY0ODkzMTQxNCwianRpIjoiMThkNjA2NzdlYjViYmYzYyJ9.ouOwdUqPTw2xNIblPfdOUT_ZzoecaYFs_HXUSszPCNI';
$tokens['at']='eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhdXRoIiwiYXVkIjoiQVQiLCJzdWIiOiIxIiwiaWF0IjoxNjE3NDU2Mjc2LCJuYmYiOjE2MTc0NTYyNjksImV4cCI6MTYxNzQ1NjI5MywianRpIjoiMTg1NjA2ODZjOTRjNjZiMiJ9.3hsQmFjoRuL8oI1h8CKD8QRcXKnxKEssUn054NByQ40';
$tokens['ca']=array('path'=>'/', 'hostonly' => 1, 'secure' => 1, 'httponly' => 1, 'samesite' => 'Lax', 'expires' => 1627968089);

$conf=require(__DIR__ . '/../../etc/config.php');
$cipher="aes-128-gcm";
$hashalg="blake2b512";
$key=$conf['settings']['login_cookie']['key'];
$salt=$conf['settings']['login_cookie']['salt'];
echo "Clear text containing refresh and access tokens: ".json_encode($tokens)."\n";
echo "\n";

$jwtrt=$tokens['rt'];
$jwtat=$tokens['at'];
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

echo "\n";
$cookievalue=StringCrypt::encryptString(json_encode($tokens), $cipher, $hashalg, $key, $salt, true, true);
echo "Encrypted text containing refresh and access tokens: ".$cookievalue."\n";

