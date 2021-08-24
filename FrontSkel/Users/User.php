<?php
// vim: expandtab:ts=4:sw=4

/**
 * FrontSkel: PHP slim based micro framework and WEB frontend
 * License: MIT
 * Author: Dino Ciuffetti <dam2000@gmail.com>
 */

declare(strict_types=1);

namespace FrontSkel\Users;

const TBL_USERS = "users";
const TBL_TOKENS = "tokens";

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Doctrine\DBAL\Connection;
use FrontSkel\Users\Tokens\TokenHandler;
use FrontSkel\Users\Tokens\RefreshToken;
use FrontSkel\Users\Tokens\AccessToken;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use FrontSkel\Utils\EncryptedCookies;

Class User
{
    private ContainerInterface $c;
    private Connection $db;
    
    public function __construct(ContainerInterface $c, Connection $db) {
        $this->c=$c;
        $this->db=$db;
        $this->log=$this->c->get(LoggerInterface::class);
    }
    
    /**
     * Calculate hash from password
     */
    private function calculateHashFromPwd(string $pwd, int $cost=10): string {
        $pwlen=strlen($pwd);
        if($pwlen==0) throw new \Exception("pwd cannot be empty when calculating hash");
        if($pwlen>72) throw new \Exception("pwd length can be maximum 72 chars");
        $pwdhash=password_hash($pwd, PASSWORD_BCRYPT, ['cost'=>$cost]);
        if(!$pwdhash) throw new \Exception("error calculating hash from password");
        return $pwdhash;
    }
    
    /**
     * Check if hash and clear password do match
     */
    public function verifyHashedPwd(string $pwd, string $pwdhash): bool {
        return password_verify($pwd, $pwdhash);
    }
    
    /**
     * Check if email and password pair match.
     * Return -2 if users is not enabled, -1 if email does not exist, 0 if email/pwd are bad, user id (>0) if credentials are valid
     */
    public function checkEmailPwd(string $email, string $pwd): int {
        $sql = "SELECT id, pwdhash, state FROM ".TBL_USERS." WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue("email", $email);
        $result=$stmt->execute();
        $row=$result->fetchAssociative();
        if(!$row) return -1; // wrong email
        if(!$row['state']=="ENABLED") return -2;
        if(!$this->verifyHashedPwd($pwd, $row['pwdhash'])) return 0; // wrong password
        // map DB $row['id'] field value type to PHP integer scalar type
        return \Doctrine\DBAL\Types\Type::getType('integer')->convertToPhpValue($row['id'], $this->db->getDatabasePlatform());
    }
    
    /**
     * generate and save refresh token to DB
     */
    private function generateAndSaveRefreshToken(string $uid, int $rt_expire): RefreshToken {
        // generate refresh token using $uid as subject
        $tho = new TokenHandler($this->c); // type: TokenHandler
        $tho->generateRefreshToken($uid);
        $rto = $tho->getRefreshToken(); // type: RefreshToken
        $rtjwt = $rto->getTokenJWT();
        
        // save the refresh token inside re_auth.tokens table so that it can always be revoked
        try {
            $this->log->notice("Saving refresh token into DB (jti: ".$rto->getToken()->jti.")");
            $rc = $this->db->executeStatement('INSERT INTO '.TBL_TOKENS.' (id, users_id, jti, token, released, expiration, revoked, revocation_time) VALUES (NULL, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? SECOND), 0, NULL)', array($uid, $rto->getToken()->jti, $rtjwt, $rt_expire));
            if($rc!=1) {
                $this->log->warning("Cannot save refresh token into DB");
            }
        } catch (\Exception $e) {
            $this->log->warning("Cannot save refresh token into DB: ".$e->getMessage());
        }
        
        return $rto;
    }
    
    /**
     * set encrypted cookie used to login. Internally it uses a refresh and an access token to do the job
     * if $expire < 0 then use the configured refresh token expiration time for the login cookie
     * if expire = 0 set the cookie expiring on browser close
     * if expire > 0 set the cookie and the refresh token expiration time manually
     */
    public function setLoginCookie(Response &$response, string $uid, int $expire=-1, array $cookieOpts=[]): void {
        // return TokenHandler object
        $tho = new TokenHandler($this->c); // type: TokenHandler
        if($expire==0) { // cookie expires on browser close, RT expires based on config settings
            $cookie_expire=0;
            $rt_expire=$this->c->get('settings')['jwt_refresh_token']['expire']+$this->c->get('settings')['jwt_refresh_token']['time_skew'];
        } elseif($expire<0) { // both cookie and RT expire based on config settings
            $date=new \DateTime();
            $cookie_expire=$date->getTimestamp()+$this->c->get('settings')['jwt_refresh_token']['expire']+$this->c->get('settings')['jwt_refresh_token']['time_skew'];
            $rt_expire=$this->c->get('settings')['jwt_refresh_token']['expire']+$this->c->get('settings')['jwt_refresh_token']['time_skew'];
        } else { // manually set expiration validity in seconds for both cookie and RT
            $date=new \DateTime();
            $cookie_expire=$date->getTimestamp()+$expire+$this->c->get('settings')['jwt_refresh_token']['time_skew'];
            $rt_expire=$expire+$this->c->get('settings')['jwt_refresh_token']['time_skew'];
        }
        $myCookieOpts=$this->c->get('settings')['login_cookie']['cookieopts'];
        $myCookieOpts['expires']=$cookie_expire; // set cookie expiration
        $cookieOpts=array_merge($myCookieOpts, $cookieOpts);
        
        $rto=$this->generateAndSaveRefreshToken($uid, $rt_expire);
        $rtjwt = $rto->getTokenJWT();
        
        // generate access token from refresh token
        $ato = $rto->getAccessToken(); // type: AccessToken
        $atjwt = $ato->getTokenJWT();
        
        $this->log->debug("JWT Refresh token (jti: ".$rto->getToken()->jti."): $rtjwt");
        $this->log->debug("JWT Access token: $atjwt");
        $dt = new \DateTime("@$cookie_expire", new \DateTimeZone('UTC')); // convert UNIX timestamp to PHP DateTime
        $dt->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        $this->log->info("Login Cookie expiration: ".$dt->format('Y-m-d H:i:s'));
        // rt: refresh token; at: access token; ca: cookie attributes
        $tokens=array('rt'=>$rtjwt, 'at'=>$atjwt, 'ca'=>$cookieOpts);
        $cleartext=json_encode($tokens);
        EncryptedCookies::setEncryptedCookie($response, $this->c->get('settings')['login_cookie']['cookiename'], $cleartext, $this->c->get('settings')['login_cookie']['key'], $this->c->get('settings')['login_cookie']['salt'], $cookieOpts);
    }
    
    /**
     * update the login cookie
     */
    public function updateLoginCookie(Response &$response, string $uid, array $tokens, array $cookieOpts=[]): void {
        //$tokens=array('rt'=>$rtjwt, 'at'=>$atjwt, 'ca'=>$cookieOpts);
        $rtjwt=$tokens['rt'];
        $atjwt=$tokens['at'];
        $cookieOpts=array_merge($tokens['ca'], $cookieOpts);
        $this->log->debug("Updating login cookie");
        $this->log->debug("JWT Refresh token: $rtjwt");
        $this->log->debug("JWT Access token: $atjwt");
        $dt = new \DateTime("@".$cookieOpts['expires'], new \DateTimeZone('UTC')); // convert UNIX timestamp to PHP DateTime
        $dt->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        $this->log->info("Login Cookie expiration: ".$dt->format('Y-m-d H:i:s'));
        $cleartext=json_encode($tokens);
        EncryptedCookies::setEncryptedCookie($response, $this->c->get('settings')['login_cookie']['cookiename'], $cleartext, $this->c->get('settings')['login_cookie']['key'], $this->c->get('settings')['login_cookie']['salt'], $cookieOpts);
    }
    
    /**
     * get tokens from login cookie
     */
    private function extractTokensFromLoginCookie(Request $request, array &$tokens, string &$cleartext, string &$rtjwt, string &$atjwt, string &$cookieOpts): void {
        $cleartext=EncryptedCookies::decryptCookie($request, $this->c->get('settings')['login_cookie']['cookiename'], $this->c->get('settings')['login_cookie']['key'], $this->c->get('settings')['login_cookie']['salt']);
        $this->log->debug("Clear text cookie value containing refresh and access tokens: $cleartext");
        $tokens=json_decode($cleartext, true);
        $rtjwt=$tokens['rt'];
        $atjwt=$tokens['at'];
        $cookieOpts=$tokens['ca'];
    }
    
    /**
     * unset the login cookie and if $revokeRefreshToken is true, also revoke the refresh token
     */
    public function dropLoginCookie(Response &$response, bool $revokeRefreshToken=false, Request &$request=null): void {
        if($revokeRefreshToken) {
            $cleartext=""; $rtjwt=""; $atjwt=""; $cookieOpts=""; $tokens=[];
            try {
            $this->extractTokensFromLoginCookie($request, $tokens, $cleartext, $rtjwt, $atjwt, $cookieOpts);
            $tho = new TokenHandler($this->c); // type: TokenHandler
                $tho->loadRefreshTokenFromJWT($rtjwt);
                $rto=$tho->getRefreshToken();
                // revoke the refresh token
                try {
                    $this->log->info("Revoke valid refresh token in DB (jti: ".$rto->getToken()->jti.")");
                    $rc = $this->db->executeStatement('UPDATE '.TBL_TOKENS.' SET revoked=?, revocation_time=NOW() WHERE jti=?', array(1, $rto->getToken()->jti));
                    if($rc!=1) {
                        $this->log->warning("Cannot save refresh token revocation into DB (jti: ".$rto->getToken()->jti.")");
                    }
                } catch (\Exception $e) {
                    $this->log->warning("Cannot save refresh token revocation into DB (jti: ".$rto->getToken()->jti."): ".$e->getMessage());
                }
                $this->log->notice("Refresh token revoked (jti: ".$rto->getToken()->jti.")");
            } catch (\Exception $e) {
                $this->log->warning("Wanted to drop the refresh token but not a valid refresh token was found");
            }
        }
        $err="Dropping login cookie";
        $cookieOpts=$this->c->get('settings')['login_cookie']['cookieopts'];
        $this->log->debug($err);
        
        /*
        $myCookieOpts=$this->c->get('settings')['login_cookie']['cookieopts'];
        $myCookieOpts['expires']=$cookie_expire; // set cookie expiration
        $cookieOpts=array_merge($myCookieOpts, $cookieOpts);
        */
        EncryptedCookies::unsetEncryptedCookie($response, $this->c->get('settings')['login_cookie']['cookiename'], $cookieOpts);
    }
    
    /**
     * Check if login cookie is present in the request
     */
    public function isLoginCookiePresent(Request $request): bool {
        return EncryptedCookies::isCookiePresent($request, $this->c->get('settings')['login_cookie']['cookiename']);
    }

    /**
     * get encrypted cookie used to login. Internally it uses a refresh and an access token to do the job
     * $tokens is returned by address
     * $newcookie is setted to 1 if needs to be resubmitted.
     * return the access token object
     */
    public function getLoginCookie(Request $request, array &$tokens=[], int &$newcookie=0): \stdClass {
        $cleartext=""; $rtjwt=""; $atjwt=""; $cookieOpts="";
        $this->extractTokensFromLoginCookie($request, $tokens, $cleartext, $rtjwt, $atjwt, $cookieOpts);
        // return TokenHandler object
        $tho = new TokenHandler($this->c); // type: TokenHandler
        try {
            $tho->loadAccessTokenFromJWT($atjwt);
            $ato=$tho->getAccessToken();
            return $ato->getToken();
        } catch (\Firebase\JWT\ExpiredException $e) {
            $this->log->debug("JWT Access token has expired. Trying to use refresh token to get a new one");
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            $err="JWT Access token signature is invalid. Forged or altered token? Very strange, since tokens are encapsulated into an encrypted cookie!";
            $this->log->error($err);
            throw new \Exception($err);
        }
        
        // get access token from valid refresh token
        try {
            $tho->loadRefreshTokenFromJWT($rtjwt);
            $rto=$tho->getRefreshToken();
            
            $sql = "SELECT released, revocation_time FROM ".TBL_TOKENS." WHERE jti = :jti AND revoked=1";
            $stmt = $this->db->prepare($sql);
            //$stmt->bindValue("refreshtoken", $rtjwt);
            $stmt->bindValue("jti", $rto->getToken()->jti);
            $result=$stmt->execute();
            $row=$result->fetchAssociative();
            if($row) { // token revoked
                $this->log->info('This refresh token (jti: '.$rto->getToken()->jti.') was released on '.$row['released'].' but was REVOKED on '.$row['revocation_time']);
                throw new \Exception('Refresh token was REVOKED on '.$row['revocation_time']);
            } else {
                $dt = new \DateTime("@".$rto->getToken()->exp, new \DateTimeZone('UTC')); // convert UNIX timestamp to PHP DateTime
                $dt->setTimezone(new \DateTimeZone(date_default_timezone_get()));
                $dt2 = new \DateTime("@".$rto->getToken()->exp - $this->c->get('settings')['jwt_refresh_token']['auto_refresh'], new \DateTimeZone('UTC'));
                $dt2->setTimezone(new \DateTimeZone(date_default_timezone_get()));
                $err="JWT Refresh token (jti: ".$rto->getToken()->jti.") is valid. Expires at ".$dt->format('Y-m-d H:i:s').
                ($this->c->get('settings')['jwt_refresh_token']['auto_refresh']>0 ? ' if no activity is detected '.$this->c->get('settings')['jwt_refresh_token']['auto_refresh'].' seconds before expiration time (after '.$dt2->format('Y-m-d H:i:s').').' : '.');
                $this->log->debug($err);
            }
            
            // auto renew refresh token if we are near its expiration, there is activity and it's requested by jwt_refresh_token-->auto_refresh conf param
            $dt=new \DateTime();
            $dt2=new \DateTime("@".$rto->getToken()->exp);
            $seconds2expire = $dt2->getTimestamp() - $dt->getTimestamp();
            $auto_refresh = $this->c->get('settings')['jwt_refresh_token']['auto_refresh'];
            if(($auto_refresh > 0) && ($seconds2expire>0) && (($seconds2expire-$auto_refresh)<0)) {
                // generate a new refresh token
                $uid=$rto->getToken()->sub;
                $rt_expire=$this->c->get('settings')['jwt_refresh_token']['expire']+$this->c->get('settings')['jwt_refresh_token']['time_skew'];
                $dt2=new \DateTime("@".$dt->getTimestamp()+$rt_expire);
                $dt2->setTimezone(new \DateTimeZone(date_default_timezone_get()));
                $this->log->info("Refreshing the refresh token for uid $uid. Will expire at ".$dt2->format('Y-m-d H:i:s'));
                $rto=$this->generateAndSaveRefreshToken($uid, $rt_expire);
                $rtjwt = $rto->getTokenJWT();
                $tokens['rt']=$rtjwt;
                if($tokens['ca']['expires']>0) $tokens['ca']['expires']=$dt2->getTimestamp(); // update cookie expiration
            }
            
            // if RT has not been revoked, use RT to get a new access token
            $ato=$rto->getAccessToken();
            // NOTE: the login cookie must be updated with the new access token. The caller could call updateLoginCookie() to do this
            $newcookie=1; // tell the caller to update the cookie, since the access token inside it has changed
            $atjwt=$ato->getTokenJWT();
            $tokens['at']=$atjwt;
            return $ato->getToken();
        } catch (\Firebase\JWT\ExpiredException $e) {
            $err="JWT Refresh token has expired";
            $this->log->info($err);
            throw new \Exception($err);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            $err="JWT Refresh token signature is invalid. Forged or altered token? Very strange, since tokens are encapsulated into an encrypted cookie!";
            $this->log->error($err);
            throw new \Exception($err);
        }
    }
}
