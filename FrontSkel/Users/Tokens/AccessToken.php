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

class AccessToken extends GenericToken
{
    private $TOKEN_AUD = "AT";
    
    public function maxExpiration(): int {
        return $this->c->get('settings')['jwt_access_token']['expire']+$this->c->get('settings')['jwt_access_token']['time_skew'];
    }
    
    /**
     * generate a new access token with the given (optional) subject, expiration or $other optional claims
     */
    public function generateAccessToken(string $sub="", int $expiration=0, array $other=[]): void {
        $date=new \DateTime();
        if($expiration==0) $expiration=$date->getTimestamp()+$this->c->get('settings')['jwt_access_token']['expire']+$this->c->get('settings')['jwt_access_token']['time_skew'];
        $iss=$this->c->get('settings')['jwt_access_token']['iss'];
        // this method is defined into the GenericToken parent class
        $this->jwt=$this->encodeJWT(
            $this->c->get('settings')['jwt_access_token']['key'],
            $this->c->get('settings')['jwt_access_token']['algorithm'],
            // https://tools.ietf.org/html/rfc7519#section-4.1
            $iss,
            $this->TOKEN_AUD,
            $sub,
            $date->getTimestamp(),
            $date->getTimestamp()-$this->c->get('settings')['jwt_access_token']['time_skew'],
            $expiration,
            $other
        );
    }
    
    /**
     * load a previously encoded JWT access token
     */
    public function loadAccessTokenFromJWT(string $jwt): void {
        // try to decode the JWT Token
        $ato=$this->decodeJWT(
            $jwt,
            $this->c->get('settings')['jwt_access_token']['key'],
            $this->c->get('settings')['jwt_access_token']['algorithm']
        );
        if((!$ato)||($ato->aud!=$this->TOKEN_AUD)) {
            throw new \Exception('Cannot load access token from JWT: token invalid or not a access token');
        }
        $this->jwt=$jwt; // save encoded JWT
    }
    
    /**
     * return access token's decoded JWT object
     */
    public function getToken(): \stdClass {
        if(!$this->jwt) {
            throw new \Exception('Cannot get access token object. Please prepare the token first with generateAccessToken() or loadTokenFromJWT()');
        }
        return $this->decodeJWT(
            $this->jwt,
            $this->c->get('settings')['jwt_access_token']['key'],
            $this->c->get('settings')['jwt_access_token']['algorithm']
        );
    }
    
    /**
     * return access token's encoded JWT string
     */
    public function getTokenJWT(): string {
        if(!$this->jwt) {
            throw new \Exception('Cannot get access token object. Please prepare the token first with generateAccessToken() or loadTokenFromJWT()');
        }
        return $this->jwt;
    }
}
