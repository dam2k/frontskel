<?php
// vim: expandtab:ts=4:sw=4

/**
 * Author: Dino Ciuffetti <dam2000@gmail.com>
 * Date: 2020-10-22
 * Note: This middleware checks if a valid auth token is present into the auth cookie (also checks if the cookie is present).
 *       The request attribute CheckValidAuthToken::class:uid is setted to reflect the user's UID (if any) or empty instead.
 */

declare(strict_types=1);

namespace FrontSkel\Middlewares\Auth;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Doctrine\DBAL\Connection;
use FrontSkel\Users\User;
use FrontSkel\Middlewares\GenericMiddleware;

class CheckValidAuthToken extends GenericMiddleware implements MiddlewareInterface
{
    private ContainerInterface $c;
    private Connection $authdb;
    
    public function __construct(ContainerInterface $c, LoggerInterface $log) {
        $this->c = $c;
        $this->authdb = $this->c->get('AuthDbConnection'); // cannot autowire AuthDbConnection on the constructor. Getting here from the DI
        parent::__construct($log);
    }
    
    /**
     * check auth tokens inside auth cookie (if any)
     */
    public function process(Request $request, RequestHandler $handler): Response {
        $this->log->debug("Checking Login Auth Cookie");
        $user=new User($this->c, $this->authdb);
        try {
            $newcookie=0; $tokens=[];
            $at=$user->getLoginCookie($request, $tokens, $newcookie);
            //print_r($at); --> stdClass Object ( [iss] => auth server [aud] => access [sub] => 1 [iat] => 1603405637 [nbf] => 1603405604 [exp] => 1603405970 ) 
            $uid=$at->sub;
	    $pid=$at->pid;
            $this->log->info("Login cookie and auth tokens valid. uid:$uid, pid:$pid");
            $request = $request->withAttribute(self::class.':uid', $uid); // uid
            $request = $request->withAttribute(self::class.':pid', $pid); // pid
            if($newcookie) { // $newcookie is passed by address by getLoginCookie, and it's updated if the access token inside the cookie has changed
                $response = $handler->handle($request); // continue to process next middlewares
                $this->log->debug("Since the access token has changed, we need to release the updated cookie");
                //$cookieOpts=$this->c->get('settings')['login_cookie']['cookieopts'];
                $cookieOpts=[];
                $user->updateLoginCookie($response, $uid, $tokens, $cookieOpts);
                return $response;
            }
        } catch (\Exception $e) {
            $this->log->debug("Login cookie is not present, expired or not valid: ".$e->getMessage());
            $request = $request->withAttribute(self::class.':uid', ''); // no uid
            $response = $handler->handle($request); // continue to process next middlewares
            $user->dropLoginCookie($response);
            return $response;
        }
        $response = $handler->handle($request); // continue to process next middlewares
        return $response;
    }
}
