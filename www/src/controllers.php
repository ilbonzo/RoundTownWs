<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//home page
$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', array());
})->bind('homepage');

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
            $feedQuery['tag'] = $tag;
        }
        $fbTag = $request->get('fbTag');
        if (!empty($fbTag)) {
            $feedQuery['fbTag'] = $fbTag;
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
        $response = new Response($app['twig']->render($app['config']['template']['json'], array('data' => $feeds)), 200,array('Content-Type' => 'application/json'));
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
                is_null($item->get_author()) ? $author = '' : $author = $item->get_author()->get_name();
                $n = array();
                $n['title'] = html_entity_decode($item->get_title());
                $n['description'] = $item->get_description();
                $n['date'] = $item->get_date('j-n-Y');
                $n['author'] = $author;
                $n['link'] = $item->get_link();
                $news[] = $n;
            }
            $response = new Response($app['twig']->render($app['config']['template']['json'], array('data' => $news)), 200,array('Content-Type' => 'application/json'));
        } else {
            $response = new JsonResponse('', 503, array('Content-Type' => 'application/json'));
        }
    }

    return $response;
})->value('id', '');

/**
 * images
 */
$app->get('/'. $app['config']['app']['api_version'] .'/images',function (Request $request) use ($app) {

    $photos = array();
    // instagram
    $media = $app['instagram']->media($app['config']['instagram']['access_token']);
    $result = $media->search(array(
                             'lat' => $app['config']['geo']['latitude'],
                             'lng' => $app['config']['geo']['longitude'],
                             'distance' => $app['config']['instagram']['distance']
                              ));
    foreach ($result['data'] as $item) {
        $p['url'] = $item['images']['standard_resolution']['url'];
        $p['url_thumb'] = $item['images']['thumbnail']['url'];
        $p['user'] = $item['caption']['from']['username'];
        $p['time'] = $item['created_time'];
        $photos[] = $p;
    }

    //foursquare
    $venue = $app['foursquare']->venue($app['config']['foursquare']['access_token']);
    $result = $venue->getVenuePhoto($app['config']['foursquare']['venue_id'],'venue');
    foreach ($result['response']['photos']['items'] as $item) {
        $p['url'] = $item['prefix'] . 'width960' . $item['suffix'];
        $p['url_thumb'] = $item['prefix'] . '150x150' . $item['suffix'];
        isset($item['user']['lastName']) ? $lastName = ' ' . $item['user']['lastName'] : $lastName = '';
        $p['user'] = $item['user']['firstName'] . $lastName;
        $p['time'] = $item['createdAt'];
        $photos[] = $p;
    }

    usort($photos, function($a, $b) use ($app) {
        return $app['imageutility']->sortByTime($a, $b);
    });

    $response = new Response($app['twig']->render($app['config']['template']['json'], array('data' => $photos)), 200,array('Content-Type' => 'application/json'));
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
        $tag = $request->get('tag');
        if (!empty($tag)) {
            $tagsArray = array($tag);
        } else {
            $tagsArray = NULL;
        }
        $result = $venue->search(null, $app['config']['geo']['latitude'], $app['config']['geo']['longitude'],$tagsArray);
        $places = array();
        foreach ($result['response']['venues'] as $item) {
            $icon = isset($item['categories'][0]) ? $item['categories'][0]['icon']['prefix'] . 'bg_88' . $item['categories'][0]['icon']['suffix'] : 'https://foursquare.com/img/categories_v2/none_bg_88.png';
            $p = array(
                'id' => $item['id'],
                'name' => $item['name'],
                'address' => isset($item['location']['address']) ? $item['location']['address'] : '',
                'lat' => $item['location']['lat'],
                'lng' => $item['location']['lng'],
                'phone' => isset($item['contact']['phone']) ? $item['contact']['phone'] : '',
                'twitter' => isset($item['contact']['twitter']) ? $item['contact']['twitter'] : '',
                'url' => isset($item['url']) ? $item['url'] : '',
                'foursquare' => isset($item['canonicalUrl']) ? $item['canonicalUrl'] : "" ,
                'icon' => $icon
            );
            $places[] = $p;
        }
        usort($places, function($a, $b) use ($app) {
            return $app['placeutility']->sortByName($a, $b);
        });

        $response = new Response($app['twig']->render($app['config']['template']['json'], array('data' => $places)), 200,array('Content-Type' => 'application/json'));
    } else {
        $result = $venue->getVenue($id);
        $item = $result['response']['venue'];
        isset($item['location']['address']) ? $address = $item['location']['address'] : $address = '';
        isset($item['contact']['phone']) ? $phone = $item['contact']['phone'] : $phone = '';
        isset($item['contact']['twitter']) ? $twitter = $item['contact']['twitter'] : $twitter = '';
        isset($item['url']) ? $url = $item['url'] : $url = '';

        (count($item['photos']['groups']) > 0) ? $image = $app['placeutility']->getImageByGroups($item['photos']['groups'], 'minithumb') : $image = '';
        if ($item['tips']['count'] > 0) {
            $tips = $item['tips']['groups'][0]['items'];
        } else {
            $tips = '';
        }
        $place = array(
                'id' => $item['id'],
                'name' => $item['name'],
                'image' => $image,
                'address' => $address,
                'lat' => $item['location']['lat'],
                'lng' => $item['location']['lng'],
                'phone' => $phone,
                'twitter' => $twitter,
                'url' => $url,
                'foursquare' => $item['canonicalUrl'],
                'categories' => $item['categories'],
                'tips' => $tips
            );
        $response = new Response($app['twig']->render($app['config']['template']['json'], array('data' => $place)), 200,array('Content-Type' => 'application/json'));
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
    $response = new Response($app['twig']->render($app['config']['template']['json'], array('data' => $photos)), 200,array('Content-Type' => 'application/json'));
    return $response;
});


