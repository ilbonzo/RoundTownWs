<?php
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use SimplePie as SP;
use RoundTownWs\Utils\PlaceUtility;
use RoundTownWs\Utils\TweetUtility;
use RoundTownWs\Utils\ImageUtility;

require_once __DIR__ . '/../modules/eden/library/eden.php';
require_once __DIR__ . '/../modules/eden/library/eden/foursquare.php';
require_once __DIR__ . '/../modules/eden/library/eden/twitter.php';
require_once __DIR__ . '/../modules/eden/library/eden/instagram.php';

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'prod'));

$app = new Application();
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());

$app->register(new DerAlex\Silex\YamlConfigServiceProvider(__DIR__ . '/../config/settings.yml'));
$app->register(new TwigServiceProvider(), array(
    'twig.path'    => array(__DIR__.'/../views'),
    'twig.options' => array('cache' => $app['config']['env'][APPLICATION_ENV]['cache'])
));

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    return $twig;
}));

//add simplepie
$app['simplepie'] = function() {
    return new SimplePie();
};

//add eden foursquare
$app['foursquare'] = function() {
    return eden('foursquare');
};

//add eden twitter
$app['twitter'] = function() {
    return eden('twitter');
};

//add eden instagram
$app['instagram'] = function() {
    return eden('instagram');
};

//add utils
$app['placeutility'] = function () {
    return new PlaceUtility();
};
$app['tweetutility'] = function () {
    return new TweetUtility();
};
$app['imageutility'] = function () {
    return new ImageUtility();
};

require_once __DIR__.'/../src/models.php';
require_once __DIR__.'/../src/controllers.php';

return $app;
