<?php
require_once __DIR__.'/../config/db.php';

$connection = new Mongo("mongodb://$host:27017");
$db = $connection->selectDB($db_name);
