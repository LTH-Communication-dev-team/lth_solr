<?php

error_reporting(E_ALL);
ini_set('display_errors', true);

require __DIR__.'/../vendor/solarium/vendor/autoload.php';

if (file_exists(__DIR__.'/config.php')) {
    require(__DIR__.'/config.php');
} else {
    die(__DIR__);
    require('config.dist.php');
}