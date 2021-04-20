<?php
// vim: expandtab:ts=4:sw=4

/**
 * FrontSkel: PHP slim based micro framework and WEB frontend
 * License: MIT
 * Author: Dino Ciuffetti <dam2000@gmail.com>
 */

declare(strict_types=1);

namespace FrontSkel\Users\Tokens;

use Psr\Container\ContainerInterface;
use Firebase\JWT\JWT;

class GenericToken
{
    protected ContainerInterface $c; // container
    protected string $jwt; // encoded JWT token
    
    public function __construct(ContainerInterface $c) {
        $this->c = $c;
    }
    
    /**
     * Encode a JWT Token - https://tools.ietf.org/html/rfc7519#section-4.1.2
     * $key: key
     * $proto: encryption protocol
     * $iss: Issuer
     * $aud: Audience
     * $iat: Issued At
     * $nbf: Not Before
     * $exp: Expiration Time
     * $other: other optional jwt claims
     */
    protected function encodeJWT(string $key, string $proto, string $iss, string $aud, string $sub, int $iat, int $nbf, int $exp, array $other=[]): string {
        $token = array_merge(array(
            "iss" => $iss,
            "aud" => $aud,
            "sub" => $sub,
            "iat" => $iat,
            "nbf" => $nbf,
            "exp" => $exp,
            "jti" => $sub.uniqid(bin2hex(random_bytes(1)), false),
        ), $other);
        $jwt = JWT::encode($token, $key, $proto);
        return $jwt;
    }
    
    /**
     * Decode a JWT Token
     */
    protected function decodeJWT(string $jwt, string $key, string $proto): \stdClass {
        return JWT::decode(
            $jwt,
            $key,
            (array)$proto
        );
    }
}