//twitter
/**
 * twitter post
 */
$app->get('/'. $app['config']['app']['api_version'] .'/tweets',function (Request $request) use ($app) {

    $source = 'all';

    $from = $request->get('from');
    if (!empty($from)) {
        $source = $from;
    }
    $tweets = array();

    if ($source === 'list' || $source === 'all' ) {
        $lists = $app['twitter']->lists($app['config']['twitter']['consumer_key'], $app['config']['twitter']['consumer_secret'], $app['config']['twitter']['access_token'], $app['config']['twitter']['access_token_secret']);
        $lists->setPage($app['config']['twitter']['result_per_page']);
        $results = $lists->getTweets($app['config']['twitter']['list_id']);
        if (!empty($results['errors'])) {
            $error = current($results['errors']);
            $response = new Response($app['twig']->render($app['config']['template']['json'], array('data' => array('error'=>$error))), 500,array('Content-Type' => 'application/json'));
        } else {
            foreach ($results as $item) {
                $t['id'] = $item['id_str'];
                $t['text'] = $item['text'];
                $t['user'] = $item['user']['screen_name'];
                $t['url'] = 'https://twitter.com/' . $item['user']['screen_name'] . '/status/' . $item['id_str'];
                $tweets[] = $t;
            }
        }
    }

    if ($source === 'search' || $source === 'all' ) {
        $search = $app['twitter']->search($app['config']['twitter']['consumer_key'], $app['config']['twitter']['consumer_secret'], $app['config']['twitter']['access_token'], $app['config']['twitter']['access_token_secret']);
        $search->setCount($app['config']['twitter']['result_per_page']);
        $results = $search->search($app['config']['twitter']['search_query']);
        if (!empty($results['errors'])) {
            $error = current($results['errors']);
            $response = new Response($app['twig']->render($app['config']['template']['json'], array('data' => array('error'=>$error))), 500,array('Content-Type' => 'application/json'));
        } else {
            foreach ($results['statuses'] as $item) {
                $t['id'] = $item['id_str'];
                $t['text'] = $item['text'];
                $t['user'] = $item['user']['screen_name'];
                $t['url'] = 'https://twitter.com/' . $item['user']['screen_name'] . '/status/' . $item['id_str'];
                $tweets[] = $t;
            }
        }
    }

    usort($tweets, function($a, $b) use ($app) {
        return $app['tweetutility']->sortById($a, $b);
    });

    $response = new Response($app['twig']->render($app['config']['template']['json'], array('data' => $tweets)), 200,array('Content-Type' => 'application/json'));

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

// Retrive twitter's user lists
$app->get('/utility/twitter/lists/{user}', function (Request $request, $user) use ($app)
{
    $lists = $app['twitter']->lists($app['config']['twitter']['consumer_key'], $app['config']['twitter']['consumer_secret'], $app['config']['twitter']['access_token'], $app['config']['twitter']['access_token_secret']);
    $results = array();
    if (is_int($user) || is_string($user)) {
        $results = $lists->getAllLists($user);
        if (!empty($results['errors'])) {
            $results = array();
        }
    }

    $response = new Response($app['twig']->render($app['config']['template']['json'], array('data' => $results)), 200,array('Content-Type' => 'application/json'));

    return $response;
});
