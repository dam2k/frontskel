<?php
// vim: expandtab:ts=4:sw=4

/**
 * Author: Dino Ciuffetti <dam2000@gmail.com>
 * Date: 2020-10-16
 * Note: This middleware releases an auth token if a previous middleware declared a valid login
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
use FrontSkel\Middlewares\Auth\CheckEmailPwd;
use FrontSkel\Middlewares\GenericMiddleware;
use FrontSkel\User\Token\RefreshToken;


class ReleaseAuthTokens extends GenericMiddleware implements MiddlewareInterface
{
    private ContainerInterface $c;
    //private LoggerInterface $log;
    private Connection $authdb;
    
    public function __construct(ContainerInterface $c, LoggerInterface $log) {
        $this->c = $c;
        //$this->log = $log;
        $this->authdb = $this->c->get('AuthDbConnection'); // cannot autowire AuthDbConnection on the constructor. Getting here from the DI
        parent::__construct($log);
    }
    
    /**
     * release authentication token. Validity is longer if rememberMe input was selected by the user
     */
    public function process(Request $request, RequestHandler $handler): Response {
        if(!$request->getAttribute(CheckEmailPwd::class.':validCredentials')) { // invalid login from the previous middleware
            $this->log->info("Cannot release token because ".CheckEmailPwd::class." mw returned invalid credentials");
            $response = $handler->handle($request); // continue to process next middlewares
            return $response;
        }
        $params=(array)$request->getParsedBody();
        $rememberMe=""; if(isset($params['rememberMe'])) $rememberMe=$params['rememberMe']; // value is "1" only if selected by the user
        $uid=(string)$request->getAttribute(CheckEmailPwd::class.':uid');
        $pid=(string)$request->getAttribute(CheckEmailPwd::class.':pid');
        $request = $request->withAttribute(self::class.':uid', $uid);
        $this->log->info("Releasing login cookie for uid:$uid, pid:$pid, rememberme:".($rememberMe ? 1 : 0));
        //$cookieOpts=$this->c->get('settings')['login_cookie']['cookieopts'];
        $cookieOpts=[];
        if($rememberMe) { // let login cookie expiring with refresh token
            $expire=-1;
        } else { // expire cookie on browser close
            $expire=0;
        }
        
        $response = $handler->handle($request); // continue to process next middlewares
        $user=new User($this->c, $this->authdb);
        $user->setLoginCookie($response, $uid, $pid, $expire, $cookieOpts);
        return $response;
    }
}
