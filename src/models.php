<?php
$host = 'localhost';
$db_name = 'roundtown';

$connection = new Mongo("mongodb://$host:27017");
$db = $connection->selectDB($db_name);

//towns
$collection_name = 'towns';
$collection = $connection->selectCollection($db, $collection_name);
$cursor = $collection->find();
$towns = array();
foreach($cursor as $document) {
    $towns['towns'][] = $document;
}

//feeds
$collection_name = 'feeds';
$collection = $connection->selectCollection($db, $collection_name);
$cursor = $collection->find();
$feeds = array();
foreach($cursor as $document) {
    $feeds['feeds'] = $document;
}
