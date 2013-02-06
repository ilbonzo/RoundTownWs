#!/usr/bin/env php
<?php
//Bootstrap our Silex application
require_once __DIR__.'/../vendor/autoload.php';
$app = require __DIR__ . '/app.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

$console = new Application('Round Town Console', '0.1');

$console
    ->register('cc')
    ->setDefinition(array(
         new InputOption('all', null, InputOption::VALUE_NONE, 'Clear Cache'),
    ))
    ->setDescription('Clear Cache')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        if ($input->getOption('all')) {
            $output->write("\n\tAll Active\n\n");
        }
        //remove cache
        shell_exec('rm -Rf ../cache/*');
        print "\n\tclear cache\n\n";
    })
;

$console
    ->register('db-install')
    ->setDescription('Install')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        require_once __DIR__.'/../config/db.php';
        $connection = new Mongo("mongodb://$host:27017");
        $connection->dropDB($db_name);
        $db = $connection->selectDB($db_name);
        $db->createCollection("feeds",false); 
        $feeds_collection = $db->feeds;

        $keys = array();
        $remote_csv = 'https://docs.google.com/spreadsheet/pub?key=0Al-GRHJsbJ6FdGdBTzFTWG5maVphYWYyNUQxOTBTMFE&single=true&gid=0&output=csv';
        mb_internal_encoding('UTF-8');
        print $remote_csv."\n";
        /* Set internal character encoding to UTF-8 */
        if (($handle = fopen($remote_csv, "r")) !== FALSE) {
            $line = 0;
            $deps = array();
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (!$line) {
                    $header = $data;
                }
                else {
                    $dep = array_combine($header, $data);
                    $feeds_collection->insert($dep);
                }
                $line++;
            }
        }

        fclose($handle);
        print "\n\tdb install\n\n";
    })
;
$console->run();
