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

class TokenHandler
{
    private ContainerInterface $c;
    private RefreshToken $rto;
    private AccessToken $ato;
    
    public function __construct(ContainerInterface $c) {
        $this->c = $c;
    }
    
    /**
     * generate new refresh token with the given (optional) subject or $other optional claims
     */
    public function generateRefreshToken(string $sub="", array $other=[]): void {
        $this->rto=new RefreshToken($this->c);
        $this->rto->generateRefreshToken($sub, $other);
    }
    
    /**
     * try to load the refresh token decoding the given JWT string
     */
    public function loadRefreshTokenFromJWT(string $jwt): void {
        $this->rto=new RefreshToken($this->c);
        $this->rto->loadRefreshTokenFromJWT($jwt);
    }
    
    /**
     * try to load the access token decoding the given JWT string
     */
    public function loadAccessTokenFromJWT(string $jwt): void {
        $this->ato=new AccessToken($this->c);
        $this->ato->loadAccessTokenFromJWT($jwt);
    }
    
    /**
     * return refresh token that is generated with generateRefreshTokens() or previously load by loadRefreshTokenFromJWT()
     */
    public function getRefreshToken(): RefreshToken {
        if(!$this->rto) {
            throw new \Exception('Cannot get refresh token object. Please prepare the token first with generateRefreshToken() or loadRefreshTokenFromJWT()');
        }
        return $this->rto;
    }
    
    /**
     * return access token that is previously load by loadAccessTokenFromJWT()
     */
    public function getAccessToken(): AccessToken {
        if(!$this->ato) {
            throw new \Exception('Cannot get access token object. Please prepare the token first with loadAccessTokenFromJWT()');
        }
        return $this->ato;
    }
}
