<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

// kernel directory is 6 levels up from this bootstrap file
$_SERVER['KERNEL_DIR'] = dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))).'/app';

// Composer
if (file_exists(__DIR__.'/../../../../../../vendor/autoload.php')) {
    $loader = require_once __DIR__.'/../../../../../../vendor/autoload.php';

    AnnotationRegistry::registerLoader('class_exists');

    return $loader;
}

throw new \RuntimeException('Could not find vendor/autoload.php, make sure you ran composer.');
