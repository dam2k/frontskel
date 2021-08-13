<?php
// vim: expandtab:ts=4:sw=4

/**
 * FrontSkel: PHP slim based micro framework and WEB frontend
 * License: MIT
 * Author: Dino Ciuffetti <dam2000@gmail.com>
 */

declare(strict_types=1);

namespace FrontSkel;

use DI\Container;
use DI\Bridge\Slim\Bridge;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;
use FrontSkel\Middlewares\ContentLength;
use FrontSkel\Exceptions\ConfigNotValidException;
use FrontSkel\Controllers\FERouter;
use FrontSkel\Controllers\InstallerRouter;
use FrontSkel\Middlewares\AddDefaultHeaders;
use FrontSkel\Middlewares\RemoveTrailingSlash;
use FrontSkel\Middlewares\RequestLogger;
use FrontSkel\Middlewares\Auth\CheckEmailPwd;
use FrontSkel\Middlewares\Auth\ReleaseAuthTokens;
use FrontSkel\Middlewares\Auth\DropAuthToken;
use FrontSkel\Middlewares\Auth\CheckValidAuthToken;
use Doctrine\DBAL\Connection;
use Smarty;
use FrontSkel\Exceptions\CustomErrorHandler;
use FrontSkel\Exceptions\CustomErrorRenderer;
use Slim\Handlers\Strategies\RequestResponse;

class App
{
    protected Container $c; // our container
    protected \Slim\App $slimapp; // our application instance
    
    /**
     * Initialize the dependency injection container and all its definitions
     */
    private function initializeContainer(String $config) : Container {
        $configFile=stream_resolve_include_path($config);
        if(!$configFile || !is_readable($config)) {
            throw new ConfigNotValidException("The configuration file \"$config\" cannot be found or read");
        }
        $builder = new \DI\ContainerBuilder();
        $builder->enableCompilation(__DIR__ . '/../tmp');
        $builder->writeProxiesToFile(true, __DIR__ . '/../tmp/proxies');
        // application configuration file using PHP-DI PHP configuration format (https://php-di.org/doc/php-definitions.html)
        $builder->addDefinitions($config);
        
        // logger component
        $builder->addDefinitions([
            // https://github.com/Seldaek/monolog
            LoggerInterface::class => function (ContainerInterface $c) {
                $settings = $c->get('settings')['logger'];
                $logger = new Logger($settings['name']);
                // the default date format is "Y-m-d\TH:i:sP"
                //$dateFormat = "Y-m-d H:i:s";
                $dateFormat = $settings['dateFormat'];
                // the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
                //$output = "%datetime% > %channel%.%level_name% > %message%\n";
                $output = $settings['logFormat'];
                // finally, create a formatter
                $formatter = new LineFormatter($output, $dateFormat);
                $handler = new StreamHandler($settings['path'], $settings['level']);
                $handler->setFormatter($formatter);
                $logger->pushHandler($handler);
                $logger->pushProcessor(new UidProcessor());
                return $logger;
            },
        ]);
        
        // AUTH DB component
        $builder->addDefinitions([
            // https://www.doctrine-project.org/projects/doctrine-dbal/en/2.11/index.html
            'AuthDbConnection' => function (ContainerInterface $c): \Doctrine\DBAL\Connection {
                $settings = $c->get('settings')['auth_db'];
                /*
                $connectionParams = array(
                    user (string): Username to use when connecting to the database.
                    password (string): Password to use when connecting to the database.
                    host (string): Hostname of the database to connect to.
                    port (integer): Port of the database to connect to.
                    dbname (string): Name of the database/schema to connect to.
                    unix_socket (string): Name of the socket used to connect to the database.
                    charset (string): The charset used when connecting to the database.
                );
                */
                
                // https://www.doctrine-project.org/projects/doctrine-dbal/en/2.11/reference/platforms.html#platforms
                if(isset($settings['platform'])&&($settings['platform']!="")) { // override doctrine DB autoscouting
                    $myPlatform = new $settings['platform']();
                    $settings['platform']=$myPlatform;
                } else {
                    unset($settings['platform']);
                }
                return \Doctrine\DBAL\DriverManager::getConnection($settings);
            }
        ]);
        
        // FE SMARTY component
        $builder->addDefinitions([
            // https://www.smarty.net/docs/en/
            Smarty::class => function (ContainerInterface $c) {
                $settings = $c->get('settings')['smarty'];
                $smarty=new Smarty();
                $smarty->setTemplateDir($settings['TemplateDir'].'/'.$settings['TemplateName']);
                $smarty->setCompileDir($settings['CompileDir']);
                $smarty->setConfigDir($settings['ConfigDir']);
                $smarty->setCacheDir($settings['CacheDir']);
                $smarty->configLoad($settings['MainConfigFile']);
                $smarty->debugging = $smarty->getConfigVars('debug');
                if($smarty->debugging == "true") $c->set('settings.displayErrorDetails]', true);
                $smarty->error_reporting = E_ALL & ~E_NOTICE;
                if($smarty->getConfigVars('caching') == "true") {
                    $smarty->setCaching(Smarty::CACHING_LIFETIME_CURRENT);
                }
                $smarty->assign('basePath', $c->get('settings')['basePath']);
                return $smarty;
            },
        ]);
        
        return $builder->build();
    }
    
