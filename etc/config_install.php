<?php
// vim: expandtab:ts=4:sw=4

use Monolog\Logger;

// this will be passed to PHP-DI container builder's addDefinitions method: https://php-di.org/doc/php-definitions.html

return [
    'settings' => [
        'displayErrorDetails' => true, // Should be set to false in production
        'outputBuffering' => false,
        'basePath' => '', // DON'T USE trailing / !! Eg for "/" you MUST left empty, for /pippo write /pippo and not /pippo/ or it will not work!!!!
	    
        // installation cookies
        'cookies' => [
            'prefixnamebystep' => '_FS_inst_s', // eg: if prefixnamebystep is _FS_inst_s, on step 2 the cookie is called _FS_inst_s2
            'cookieopts' => ['path' => '/', 'hostonly' => true, 'secure' => true, 'httponly' => true, 'samesite' => 'Lax'],
            'key' => 'a55873a6f1f86d6c367badca6d54a3a9fa6b0fbb390475b13b1e8ce3af976f4c', // 64 truly random chars from 0 to f should be ok
            'salt' => '3a2fb468f5726f9132d094ae', // 24 truly random chars from 0 to f should be ok
        ],
        
        // LOGGER PARAMETERS
        'logger' => [ 
            'name' => 'FrontSkel',
            'path' => __DIR__ . '/../logs/app.log',
            'level' => Logger::DEBUG,
            'dateFormat' => 'Y-m-d H:i:s',
            'logFormat' => "%datetime% [%channel%.%level_name% %extra%] %message%\n",
        ],
        
        // FE SMARTY PARAMETERS
        'smarty' => [
            'TemplateDir' => __DIR__ . '/../smarty/templates',
            'CompileDir' => __DIR__ . '/../smarty/templates_c',
            'ConfigDir' => __DIR__ . '/../smarty/configs',
            'CacheDir' => __DIR__ . '/../smarty/cache',
            'MainConfigFile' => __DIR__ . '/smarty.conf',
            'TemplateName' => 'installer',
        ]
    ]
];

