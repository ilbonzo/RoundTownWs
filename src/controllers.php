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
$app->get('/api/feeds/{id}', function (Request $request, $id) use ($app, $feeds) {
    if ($id === '') {
        $callback = $request->query->get('callback');
        $response = new JsonResponse($feeds,200,array('Content-Type' => 'application/json'));
    } else {
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
        $callback = $request->query->get('callback');
        $response = new JsonResponse($json,200,array('Content-Type' => 'application/json'));
    }

    $response->setCallback($callback);
    return $response;
})->value('id', '');