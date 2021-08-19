#!/usr/bin/env php
<?php
// vim: expandtab:ts=4:sw=4

declare(strict_types=1);

namespace FrontSkel\Utils;

use \Firebase\JWT\JWT;

// autoload provided by composer
require __DIR__ . '/vendor/autoload.php';

$tokens['rt']='eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhdXRoIiwiYXVkIjoiUlQiLCJzdWIiOiIxIiwiaWF0IjoxNjI5MjQwNDU5LCJuYmYiOjE2MjkyNDA0MjYsImV4cCI6MTYzOTc1MjQ5MiwianRpIjoiMTFlNjExYzNjOGIwYjVkMyJ9.QPG3hQ6C3yFKVyH2OkX98agS9CCxJ8aiP6U8jxca8Yw';
$tokens['at']='eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhdXRoIiwiYXVkIjoiQVQiLCJzdWIiOiIxIiwiaWF0IjoxNjE3NDU2Mjc2LCJuYmYiOjE2MTc0NTYyNjksImV4cCI6MTYxNzQ1NjI5MywianRpIjoiMTg1NjA2ODZjOTRjNjZiMiJ9.3hsQmFjoRuL8oI1h8CKD8QRcXKnxKEssUn054NByQ40';
$tokens['ca']=array('path'=>'/', 'hostonly' => 1, 'secure' => 1, 'httponly' => 1, 'samesite' => 'Lax', 'expires' => 1627968089);

$conf=require(__DIR__ . '/etc/config.php');
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
//$cookievalue=StringCrypt::encryptString(json_encode($tokens), $cipher, $hashalg, $key, $salt, true, true);
$cookievalue=base64_encode(EncryptedCookies::prepareEncryptedCookie('_login', json_encode($tokens), $key, $salt)->toHeaders()[0]);

echo "Encrypted cookie containing refresh and access tokens: ".$cookievalue."\n";

