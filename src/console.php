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

$console->run();
