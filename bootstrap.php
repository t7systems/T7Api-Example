<?php

//ensure this is false for production environments!
//ini_set( "display_errors", false);

if (ini_get( "display_errors")) {
    set_exception_handler(function (Exception $ex){
        require __DIR__ . '/views/error500.php';
    });
}

if (!is_file('../config/custom.php')) {
    throw new RuntimeException('Please follow instructions inside '.realpath(__DIR__.'/config/custom.php.dist').'!', 7770);
}

require __DIR__ . '/Application.php';

session_start();

$app = new Application();

/**
 * Add configuration to container
 */
$app['cfg']  = require '../config/common.php';
$app['cfg'] = array_replace_recursive($app['cfg'], require '../config/custom.php');

if (!isset($app['cfg']['reqId'])) {
    throw new RuntimeException('reqId missing in '.realpath(__DIR__.'/config/custom.php').'!', 7773);
}
if (!isset($app['cfg']['secretKey'])) {
    throw new RuntimeException('secretKey missing in '.realpath(__DIR__.'/config/custom.php').'!', 7773);
}

/**
 * Optionally, create your own SoapClient instance and provide a custom URL, if you need to do so
 */
/*
$app['soap'] = function () use ($app) {
    //Always return a new instance to avoid nasty Segmentation fault bug.
    //See https://bugs.php.net/bug.php?id=43437 for example
    //If you feel lucky, share a singleton instance (see $app['t7_client'] below)
    return new \SoapClient($app['cfg']['urls']['wsdl']);
};
*/


/**
 * Optionally, add a CacheInterface instance depending on your needs, or let ClientServiceProvider create a default Cache
 */
if (isset($app['cfg']['cache'])) {
    if (isset($app['cfg']['cache']['dir'])) {
        $app['cache'] = new \T7LC\Soap\Cache\FileCache($app['cfg']['cache']);
    } else if (isset($app['cfg']['cache']['redis_host'])) {
        $app['cache'] = new \T7LC\Soap\Cache\RedisCache($app['cfg']['cache']);
    }
}


/**
 * Add client to the application/container
 */
$clientProvider = new \T7LC\Soap\Provider\ClientServiceProvider();
$clientProvider->register($app);

return $app;