<?php

use Rammewerk\Component\Hydrator\Hydrator;

require __DIR__ . '/vendor/autoload.php';

$request = \Rammewerk\Http\RequestFactory::create();

$response = new \Rammewerk\Http\Response();

$response->htmxRetarget('.test');

var_dump($response->headers->all());
