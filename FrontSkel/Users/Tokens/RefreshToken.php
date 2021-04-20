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

class RefreshToken extends GenericToken
{
    private $TOKEN_AUD = "RT";
    
    /**
     * generate a new refresh token with the given (optional) subject or $other optional claims
     */
    public function generateRefreshToken(string $sub="", array $other=[]): void {
        $date=new \DateTime();
        $iss=$this->c->get('settings')['jwt_refresh_token']['iss'];
        // this method is defined into the GenericToken parent class
        $this->jwt=$this->encodeJWT(
            $this->c->get('settings')['jwt_refresh_token']['key'],
            $this->c->get('settings')['jwt_refresh_token']['algorithm'],
            // https://tools.ietf.org/html/rfc7519#section-4.1
            $iss,
            $this->TOKEN_AUD,
            $sub,
            $date->getTimestamp(),
            $date->getTimestamp()-$this->c->get('settings')['jwt_refresh_token']['time_skew'],
            $date->getTimestamp()+$this->c->get('settings')['jwt_refresh_token']['expire']+$this->c->get('settings')['jwt_refresh_token']['time_skew'],
            $other
        );
    }
    
    /**
     * load a previously encoded JWT refresh token
     */
    public function loadRefreshTokenFromJWT(string $jwt): void {
        // try to decode the JWT Token
        $rto=$this->decodeJWT(
            $jwt,
            $this->c->get('settings')['jwt_refresh_token']['key'],
            $this->c->get('settings')['jwt_refresh_token']['algorithm']
        );
        if((!$rto)||($rto->aud!=$this->TOKEN_AUD)) {
            throw new \Exception('Cannot load refresh token from JWT: token invalid or not a refresh token');
        }
        $this->jwt=$jwt; // save encoded JWT
    }
    
    private function throwExceptionIfRefreshTokenIsNull() {
        if(!$this->jwt) {
            throw new \Exception('Cannot get refresh token object. Please prepare the token first with generateRefreshToken() or loadRefreshTokenFromJWT()');
        }
    }
    
    /**
     * return refresh token's decoded JWT object
     */
    public function getToken(): \stdClass {
        $this->throwExceptionIfRefreshTokenIsNull();
        return $this->decodeJWT(
            $this->jwt,
            $this->c->get('settings')['jwt_refresh_token']['key'],
            $this->c->get('settings')['jwt_refresh_token']['algorithm']
        );
    }
    
    /**
     * return refresh token's encoded JWT string
     */
    public function getTokenJWT(): string {
        $this->throwExceptionIfRefreshTokenIsNull();
        return $this->jwt;
    }
    
    /**
     * return new access token. Access token expiration will be limited to refresh token's expiration or lower
     */
    public function getAccessToken(): AccessToken {
        $this->throwExceptionIfRefreshTokenIsNull();
        $ato = new AccessToken($this->c);
        $rto = $this->getToken();
        // print_r($rto);
        $rto_expiration=$rto->exp;
        $date=new \DateTime();
        $ato_expiration=$date->getTimestamp()+$ato->maxExpiration();
        if($rto_expiration < $ato_expiration) { // access token would expire after refresh token. Limiting access token life
            $ato_expiration=$rto_expiration;
        }
        $nt=array('iss' => 1, 'aud' => 1, 'sub' => 1, 'iat' => 1, 'nbf' => 1, 'exp' => 1, 'jti' => 1); // don't overwrite those properties
        $other=array_diff_key((array)$rto, $nt);
        $ato->generateAccessToken($rto->sub, $ato_expiration, $other);
        return $ato;
    }
}
