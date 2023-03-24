<?php declare(strict_types=1);

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Eureka\Kernel\Http\Application\Application;
use Eureka\Kernel\Http\Kernel;

//~ Start session
session_start();

//~ Define Loader & add main classes for config
require_once __DIR__ . '/../vendor/autoload.php';

$root  = realpath(__DIR__ . '/..');
$env   = getenv('EKA_ENV') ?: 'dev';
$debug = (bool) (getenv('EKA_DEBUG') ?: ($env === 'dev'));

//~ Run application
try {
    $application = new Application(new Kernel($root, $env, $debug));
    $application->send($application->run());
} catch (\Exception $exception) {
    echo 'Exception: ' . $exception->getMessage() . PHP_EOL;
    echo 'Trace: ' . $exception->getTraceAsString() . PHP_EOL;
    exit(1);
}

function _var_export($var, $title = 'var')
{
    echo '<pre>' . $title . ': ' . var_export($var, true) . '</pre>';
}
