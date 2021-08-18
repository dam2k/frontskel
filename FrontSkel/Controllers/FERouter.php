<?php
// vim: expandtab:ts=4:sw=4

/**
 * FrontSkel: PHP slim based micro framework and WEB frontend
 * License: MIT
 * Author: Dino Ciuffetti <dam2000@gmail.com>
 */

declare(strict_types=1);

namespace FrontSkel\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Smarty;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use FrontSkel\Middlewares\Auth\CheckEmailPwd;
use FrontSkel\Middlewares\Auth\CheckValidAuthToken;

class FERouter extends GenericRouter
{
    private string $favicon="AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAQAADkPAAA5DwAAAAAAAAAAAABVboP/WG9+/1ptd/9CTmb/UVx0/zlBYv8rNUn/Fh4p/xUdIP8hJCf/HyIj/x0eHf8bHBz/HR4e/x8hIf8fISH/cYeW/5uutP90hY7/Iyti/x0kbv8gKmv/QExm/yIqOf8TGh3/Jigo/zk2Nv86NjT/My8t/yUjIv8mJCL/Ozc0/3uDh/+4xMT/kJ2j/ysyav8YIXD/GSFs/yYwVv8xOUf/FRsf/zAxMf89ODf/NzMy/yMhIv8SExL/KSgl/0A7O/93eXj/tsHB/5imqv8tNWT/Ex1g/woTRv8cJkH/O0VV/yEnLv82MzL/PDY0/y4rLP8cGxz/HBwd/y4sLf8zMDH/f4GA/7S+wv+ruLz/aHeI/1hnff8rNkL/OUVX/zdGWv8wO0z/QUZR/01RWv87Q07/UFph/46Ym/+wvMD/laOn/4mLjP+4wsX/rLq+/7TDxv+qt7f/Jy85/0JMZf9NXG//RVJk/3eDj/9QWG7/Tlxv/z1NV/9vfIP/zt3h/7bJy/95fHv/doGF/296fv9ze3//SFBU/yIqPf83PH3/OkCR/0FKe/9jcHr/Nj5k/z1Cef9kb3z/kZyi/7rIzf+6y83/UFNW/yIqNv8XHyn/Dxch/xwlLf88Rkz/RVB0/yArcf9sfJj/uMrO/2JwjP8tN2b/tMDI/+36+v/f9fn/xNnc/21vcv81P0b/NT5D/2x4fP9PWmP/OEJL/1FbXv84Q0n/u8fJ/+n6/f/T5OX/U2Bn/1Zkbf+RoKX/4PL0/8vi4//EyMf/namr/7PBwv/x////e4yW/0VUaP+ZqbT/U2Nt/y49S/9XZW//jJmg/7XDyP9EVF3/DRwl/2RwdP+ot7f/iI+R/8vY2v/C0db/4PT8/5KkrP93hIv/go+T/8PS1v9fb3f/NUVR/11pcv/l9/n/1ejq/3aDif9GUVX/o66u/1FWYP/P3OH/wdLW/+L2+f+7z9X/zNzf/+X4/P/h9/r/3vP2/6C0uP/b7/H/3PHz/970+f/l+Pn/ztva/+Dy9f9iZnL/2+ju/87f4/+nuLn/3/P5/5qutv/Y6/T/2u71/+D1+/+lvMH/y+Hm/9zy9f/e8fX/xNbY/9Xo6//c8vf/YGFr/7nBxv/Z6+7/oLCz/+r///+qvsT/wtTb/+L2/f/c8/j/xNne/9fr8v/U6O3/qru8/9Pk5v/g9Pj/2+/z/3p2dv91eH3/0OLo/9Pq7f/b8ff/4fX7/9Tm7P/W7PD/1vDy/9Xs7//e9Pn/2Ozy/66/w//d8vT/3PL1/9vx9v9VV2D/TlZj/7LDy//l/P//2e/1/9nw9f/d8/b/1eru/9rv9P/c8fX/3PP1/9zz9v/f9Pb/2/P1/9v09f/b8/f/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA==";
    
    /**
     * Here, dependency injection magic occurs thanks to PHP-DI container. It's just as simple as defining what you want on the constructor
     */
    public function __construct(ContainerInterface $c, LoggerInterface $log) {
        parent::__construct($c, $log);
    }
    
    /**
     * GET /favicon.ico
     */
    public function GETFavicon(Request $request, Response $response): Response {
        $response=$response->withHeader('Content-Type', 'image/x-icon');
        $response->getBody()->write(base64_decode($this->favicon));
        return $response;
    }
    
    /**
     * GET /
     */
    public function GETSlash(Request $request, Response $response): Response {
	// CheckValidAuthToken mw get the login cookie from the request (if any), then retrieves and checks the tokens (if valid). It also updates the cookie.
        $uid=(string)$request->getAttribute(CheckValidAuthToken::class.':uid');
        if($uid) {
            $this->log->info("Valid uid in request route: $uid (user logged in)");
        } else {
            $this->log->info("NOT valid uid in request route (user not logged in). Redirecting to /login");
            return $response->withHeader('Location', $this->c->get('settings')['basePath'].'/login')->withStatus(302);
        }
        
        $this->requireSmarty();
        $this->smarty->assign('uid', $uid);
        $response->GETBody()->write($this->smarty->fetch('index.tpl'));
        return $response;
    }
    
    /**
     * Redirect the user to / if uid is valid
     */
    private function redirectUserToSlashIfValidUID(Response $response, $uid): Response {
        if($uid) {
            $this->log->info("Valid uid in request route: $uid (user logged in). Redirecting to /");
            return $response->withHeader('Location', $this->c->get('settings')['basePath'].'/')->withStatus(302);
        } else {
            $this->log->info("NOT valid uid in request route (user not logged in)");
        }
        return $response;
    }
    
    /**
     * GET /login
     */
    public function GETLogin(Request $request, Response $response): Response {
        $uid=(string)$request->getAttribute(CheckValidAuthToken::class.':uid');
        $response=$this->redirectUserToSlashIfValidUID($response, $uid); // id $uid is valid, redirect to /
        $this->requireSmarty();
        $this->smarty->assign('wronglogin', false);
        $response->getBody()->write($this->smarty->fetch('login.tpl'));
        return $response;
    }
    
    /**
     * POST /login
     */
    public function POSTLogin(Request $request, Response $response): Response {
        // Route middleware CheckEmailPwd returned attribute
        $this->requireSmarty();
	if(!$request->getAttribute(CheckEmailPwd::class.':validCredentials')) {
		$this->smarty->assign('wronglogin', true);
	} else {
		$this->smarty->assign('wronglogin', false);
	}
        
        $uid=(string)$request->getAttribute(CheckEmailPwd::class.':uid');
        $response=$this->redirectUserToSlashIfValidUID($response, $uid);
        $response->getBody()->write($this->smarty->fetch('login.tpl'));
        return $response;
    }
    
    /**
     * GET /logout
     */
    public function GETLogout(Request $request, Response $response): Response {
        //$this->smarty->assign('wronglogin', false);
        $this->requireSmarty();
        $response->getBody()->write($this->smarty->fetch('logout.tpl'));
        return $response;
    }
}
