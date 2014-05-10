<?php

require __DIR__ . '/../vendor/autoload.php';

// NOTE1: use 192.168.100.10 instead of 127.0.0.1 if you using Virtual Machine for the API server
// NOTE2: a "ConnectionException" can be thrown if there is a problem with the server (timeout or connection refused,
//        for example). Be sure to handle this case by surrounding with a try/catch.

// Good response, no error, with exception
$client = new \EloGank\ApiClient\Client('127.0.0.1', 8080, 'json');
$response = $client->send('EUW', 'summoner.summoner_existence', ['Foobar']);

var_dump($response);

// Error response, with exception
$client = null;
$client = new \EloGank\ApiClient\Client('127.0.0.1', 8080, 'json');

try {
    $response = $client->send('EUW', 'summoner.summoner_existence', ['Not_found_summoner']);
}
catch (\EloGank\ApiClient\Exception\ApiException $e) {
    var_dump($e->getCause(), $e->getMessage());
}

// Good response, without exception
$client = null;
$client = new \EloGank\ApiClient\Client('127.0.0.1', 8080, 'json', false);
$response = $client->send('EUW', 'summoner.summoner_existence', ['Foobar']);

if ($response['success']) {
    var_dump($response['result']);
}
else {
    // catch error
}

// Error response, without exception
// Good response, without exception
$client = null;
$client = new \EloGank\ApiClient\Client('127.0.0.1', 8080, 'json', false);
$response = $client->send('EUW', 'summoner.summoner_existence', ['Not_found_summoner']);

if ($response['success']) {
    // do some process
}
else {
    var_dump($response['error']);
}