    /**
     * Setup application middlewares
     */
    private function setupApplicationMiddlewares(): void {
        // 1) ContentLengthMiddleware
        /*$contentLengthMiddleware = new \Slim\Middleware\ContentLengthMiddleware();
        $this->slimapp->add($contentLengthMiddleware);      // ^ Add Content-Length response header based on Response weight
        */
        $this->slimapp->add(ContentLength::class);
        
        // 2) Slim addBodyParsingMiddleware() and addRoutingMiddleware()
        $this->slimapp->addBodyParsingMiddleware();
        $this->slimapp->addRoutingMiddleware();
        
        // 3) Custom Middlewares in reverse order of execution
        $this->slimapp->add(RemoveTrailingSlash::class);    // ^ Check if the request contains a trailing /, redirect without it in case
        $this->slimapp->add(AddDefaultHeaders::class);      // ^ Add default respose headers
        $this->slimapp->add(RequestLogger::class);          // ^ Log client request
        
        // TODO: check honeypot middleware: https://github.com/middlewares/honeypot
        // TODO: check CSP middleware: https://github.com/middlewares/csp
        // TODO: check CORS middleware: https://github.com/middlewares/cors
        // TODO: check Content Negotiation: https://github.com/gofabian/negotiation-middleware
        /*
        // CSRF --> https://github.com/slimphp/Slim-Csrf
        $responseFactory = $this->slimapp->getResponseFactory();
        */
        
        // 4) Error handlers
        $customErrorHandler = new CustomErrorHandler($this->slimapp->getCallableResolver(), $this->slimapp->getResponseFactory(), $this->c);
        $customErrorHandler->registerErrorRenderer('text/html', CustomErrorRenderer::class);
        
        /*
         * @param bool $displayErrorDetails -> Should be set to false in production
         * @param bool $logErrors -> Parameter is passed to the default ErrorHandler
         * @param bool $logErrorDetails -> Display error details in error log
        */
        $errorMiddleware=$this->slimapp->addErrorMiddleware($this->c->get('settings')['displayErrorDetails'], true, true);
        $errorMiddleware->setDefaultErrorHandler($customErrorHandler);
    }
    
    /**
     * Setup routing - user should extend this class and override this method with its personal slim routers
     */
    protected function setupRouting(): void {
        // http://www.slimframework.com/docs/v4/objects/routing.html
        // FRONTEND
        // NOTE: middlewares are executed in reverse order! last mw <<< mw <<< mw <<< first mw
        /*
        $this->slimapp->get('/', [FERouter::class, 'GETSlash'])->setName('getslash')->add(CheckValidAuthToken::class);
        $this->slimapp->get('/favicon.ico', [FERouter::class, 'getFavicon'])->setName('getfavicon');
        $this->slimapp->get('/login', [FERouter::class, 'GETLogin'])->setName('getlogin')->add(CheckValidAuthToken::class);
        $this->slimapp->get('/logout', [FERouter::class, 'GETLogout'])->setName('getlogout')->add(DropAuthToken::class);
        $this->slimapp->post('/login', [FERouter::class, 'POSTLogin'])->setName('postlogin')->add(ReleaseAuthTokens::class)->add(CheckEmailPwd::class);
        */
        
        // AUTH BACKEND
        /*
        $this->slimapp->get('/be/auth/v1/checkUserPwd', [BEAuthV1Router::class, ''])->setName('checkUserPwd');
        */
    }
    
    /**
     * Setup routing (Installer)
     * https://www.slimframework.com/docs/v4/objects/routing.html
     */
    #private function setupRoutingInstaller(): void {
    #    // http://www.slimframework.com/docs/v4/objects/routing.html
    #    // NOTE: middlewares are executed in reverse order! last mw <<< mw <<< mw <<< first mw
    #    $this->slimapp->get('/', [InstallerRouter::class, 'GETSlash'])->setName('getslash');
    #    $this->slimapp->get('/favicon.ico', [InstallerRouter::class, 'getFavicon'])->setName('getfavicon');
    #    $this->slimapp->map(['GET', 'POST'], '/install[/{step}]', [InstallerRouter::class, 'GETSlashInstall'])->setName('getslashinstall');
    #}
    
    // config file must be a valid PHP-DI PHP configuration file: https://php-di.org/doc/php-definitions.html
    public function __construct(String $config) {
        //$installing=false;
        //try {
            $this->c = $this->initializeContainer($config);
        //} catch(\FrontSkel\Exceptions\ConfigNotValidException $e) { // cannot open config file
            /*
            $installing=true;
            $this->c = $this->initializeContainer('../etc/config_install.php');
            $this->c->set('configFileNew', $config);
            */
        //}
        $this->slimapp = Bridge::create($this->c); // use slim-bridge to create the slim app: https://php-di.org/doc/frameworks/slim.html
        if(substr($this->c->get('settings')['basePath'], -1) == '/') { // fuck! I said in the config comment to not put a trailing slash, even if basepath is / !!!
            $msg='Please remove trailing slash from basePath parameter in config file!!';
            $this->c->get(LoggerInterface::class)->error($msg);
            throw new ConfigNotValidException($msg);
        }
        $this->slimapp->setBasePath($this->c->get('settings')['basePath']);
        $this->setupApplicationMiddlewares();
        
        // LOG
        //$this->c->get(LoggerInterface::class)->info("porcozio che notizia, ma come l'ha messa dentro guarda!");
        
        // DB
        /*
        try {
            //$conn=$this->c->get(Connection::class);
            //$platform = $conn->getDatabasePlatform();
            //print_r($platform);
            $sql = "SELECT * FROM re WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue('id', 1); // map id to 1
            $stmt->execute();
            print_r($stmt->fetchAssociative());
        } catch (\Exception $e) {
            echo "Beccato... " . $e->getMessage();
        }
        */
        
        $routeCollector = $this->slimapp->getRouteCollector();
        $routeCollector->setDefaultInvocationStrategy(new RequestResponse());
        $routeCollector->setCacheFile(__DIR__ . '/../tmp/routercache.tmp');
        
        //if(!$installing) {
        $this->setupRouting();
        /*} else  {
            $this->setupRoutingInstaller();
        }*/
        
        // run the application
        $this->slimapp->run();
    }
}
