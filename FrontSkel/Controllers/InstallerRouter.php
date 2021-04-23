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
use Slim\Psr7\Cookies;
use FrontSkel\Utils\EncryptedCookies;

class InstallerRouter extends GenericRouter
{
    // favicon.ico icon base64 bytestream
    private string $favicon="AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAQAADkPAAA5DwAAAAAAAAAAAABVboP/WG9+/1ptd/9CTmb/UVx0/zlBYv8rNUn/Fh4p/xUdIP8hJCf/HyIj/x0eHf8bHBz/HR4e/x8hIf8fISH/cYeW/5uutP90hY7/Iyti/x0kbv8gKmv/QExm/yIqOf8TGh3/Jigo/zk2Nv86NjT/My8t/yUjIv8mJCL/Ozc0/3uDh/+4xMT/kJ2j/ysyav8YIXD/GSFs/yYwVv8xOUf/FRsf/zAxMf89ODf/NzMy/yMhIv8SExL/KSgl/0A7O/93eXj/tsHB/5imqv8tNWT/Ex1g/woTRv8cJkH/O0VV/yEnLv82MzL/PDY0/y4rLP8cGxz/HBwd/y4sLf8zMDH/f4GA/7S+wv+ruLz/aHeI/1hnff8rNkL/OUVX/zdGWv8wO0z/QUZR/01RWv87Q07/UFph/46Ym/+wvMD/laOn/4mLjP+4wsX/rLq+/7TDxv+qt7f/Jy85/0JMZf9NXG//RVJk/3eDj/9QWG7/Tlxv/z1NV/9vfIP/zt3h/7bJy/95fHv/doGF/296fv9ze3//SFBU/yIqPf83PH3/OkCR/0FKe/9jcHr/Nj5k/z1Cef9kb3z/kZyi/7rIzf+6y83/UFNW/yIqNv8XHyn/Dxch/xwlLf88Rkz/RVB0/yArcf9sfJj/uMrO/2JwjP8tN2b/tMDI/+36+v/f9fn/xNnc/21vcv81P0b/NT5D/2x4fP9PWmP/OEJL/1FbXv84Q0n/u8fJ/+n6/f/T5OX/U2Bn/1Zkbf+RoKX/4PL0/8vi4//EyMf/namr/7PBwv/x////e4yW/0VUaP+ZqbT/U2Nt/y49S/9XZW//jJmg/7XDyP9EVF3/DRwl/2RwdP+ot7f/iI+R/8vY2v/C0db/4PT8/5KkrP93hIv/go+T/8PS1v9fb3f/NUVR/11pcv/l9/n/1ejq/3aDif9GUVX/o66u/1FWYP/P3OH/wdLW/+L2+f+7z9X/zNzf/+X4/P/h9/r/3vP2/6C0uP/b7/H/3PHz/970+f/l+Pn/ztva/+Dy9f9iZnL/2+ju/87f4/+nuLn/3/P5/5qutv/Y6/T/2u71/+D1+/+lvMH/y+Hm/9zy9f/e8fX/xNbY/9Xo6//c8vf/YGFr/7nBxv/Z6+7/oLCz/+r///+qvsT/wtTb/+L2/f/c8/j/xNne/9fr8v/U6O3/qru8/9Pk5v/g9Pj/2+/z/3p2dv91eH3/0OLo/9Pq7f/b8ff/4fX7/9Tm7P/W7PD/1vDy/9Xs7//e9Pn/2Ozy/66/w//d8vT/3PL1/9vx9v9VV2D/TlZj/7LDy//l/P//2e/1/9nw9f/d8/b/1eru/9rv9P/c8fX/3PP1/9zz9v/f9Pb/2/P1/9v09f/b8/f/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA==";
    // generic passwords, minimum length
    private int $minpwd=6;
    // generic passwords, maximum length
    private int $maxpwd=32;
    
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
        return $this->redirectUser($response, "/install/0");
    }
    
    /**
     * validate form input
     */
    private function validateForm(string $step, array $params, \Sirius\Validation\Validator $validator): array {
        if(!$validator->validate($params)) { // one or more patameters failed validation
            $ret=$validator->getMessages();
            $this->log->info("Form validation errors for step \"$step\". Bad fields: ".implode(",", array_keys($ret)));
            return $ret;
        } // else
        $this->log->info("Form validation OK for step \"$step\"");
        return [];
    }
    
    /**
     * try to validate DB connection
     */
    private function validateDb(string $step, array $params): array {
        /*
        $validator->add('adminInputDBUser', 'required | length(1,16)'); // DB user
        $validator->add('adminInputDBPwd', 'length(0,'.$this->maxpwd.')'); // DB password
        $validator->add('adminInputDBHost', 'length(0,64)'); // DB host
        $validator->add('adminInputDBPort', 'Between(0,65535)'); // DB port
        $validator->add('adminInputDBName', 'required | length(1,64)'); // DB name
        $validator->add('adminInputDBSocket', 'length(0,4096)'); // UX Socket
        $validator->add('adminInputDBCharset', 'length(0,64)'); // Default DB charset
        $validator->add('adminInputDBDriver', 'length(0,16)'); // DB Driver
        
        'user' => $params[],
        'password' => '',
        //'host' => '127.0.0.1',
        //'port' => 3306,
        'dbname' => 're',
        'unix_socket' => '/var/run/mysqld/mysqld.sock',
        'charset' => 'UTF8',
        'driver' => 'pdo_mysql',
        'driverOptions' => [],
        */
        
        // https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#configuration
        $dbparams=[
            'user'=>$params['adminInputDBUser'],
            'password'=>$params['adminInputDBPwd'],
            'host'=>$params['adminInputDBHost'],
            'port'=>(int)$params['adminInputDBPort'],
            'dbname'=>$params['adminInputDBName'],
            'path'=>$params['adminInputDBName'],
            'unix_socket'=>$params['adminInputDBSocket'],
            'charset'=>$params['adminInputDBCharset'],
            'driver'=>$params['adminInputDBDriver'],
            /*
            'driverOptions'=>$params['adminInputDBDriver'],
            'platform'=>$params['adminInputDBPlatform'],
            */
        ];
        /*
        // https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/platforms.html#platforms
        if(isset($settings['platform'])&&($settings['platform']!="")) { // override doctrine DB autoscouting
            $myPlatform = new $settings['platform']();
            $settings['platform']=$myPlatform;
        } else {
            unset($settings['platform']);
        }
        */
        
        $db = \Doctrine\DBAL\DriverManager::getConnection($dbparams);
        $sql = "SELECT 1";
        //$stmt->bindValue("email", $param);
        try {
            $stmt = $db->prepare($sql);
            $result=$stmt->execute();
        } catch(\Exception $e) {
            $this->log->info("DB validation NOK for step \"$step\": ".$e->getMessage());
            $err=['adminInputDBName'];
            $err['adminInputDBName']=$e->getMessage();
            return $err;
        }
        
        $this->log->info("DB validation OK for step \"$step\"");
        return [];
    }
    
    /**
     * Check if input parameters are valid for the given step.
     * Return an empty array if everything is OK, or the list of problematic elements in case of validation errors
     */
    private function checkFormParams(string $step, array $params): array {
        // https://www.sirius.ro/php/sirius/validation/validation_helper.html
        $ret=[];
        $validator = new \Sirius\Validation\Validator;
        switch($step) {
        case 0: // welcome
            // welcome stage does not expect parameters
            return $ret;
            break;
        case 1: // fronted admin user details
            $validator->add('adminInputEmail', 'required | email | length(3,320)'); // admin email address
            $validator->add('adminInputPassword', 'required | length('.$this->minpwd.','.$this->maxpwd.')'); // admin password
            $ret=$this->validateForm($step, $params, $validator);
            return $ret;
            break;
        case 2: // fronted DB details
            $validator->add('adminInputDBUser', 'required | length(1,16)'); // DB user
            $validator->add('adminInputDBPwd', 'length(0,'.$this->maxpwd.')'); // DB password
            $validator->add('adminInputDBHost', 'length(0,64)'); // DB host
            $validator->add('adminInputDBPort', 'Between(0,65535)'); // DB port
            $validator->add('adminInputDBName', 'required | length(1,64)'); // DB name
            $validator->add('adminInputDBSocket', 'length(0,4096)'); // UX Socket
            $validator->add('adminInputDBCharset', 'length(0,64)'); // Default DB charset
            $validator->add('adminInputDBDriver', 'length(0,16)'); // DB Driver
            $ret=$this->validateForm($step, $params, $validator);
            if(count($ret)) return $ret; // some error on form validation
            $ret=$this->validateDb($step, $params);
            return $ret;
            break;
        case 3: // tokens
            // REFRESH TOKEN
            $validator->add('adminInputRTKey', 'required | length(64,64) | regex(/^[a-f0-9]+$/i)'); // Refresh token key
            $validator->add('adminInputRTTimeSkew', 'required | Integer | GreaterThan(min=0&inclusive=true)'); // Refresh token time skew
            $validator->add('adminInputRTExpiration', 'required | Integer | GreaterThan(min=0&inclusive=false)'); // Refresh token expiration time
            $validator->add('adminInputRTAutoRefresh', 'required | Integer | GreaterThan(min=0&inclusive=true)'); // Refresh token autorefresh
            // ACCESS TOKEN
            $validator->add('adminInputATKey', 'required | length(64,64) | regex(/^[a-f0-9]+$/i)'); // Access token key
            $validator->add('adminInputATTimeSkew', 'required | Integer | GreaterThan(min=0&inclusive=true)'); // Access token time skew
            $validator->add('adminInputATExpiration', 'required | Integer | GreaterThan(min=0&inclusive=false)'); // Access token expiration time
            $ret=$this->validateForm($step, $params, $validator);
            return $ret;
            break;
        default:
            throw new \Exception("step \"$step\" not implemented");
            break;
        }
    }
    
    /**
     * GET /install/[{step}]
     */
    public function GETSlashInstall(Request $request, Response $response, array $args): Response {
        $this->requireSmarty();
        $step=$args['step']; // current step is caugth in the request uri path
        if($step==null) $this->redirectUser($response, "/install/0");
        
        $loadedphpextensions=get_loaded_extensions(false);
        
        // check if requested method is GET or POST
        switch($request->getMethod()) {
        case 'POST':
            $this->log->info("Installer step $step (POST)");
            $params=(array)$request->getParsedBody(); // submitted params
            // 1) check the parameters filled by the user for the given step
            $formerr=$this->checkFormParams($step, $params);
            // 2) cleanup unneeded parameters
            $params2=$params;
            unset($params2['__formstep']);
            $this->log->debug("Sending step cookie ".$this->c->get('settings')['cookies']['prefixnamebystep'].$step);
            // 3) save parameters for this step into the installation step cookie
            EncryptedCookies::setEncryptedCookie(
                $response,
                $this->c->get('settings')['cookies']['prefixnamebystep'].$step,
                json_encode($params2),
                $this->c->get('settings')['cookies']['key'],
                $this->c->get('settings')['cookies']['salt'],
                $this->c->get('settings')['cookies']['cookieopts']
            );
            
            // form submit buttons decide the operation to be performed on the step (back or next)
            $op=""; if(isset($params['__formstep'])) $op=$params['__formstep']; // submitted operation (back or next)
            switch($op) {
            case '__back': // user want to go to the previous step
                $this->log->debug("User want to go back from step $step to step ".(int)$step-1);
                return $this->redirectUser($response, "/install/".(int)$step-1);
                break;
            case '__next': // user want to go to the next step
                $this->log->debug("User want to go next from step $step to step ".(int)$step+1);
                if(!count($formerr)) { // form validated successfully
                    return $this->redirectUser($response, "/install/".(int)$step+1);
                }
                // setup form errors
                $this->log->info("Step up from $step to ".((int)$step+1)." not permitted because form is not validated");
                break;
            default:
                // should not happen, let flow as if user requested GET method
                break;
            }
            break;
        
        case 'GET':
            $this->log->info("Installer step $step (GET)");
            try {
                $params2=(array)json_decode(
                    EncryptedCookies::decryptCookie(
                        $request,
                        $this->c->get('settings')['cookies']['prefixnamebystep'].$step,
                        $this->c->get('settings')['cookies']['key'],
                        $this->c->get('settings')['cookies']['salt'],
                    )
                );
                $formerr=$this->checkFormParams($step, $params2);
            } catch(\Exception $e) { // cannot get cookie, not a big problem at the moment
                $this->log->debug("Step cookie ".$this->c->get('settings')['cookies']['prefixnamebystep'].$step." is not there or is not valid");
                EncryptedCookies::unsetEncryptedCookie(
                    $response,
                    $this->c->get('settings')['cookies']['prefixnamebystep'].$step,
                    $this->c->get('settings')['cookies']['cookieopts']
                );
            }
            
            break;
        default:
            // should not happen
            return $response;
        }
        
        $this->smarty->assign('step', $step);
        $this->smarty->assign('formstep', '__formstep'); // submit button form attribute name
        $this->smarty->assign('formbackward', '__back'); // back submit button value
        $this->smarty->assign('formforward', '__next');  // next submit button value
        $this->smarty->assign('minpwd', $this->minpwd);  // generic passwords, minimum length
        $this->smarty->assign('maxpwd', $this->maxpwd);  // generic passwords, maximum length
        $this->smarty->assign('params', $params2);       // form parameters
        $this->smarty->assign('formerr', $formerr);
        
        switch($step) {
        case 0: // welcome
            $this->log->info("Installer presentation");
            $this->smartyRender($response, 'index.tpl');
            break;
        case 1: // fronted admin user details
            $this->smartyRender($response, 'installstep1_adminuser.tpl');
            break;
        case 2: // fronted DB details
            $dbdrivers=\Doctrine\DBAL\DriverManager::getAvailableDrivers();
            $loaded_dbdrivers=[];
            foreach($dbdrivers as $dbdriver) { // search if supported driver is loaded as php extension
                $ret=array_search($dbdriver, $loadedphpextensions, true);
                if($ret) { // driver supported and loaded
                    $loaded_dbdrivers[$dbdriver]=true;
                } else { // driver supported but not loaded
                    $loaded_dbdrivers[$dbdriver]=false;
                }
            }
            
            // default db user
            $this->smarty->assign('defaultdbuser', "frontskel");
            // default db name
            $this->smarty->assign('defaultdbname', "frontskel");
            // default db charset
            $this->smarty->assign('defaultdbcharset', "UTF8");
            // all supported db drivers
            $this->smarty->assign('dbdrivers', $dbdrivers);
            // only supported and loaded db drivers
            $this->smarty->assign('loaded_dbdrivers', $loaded_dbdrivers);
            
            $this->smartyRender($response, 'installstep2_fedb.tpl');
            break;
        case 3: // tokens
            // default rt key
            $this->smarty->assign('defaultrtkey', bin2hex(random_bytes(32)));
            // default rt time skew
            $this->smarty->assign('defaultrtts', 10);
            // default rt time expiration
            $this->smarty->assign('defaultrtexp', 10512000);
            // default rt autorefresh time
            $this->smarty->assign('defaultrtar', 7200);
            // default at key
            $this->smarty->assign('defaultatkey', bin2hex(random_bytes(32)));
            // default at time skew
            $this->smarty->assign('defaultatts', 2);
            // default at time expiration
            $this->smarty->assign('defaultatexp', 60);
            
            $this->smartyRender($response, 'installstep3_tokens.tpl');
            break;
        default:
            $msg="User requested install step ".$step." but this is not valid here";
            $this->log->error($msg);
            throw new \Slim\Exception\HttpNotFoundException($request);
            break;
        }
        return $response;
    }
}
