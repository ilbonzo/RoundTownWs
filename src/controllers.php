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


//Towns
$app->get('/api/towns', function (Request $request) use ($app, $towns) {

    return new JsonResponse($towns,200,array('Content-Type' => 'application/json'));
});

//Town
$app->get('/api/town/{id}', function ($id) use ($app, $towns) {
    return new JsonResponse($towns[$id],200,array('Content-Type' => 'application/json'));
});

//Feeds
$app->get('/api/feeds', function (Request $request) use ($app, $feeds) {
    return new JsonResponse($feeds,200,array('Content-Type' => 'application/json'));
});

//Feed
$app->get('/api/feed/{id}', function ($id) use ($app, $feeds) {

    if ($id >= count($feeds)) {
        return new JsonResponse('error', 200, array('Content-Type' => 'application/json'));
    }

    $feed = new DOMDocument();
    $feed->load($feeds[$id]['url']);
    $json = array();

    $json['title'] = $feed->getElementsByTagName('channel')->item(0)->getElementsByTagName('title')->item(0)->firstChild->nodeValue;
    $json['description'] = $feed->getElementsByTagName('channel')->item(0)->getElementsByTagName('description')->item(0)->firstChild->nodeValue;
    $json['link'] = $feed->getElementsByTagName('channel')->item(0)->getElementsByTagName('link')->item(0)->firstChild->nodeValue;

    $items = $feed->getElementsByTagName('item');

    $json['items'] = array();
    $i = 0;

    foreach($items as $item) {
        $title = $item->getElementsByTagName('title')->item(0)->firstChild->nodeValue;
        $description = $item->getElementsByTagName('description')->item(0)->firstChild->nodeValue;
        $pubDate = $item->getElementsByTagName('pubDate')->item(0)->firstChild->nodeValue;
        $guid = $item->getElementsByTagName('guid')->item(0)->firstChild->nodeValue;
        $link = $item->getElementsByTagName('link')->item(0)->firstChild->nodeValue;

        $json['item'][$i]['title'] = $title;
        $json['item'][$i]['description'] = $description;
        $json['item'][$i]['pubdate'] = $pubDate;
        $json['item'][$i]['guid'] = $guid;
        $json['item'][$i]['link'] = $link;

        $i++;
    }

    return new JsonResponse($json,200,array('Content-Type' => 'application/json'));
});
