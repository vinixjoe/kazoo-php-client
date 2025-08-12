<?php
declare(strict_types=1);

/**
 * Example: Number Re-assignment
 *
 * Moves a number from one device/user/callflow to another on the SAME account.
 * Env vars:
 *  KAZOO_BASE_URL, KAZOO_USER, KAZOO_PASS, KAZOO_REALM
 *  NUMBER_E164 - the number to move (e.g., +15551234567)
 *  TARGET_DEVICE_ID or TARGET_CALLFLOW_ID - where to point the number
 */

require __DIR__ . '/../vendor/autoload.php';

use Kazoo\SDK;
use Kazoo\Auth\UserAuth;
use Nyholm\Psr7\Factory\Psr17Factory;
use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle7\Client as GuzzlePsr18;

$httpFactory = new Psr17Factory();
$httpClient  = new GuzzlePsr18(new GuzzleClient([ 'http_errors' => false ]));

$kazoo = new SDK(
    baseUrl: getenv('KAZOO_BASE_URL') ?: '',
    httpClient: $httpClient,
    requestFactory: $httpFactory,
    streamFactory: $httpFactory,
    auth: new UserAuth(getenv('KAZOO_USER') ?: '', getenv('KAZOO_PASS') ?: '', getenv('KAZOO_REALM') ?: '')
);

$number = getenv('NUMBER_E164') ?: '';
$targetDevice = getenv('TARGET_DEVICE_ID') ?: null;
$targetCallflow = getenv('TARGET_CALLFLOW_ID') ?: null;

if ($number === '') {
    fwrite(STDERR, "Missing NUMBER_E164\n");
    exit(1);
}

$payload = [];
if ($targetDevice) {
    $payload = ['device_id' => $targetDevice];
} elseif ($targetCallflow) {
    $payload = ['callflow_id' => $targetCallflow];
} else {
    fwrite(STDERR, "Set either TARGET_DEVICE_ID or TARGET_CALLFLOW_ID\n");
    exit(1);
}

try {
    $res = $kazoo->numbers()->assign($number, $payload);
    echo "Number {$number} re-assigned.\n";
} catch (\Throwable $e) {
    fwrite(STDERR, "Failed to re-assign {$number}: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
