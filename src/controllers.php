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
        //@TODO read feed
        //$feed->load($cursor['url']);

        $callback = $request->query->get('callback');
        $response = new JsonResponse($json,200,array('Content-Type' => 'application/json'));
    }

    $response->setCallback($callback);
    return $response;
})->value('id', '');