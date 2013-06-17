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
$app->get('/api/feeds/{id}', function (Request $request, $id) use ($app, $connection, $db) {
    $collection_name = 'feeds';
    $collection = $connection->selectCollection($db, $collection_name);
    if ($id === '') {
        $cursor = $collection->find();
        $feeds = array();
        foreach($cursor as $document) {
            $feeds[] = $document;
        }
        $callback = $request->query->get('callback');
        $response = new JsonResponse($feeds,200,array('Content-Type' => 'application/json'));
    } else {
        $cursor = $collection->findOne(array('_id' => new MongoId($id)));
        //if ($id >= count($feeds)) {
        //    return new JsonResponse('error', 200, array('Content-Type' => 'application/json'));
        //}
        $json = array();
        $feed = $app['simplepie'];
        $feed->set_feed_url($cursor['url']);
        $feed->set_item_class();
        $feed->enable_cache(false);
        //$feed->enable_cache(true);
        //$feed->set_cache_duration(3600);
        //$feed->set_cache_location('cache');
        $feed->init();
        $feed->handle_content_type();
        $news = array();
        foreach($feed->get_items() as $item) {
            $n = array();
            $n['title'] = $item->get_title();
            $n['description'] = $item->get_description();
            $news[] = $n;
        }
        $callback = $request->query->get('callback');
        $response = new JsonResponse($news,200,array('Content-Type' => 'application/json'));
    }

    $response->setCallback($callback);
    return $response;
})->value('id', '');