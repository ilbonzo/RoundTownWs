<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//home page
$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', array());
})
->bind('homepage')
;

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }
    $page = 404 == $code ? '404.html' : '500.html';

    return new Response($app['twig']->render($page, array('code' => $code)), $code);
});

//Feeds
/**
 * Feeds
 */
$app->get('/'. $app['config']['app']['api_version'] .'/feeds/{id}', function (Request $request, $id) use ($app, $connection, $db) {
    $collection_name = 'feeds';
    $collection = $connection->selectCollection($db, $collection_name);
    if ($id === '') {
        $feedQuery = array();
        $tag = $request->get('tag');
        if (!empty($tag)) {
            $feedQuery = array('tag' => $tag);
        }
        $cursor = $collection->find($feedQuery);
        $cursor->sort(array('title' => 1));
        $feeds = array();
        foreach($cursor as $document) {
            $feed['id'] = $document['_id']->{'$id'};
            $feed['title'] = $document['title'];
            $feed['url'] = $document['url'];
            $feed['tag'] = $document['tag'];
            $feeds[] = $feed;
        }
        $response = new Response($app['twig']->render($app['config']['template']['json'], ['data' => $feeds]), 200,array('Content-Type' => 'application/json'));
    } else {
        $cursor = $collection->findOne(array('_id' => new MongoId($id)));
        $json = array();
        $feed = $app['simplepie'];
        $feed->set_feed_url($cursor['url']);
        $feed->set_item_class();
        $feed->enable_cache(true);
        $feed->set_cache_duration(3600);
        $feed->set_cache_location('../cache/simplepie');
        $feedResult = $feed->init();
        $news = array();
        if ($feedResult) {
            $feed->handle_content_type();
            foreach($feed->get_items() as $item) {
                $n = array();
                $n['title'] = $item->get_title();
                $n['description'] = $item->get_description();
                $n['date'] = $item->get_date('j-n-Y');
                $n['author'] = $item->get_author()->get_name();
                $n['link'] = $item->get_link();
                $news[] = $n;
            }
            $response = new Response($app['twig']->render($app['config']['template']['json'], ['data' => $news]), 200,array('Content-Type' => 'application/json'));
        } else {
            $response = new JsonResponse('', 503, array('Content-Type' => 'application/json'));
        }
    }

    return $response;
})->value('id', '');

//foursquare
/**
 * images
 */
$app->get('/'. $app['config']['app']['api_version'] .'/images',function (Request $request) use ($app) {

    $venue = $app['foursquare']->venue($app['config']['foursquare']['access_token']);
    $result = $venue->getVenuePhoto($app['config']['foursquare']['venue_id'],'venue');
    $photos = array();
    foreach ($result['response']['photos']['items'] as $item) {
        $p['url'] = $item['prefix'] . 'width960' . $item['suffix'];
        $p['url_thumb'] = $item['prefix'] . '150x150' . $item['suffix'];
        isset($item['user']['lastName']) ? $lastName = ' ' . $item['user']['lastName'] : $lastName = '';
        $p['user'] = $item['user']['firstName'] . $lastName;
        $photos[] = $p;
    }
    $response = new Response($app['twig']->render($app['config']['template']['json'], ['data' => $photos]), 200,array('Content-Type' => 'application/json'));
    return $response;
});

/**
 * Places
 */
$app->get('/'. $app['config']['app']['api_version'] .'/places/{id}', function (Request $request, $id) use ($app) {
    $venue = $app['foursquare']->venue($app['config']['foursquare']['access_token']);
    if ($id === '') {
        $venue->setLimit(50);
        $venue->setRadius(2000);
        $result = $venue->search(null, $app['config']['geo']['latitude'], $app['config']['geo']['longitude']);

        $places = array();
        foreach ($result['response']['venues'] as $item) {

            isset($item['location']['address']) ? $address = $item['location']['address'] : $address = '';
            isset($item['contact']['phone']) ? $phone = $item['contact']['phone'] : $phone = '';
            isset($item['contact']['twitter']) ? $twitter = $item['contact']['twitter'] : $twitter = '';
            isset($item['url']) ? $url = $item['url'] : $url = '';
            $p = [
                'id' => $item['id'],
                'name' => $item['name'],
                'address' => $address,
                'phone' => $phone,
                'twitter' => $twitter,
                'url' => $url,
                'foursquare' => $item['canonicalUrl']
            ];
            $places[] = $p;
        }
        $response = new Response($app['twig']->render($app['config']['template']['json'], ['data' => $places]), 200,array('Content-Type' => 'application/json'));
    } else {
        $result = $venue->getVenue($id);
        $item = $result['response']['venue'];
        isset($item['location']['address']) ? $address = $item['location']['address'] : $address = '';
        isset($item['contact']['phone']) ? $phone = $item['contact']['phone'] : $phone = '';
        isset($item['contact']['twitter']) ? $twitter = $item['contact']['twitter'] : $twitter = '';
        isset($item['url']) ? $url = $item['url'] : $url = '';
        if ($item['tips']['count'] > 0) {
            $tips = $item['tips']['groups'][0]['items'];
        } else {
            $tips = '';
        }
        $place = [
                'id' => $item['id'],
                'name' => $item['name'],
                'address' => $address,
                'phone' => $phone,
                'twitter' => $twitter,
                'url' => $url,
                'foursquare' => $item['canonicalUrl'],
                'tips' => $tips
            ];
        $response = new Response($app['twig']->render($app['config']['template']['json'], ['data' => $place]), 200,array('Content-Type' => 'application/json'));
    }

    return $response;
})->value('id', '');

/**
 * Place images
 */
$app->get('/'. $app['config']['app']['api_version'] .'/places/{id}/images', function (Request $request, $id) use ($app) {
    $venue = $app['foursquare']->venue($app['config']['foursquare']['access_token']);
    $result = $venue->getVenuePhoto($id,'venue');
    $photos = array();
    foreach ($result['response']['photos']['items'] as $item) {
        $p['url'] = $item['prefix'] . 'width960' . $item['suffix'];
        $p['url_thumb'] = $item['prefix'] . '150x150' . $item['suffix'];
        isset($item['user']['lastName']) ? $lastName = ' ' . $item['user']['lastName'] : $lastName = '';
        $p['user'] = $item['user']['firstName'] . $lastName;
        $photos[] = $p;
    }
    $response = new Response($app['twig']->render($app['config']['template']['json'], ['data' => $photos]), 200,array('Content-Type' => 'application/json'));
    return $response;
});

/**
 * Utility
 */
$app->get('/utility/foursquare/oauth', function () use ($app) {
    $auth = $app['foursquare']->auth($app['config']['foursquare']['client_id'], $app['config']['foursquare']['client_secret'], $app['config']['foursquare']['uri_redirect']);
    //if no code and no session
    if(!isset($_GET['code'])) {
        //redirect to login
        $login = $auth->getLoginUrl();
        header('Location: '.$login);
        exit;
    }

    //Code is returned back from foursquare
    if(isset($_GET['code'])) {
        //save it to session
        $access = $auth->getAccess($_GET['code']);
        print_r ($access['access_token']);
    }
});

