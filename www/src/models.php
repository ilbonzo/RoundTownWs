<?php

$connection = new MongoClient("mongodb://" . $app['config']['db']['host'] . ":27017");
$db = $connection->selectDB($app['config']['db']['name']);
