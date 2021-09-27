<?php
// vim: expandtab:ts=4:sw=4

/**
 * Author: Dino Ciuffetti <dam2000@gmail.com>
 * Date: 2020-10-13
 * Note: Middleware to check if POSTed user and password are correct
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
/*
use Slim\Routing\RouteContext;
use Slim\Psr7\Cookies;
use FrontSkel\Utils\StringCrypt;
*/


class CheckEmailPwd extends GenericMiddleware implements MiddlewareInterface
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
     * Get user input from http request, check credentials and set attributes accordingly that
     * can be read from the calling router or other middlewares. The added attributes are:
     * - FrontSkel\Middlewares\Auth\CheckEmailPwd:validCredentials : can be true or false. True if credentials are valid, False if not
     * - FrontSkel\Middlewares\Auth\CheckEmailPwd:uid : the user id of the logging user
     */
    public function process(Request $request, RequestHandler $handler): Response {
        $params=(array)$request->getParsedBody();
        $email=""; if(isset($params['inputEmail'])) $email=$params['inputEmail'];
        $pwd=""; if(isset($params['inputPassword'])) $pwd=$params['inputPassword']; // TODO: truncate pwd to 72 chars
        $rememberMe=""; if(isset($params['rememberMe'])) $rememberMe=$params['rememberMe'];
        
        $user=new User($this->c, $this->authdb);
        $userData=$user->checkEmailPwd($email, $pwd);
	$uid=$userData[0];
        unset($user);
        if($uid>0) {
            $this->log->info("Password IS valid for email \"$email\". UID: $uid, PID: $userData[1]");
            $request = $request->withAttribute(self::class.':validCredentials', true); // yes, user can login
            $request = $request->withAttribute(self::class.':uid', $uid); // the user ID
            $request = $request->withAttribute(self::class.':pid', $userData[1]); // the user user profile ID
        } else { // uid <= 0
            $request = $request->withAttribute(self::class.':validCredentials', false); // no, user cannot login
            switch($uid) {
            case 0:
                $this->log->warning("Wrong password ($pwd) for email \"$email\"");
                break;
            case -1:
                $this->log->warning("Wrong email \"$email\"");
                break;
            case -2:
                $this->log->warning("Email \"$email\" is not in ENABLED state");
                break;
            default:
                $this->log->error("Unknown error checking credentials for email \"$email\"");
            }
        }
        
        $response = $handler->handle($request); // continue to process next middlewares
        return $response;
    }
}
