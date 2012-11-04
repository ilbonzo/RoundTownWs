<?php
namespace RoundTownWs\Tests;

require_once __DIR__ . '/../../../vendor/autoload.php';

use Silex\WebTestCase;

class FeedTest extends WebTestCase {

    public function createApplication() {
        $app = require __DIR__ . '/../../../src/app.php';
        require __DIR__ . '/../../../src/models.php';
        require __DIR__ . '/../../../src/controllers.php';
        $app['debug'] = true;
        unset($app['exception_handler']);

        return $app;
    }

    public function testApiFeeds() {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/api/feeds');
        $this->assertTrue($client->getResponse()->isOk());
    }

    public function testApiFeed() {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/api/feed/1');
        $this->assertTrue($client->getResponse()->isOk());
    }
}
