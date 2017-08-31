#!/usr/bin/env php
<?php
require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use src\TeamcityCodeAnalysisTransformCommand;

$application = new Application();

$application->add(new TeamcityCodeAnalysisTransformCommand());
$application->run();