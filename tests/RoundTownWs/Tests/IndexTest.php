<?php
namespace RoundTownWs\Tests;

require_once __DIR__ . '/../../../vendor/autoload.php';

use Silex\WebTestCase;

class IndexTest extends WebTestCase {

    public function createApplication() {
        $app = require __DIR__ . '/../../../src/app.php';
        require __DIR__ . '/../../../src/models.php';
        require __DIR__ . '/../../../src/controllers.php';
        $app['debug'] = true;
        unset($app['exception_handler']);

        return $app;
    }

    public function testInitialPage() {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');
        $response = $client->getResponse();
        $this->assertTrue($response->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Round Town")'));
    }

}
