<?php
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use SimplePie as SP;
require_once '../modules/eden/library/eden.php';
require_once '../modules/eden/library/eden/foursquare.php';

require_once '../config/config.php';

$app = new Application();
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new TwigServiceProvider(), array(
    'twig.path'    => array(__DIR__.'/../views'),
    'twig.options' => array('cache' => __DIR__.'/../cache'),
));

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    return $twig;
}));

//add simplepie
$app['simplepie'] = function() {
    return new SimplePie();
};

//add eden
$app['foursquare'] = function() {
    return eden('foursquare');
};

require_once __DIR__.'/../src/models.php';
require_once __DIR__.'/../src/controllers.php';

return $app;
