<?php

declare(strict_types=1);
error_reporting(E_ALL);
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

use App\Handlers\HttpErrorHandler;
use App\ResponseEmitter\ResponseEmitter;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;

try {
    require __DIR__ . '/../vendor/autoload.php';
    $containerBuilder = new ContainerBuilder();

    $settings = include APP_PATH . '/config/settings.php';
    $settings($containerBuilder);

    $dependencies = include APP_PATH . '/config/dependencies.php';
    $dependencies($containerBuilder);

    $repositories = include APP_PATH . '/config/repositories.php';
    $repositories($containerBuilder);

    $container = $containerBuilder->build();

    AppFactory::setContainer($container);
    $app = AppFactory::create();
    $callableResolver = $app->getCallableResolver();

    $routes = include APP_PATH . '/config/routes.php';
    $routes($app);

    $displayErrorDetails = $container->get('settings')['displayErrorDetails'];

    $serverRequestCreator = ServerRequestCreatorFactory::create();
    $request = $serverRequestCreator->createServerRequestFromGlobals();


    // Run App & Emit Response
    $response = $app->handle($request);
    $responseEmitter = new ResponseEmitter();
    $responseEmitter->emit($response);
} catch (Exception $e) {
    echo 'Exception' . $e->getMessage();
}
