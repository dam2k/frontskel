<?php
// vim: expandtab:ts=4:sw=4

declare(strict_types=1);

namespace FrontSkel\Middlewares\Auth;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Doctrine\DBAL\Connection;
use FrontSkel\User\User;
use FrontSkel\Middlewares\GenericMiddleware;
//use FrontSkel\Utils\EncryptedCookies;
/*
use Slim\Routing\RouteContext;
use Slim\Psr7\Cookies;
use FrontSkel\Utils\StringCrypt;
*/

class DropAuthToken extends GenericMiddleware implements MiddlewareInterface
{
    private ContainerInterface $c;
    private Connection $authdb;
    
    public function __construct(ContainerInterface $c, LoggerInterface $log) {
        $this->c = $c;
        $this->authdb = $this->c->get('AuthDbConnection'); // cannot autowire AuthDbConnection on the constructor. Getting here from the DI
        parent::__construct($log);
    }
    
    public function process(Request $request, RequestHandler $handler): Response {
        /*
        $params=(array)$request->getParsedBody();
        $myParam=""; if(isset($params['myParam'])) $myParam=$params['myParam'];
        */
        /*
        try {
            $this->log->info(self::class . ": ".EncryptedCookies::decryptCookie($request, 'myCookie', "chiave super sicura", "il sale fa male"));
        } catch (\Exception $e) {
            $this->log->warning(self::class . ": cannot decrypt cookie: " . $e->getMessage());
        }
        */
        $user=new User($this->c, $this->authdb);
        $response = $handler->handle($request);
        $this->log->info("Drop login cookie and revoke refresh token to log the user out");
        $user->dropLoginCookie($response, true, $request);
        
        return $response;
    }
}
