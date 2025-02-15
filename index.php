<?php

use Rammewerk\Component\Hydrator\Hydrator;

require __DIR__ . '/vendor/autoload.php';

$request = \Rammewerk\Request\Request::simpleInitialize();

$response = new \Rammewerk\Request\Response();

$response->htmxRetarget('.test');

var_dump($response->headers->all());
