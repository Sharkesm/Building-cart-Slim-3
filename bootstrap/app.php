<?php

use Cart\App;
use Slim\Views\Twig;
use Illuminate\Database\Capsule\Manager as Capsule;


session_start();


require __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__.'/../');
$dotenv->load();

$app = new App;

$container = $app->getContainer();

$capsule = new Capsule;

$capsule->addConnection([
  'driver' => 'mysql',
  'host' => 'localhost',
  'database' => 'cart',
  'username' => 'root',
  'password' => '',
  'charset' => 'utf8',
  'collation' => 'utf8_unicode_ci',
  'prefix' => '',
]);


$capsule->setAsGlobal();
$capsule->bootEloquent();

# Setting up sandbox Authorizations
Braintree_Configuration::environment(getenv('BRAINTREE_ENV'));
Braintree_Configuration::merchantId(getenv('MERCHANTID'));
Braintree_Configuration::publicKey(getenv('PUBLIC_KEY'));
Braintree_Configuration::privateKey(getenv('PRIVATE_KEY'));



require __DIR__ . '/../app/routes.php';


$app->add(new \Cart\Middleware\ValidationErrorsMiddleware($container->get(Twig::class)));
$app->add(new \Cart\Middleware\OldInputMiddleware($container->get(Twig::class)));
$app->add(new RKA\Middleware\IpAddress(true));




/**

-- Capture user ip address for testing only

**/
$app->get('/ip/',function($request, $response){

      $ip = $request->getAttribute('ip_address');
      var_dump($ip);

});
