<?php
namespace RoundTownWs\Tests;

require_once __DIR__ . '/../../../vendor/autoload.php';

use Silex\WebTestCase;

class TweetsTest extends WebTestCase {

    public function createApplication() {
        $app = require __DIR__ . '/../../../src/app.php';
        require __DIR__ . '/../../../src/models.php';
        require __DIR__ . '/../../../src/controllers.php';
        $app['debug'] = true;
        unset($app['exception_handler']);

        return $app;
    }

    public function testApiTweets() {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/1.0/tweets');
        $this->assertTrue($client->getResponse()->isOk());
    }

}
