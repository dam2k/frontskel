<?php
// vim: expandtab:ts=4:sw=4

/**
 * FrontSkel: PHP slim based micro framework and WEB frontend
 * License: MIT
 * Author: Dino Ciuffetti <dam2000@gmail.com>
 */

declare(strict_types=1);

namespace FrontSkel\Utils;

define("DEFAULT_COOKIE_OPTS", "['path' => '/', 'hostonly' => true, 'secure' => true, 'httponly' => true, 'samesite' => 'Lax']");

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Cookies;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use FrontSkel\Utils\StringCrypt;

Class EncryptedCookies
{
    private static ?Cookies $requestcookies;
    
    /**
     * Setup a normal cookie.
     * Cookie options that you can setup with the $opts are those definable by Slim\Psr7\Cookies::setDefaults
     */
    public static function prepareNormalCookie(
      string $cookiename,
      string $cookievalue,
      array $opts=DEFAULT_COOKIE_OPTS
    ): Cookies {
        $cookies = new Cookies();
        $cookies->setDefaults($opts);
        //$cookies->set($cookiename, ['value' => $cookievalue, 'samesite' => 'Strict']); // this way I can ovveride the samesite attribute for this cookie only
        $cookies->set($cookiename, ['value' => $cookievalue]);
        return $cookies;
    }
    
    /**
     * Setup a encrypted cookie that the client cannot really read in any mode (...depending by its ability to crack the given encryption algorithms)
     * Prepare and return a Slim\Psr7\Cookies, but encrypted and unreadable by the client.
     * Cookie options that you can setup with the $opts are those definable by Slim\Psr7\Cookies::setDefaults
     */
    public static function prepareEncryptedCookie(
      string $cookiename,   /* cookie name */
      string $cookievalue,  /* cookie value */
      string $key,          /* encryption key */
      string $salt,         /* scrambling salt */
      array $opts=DEFAULT_COOKIE_OPTS,
      string $cipher="aes-128-gcm", /* encryption cipher suite */
      string $hashalg="blake2b512" /* hashing algorithm */
    ): Cookies {
        $cookievalue=StringCrypt::encryptString($cookievalue, $cipher, $hashalg, $key, $salt, true, true);
        $cookies=self::prepareNormalCookie($cookiename, $cookievalue, $opts);
        return $cookies;
    }
    
    /**
     * Set a encrypted cookie. See prepareEncryptedCookie()
     */
    public static function setEncryptedCookie(
      Response &$response,   /* PSR-7 Response */
      string $cookiename,   /* cookie name */
      string $cookievalue,  /* cookie value */
      string $key,          /* encryption key */
      string $salt,         /* scrambling salt */
      array $opts=DEFAULT_COOKIE_OPTS,
      string $cipher="aes-128-gcm", /* encryption cipher suite */
      string $hashalg="blake2b512" /* hashing algorithm */
    ): Response {
        $cookies=self::prepareEncryptedCookie($cookiename, $cookievalue, $key, $salt, $opts, $cipher, $hashalg);
        $response=$response->withHeader('Set-Cookie', $cookies->toHeaders());
        unset($cookies);
        return $response;
    }
    
    /**
     * Unset the given cookie setting its expiring time to the past
     */
    public static function unsetEncryptedCookie(
      Response &$response,
      string $cookiename,
      array $opts=DEFAULT_COOKIE_OPTS
    ): Response {
        //$cookies=self::prepareNormalCookie($cookiename, '', ['value' => '', 'expires' => time()-3600]);
        
        $myCookieOpts=$opts;
        //$myCookieOpts['value']=''; // set cookie expiration
        //$myCookieOpts['expires']=time()-3600; // set cookie expiration
        $myCookieOpts['expires']=1; // set cookie expiration time to 1 second after unix epoq
        $opts=array_merge($myCookieOpts, $opts);
        
        $cookies=self::prepareNormalCookie($cookiename, '', $opts);
        $response=$response->withHeader('Set-Cookie', $cookies->toHeaders());
        unset($cookies);
        return $response;
    }
    
    /**
     * Get Cookies from the PSR-7 Request
     */
    public static function getRequestCookies(Request $request): Cookies {
        // get a cookie
        self::$requestcookies = new Cookies($request->getCookieParams());
        //echo "cookie value is: " . self::$requestcookies->get('MyCookieName');
        return self::$requestcookies;
    }
    
    /**
     * Get the given cookie's value from the PSR-7 Request
     */
    public static function getRequestCookieValue(Request $request, string $cookiename): string {
        if(!isset(self::$requestcookies)) self::getRequestCookies($request);
        $cookievalue=self::$requestcookies->get($cookiename);
        if(!$cookievalue) throw new \Exception("Requested cookie (\"$cookiename\") does not exist");
        return $cookievalue;
    }
    
    /**
     * Decrypt an encrypted cookie
     */
    public static function decryptCookie(
      Request $request,
      string $cookiename,
      string $key,          /* encryption key */
      string $salt,         /* scrambling salt */
      string $cipher="aes-128-gcm", /* encryption cipher suite */
      string $hashalg="blake2b512" /* key hashing algorithm */
    ): string {
        $cookievalue=self::getRequestCookieValue($request, $cookiename);
        $cookievalue=StringCrypt::decryptString($cookievalue, $cipher, $hashalg, $key, $salt, true, true);
        return $cookievalue;
    }
}
