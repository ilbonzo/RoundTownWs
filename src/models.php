<?php
require_once __DIR__.'/../config/db.php';

$connection = new Mongo("mongodb://$host:27017");
$db = $connection->selectDB($db_name);

//feeds
$collection_name = 'feeds';
$collection = $connection->selectCollection($db, $collection_name);
$cursor = $collection->find();
$feeds = array();
foreach($cursor as $document) {
    $feeds[] = $document;
}
