<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use PhpPdgAnalysis\Command\UpdateCommand;
use PhpPdgAnalysis\Command\PrintCommand;
use PhpPdgAnalysis\Command\ClearCommand;

$cacheFile = __DIR__ . '/cache.json';

$application = new Application();
$application->add(new UpdateCommand($cacheFile));
$application->add(new PrintCommand($cacheFile));
$application->add(new ClearCommand($cacheFile));
$application->run();