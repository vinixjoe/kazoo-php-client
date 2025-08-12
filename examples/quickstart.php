<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Kazoo\SDK;
use Kazoo\Auth\UserAuth;
use Nyholm\Psr7\Factory\Psr17Factory;
use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle7\Client as GuzzlePsr18;

$baseUrl = getenv('KAZOO_BASE_URL') ?: 'https://kazoo.example.com:8000';
$username = getenv('KAZOO_USERNAME') ?: 'user';
$password = getenv('KAZOO_PASSWORD') ?: 'pass';
$realm    = getenv('KAZOO_REALM') ?: 'example.com';

$httpFactory = new Psr17Factory();
$httpClient  = new GuzzlePsr18(new GuzzleClient([ 'http_errors' => false ]));

$sdk = new SDK(
    baseUrl: $baseUrl,
    httpClient: $httpClient,
    requestFactory: $httpFactory,
    streamFactory: $httpFactory,
    auth: new UserAuth($username, $password, $realm)
);

print_r($sdk->accounts()->current());
