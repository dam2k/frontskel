#!/usr/bin/env php
<?php
// vim: expandtab:ts=4:sw=4

/**
 * FrontSkel: PHP slim based micro framework and WEB frontend
 * License: MIT
 * Author: Dino Ciuffetti <dam2000@gmail.com>
 */

declare(strict_types=1);

namespace FrontSkel\Utils;

use \Firebase\JWT\JWT;

// autoload provided by composer
require __DIR__ . '/vendor/autoload.php';
$conf=require(__DIR__ . '/etc/config.php');

$cookievalue='BcG7spNAAABQK//DxhmHYnksG+KMOiFA7uW1EDYkpOMRIATCM8DyT/cX7Gxv79xCKz/AytJzXt99ePv7LH16eY2/3RURuKAcrh1Ek80ajDQhlEiksRZh2vcJVG4HlqPRhrkE/HhsloeuV0HegKrzYzE/Yo6r2QiTsfaeJW0hautWDwjr9Uxjl4HZmlVOqnEe/Zyx7XQnoOTWPOmP1KuLki4Badks64rN87k6GIIDV65oyHdz4wMvJT3mXE5qunSKlGDlYv7RnuQ8Pjj9TVPiLLJ60eABd5jQrexW1pZPGEbRdtOgnvgZH+ecyuK4lUE4p8Sby5YtOU8eALmA64reKEZz88CSKaA2uIICXfxeHGEuOR71qHCN4iAlaiNKUmtNG51bDr2MYAgLfwk4R/E0p2OWCxiHDlcpnGpKw2ywRswsGkMhjxNPEI5KsVtC3qnEMu11x4jzgU+OcYIbrR4FpYCqMWtbx+1DE64js7K7E4c7n4xsADIwzaZN0NNZ22/KcOElC94npSf1qdq2cWb6LS5VW9sDR6/C0GO/fH7v/iJff8Ifv//9eUMfv7/8Bw==';

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
