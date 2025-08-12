<?php
declare(strict_types=1);

/**
 * Example: Account Copy (source -> destination)
 *
 * Copies users, devices, and numbers from a SOURCE Kazoo account to a DEST.
 * This is a learning example; adapt/extend for real migrations (ID mapping, conflict handling).
 *
 * Env vars required:
 *  SOURCE_* for source cluster/account auth
 *  DEST_*   for destination cluster/account auth
 *
 * SOURCE_BASE_URL, SOURCE_USERNAME, SOURCE_PASSWORD, SOURCE_REALM
 * DEST_BASE_URL,   DEST_USERNAME,   DEST_PASSWORD,   DEST_REALM
 */

require __DIR__ . '/../vendor/autoload.php';

use Kazoo\SDK;
use Kazoo\Auth\UserAuth;
use Nyholm\Psr7\Factory\Psr17Factory;
use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle7\Client as GuzzlePsr18;

function make_sdk(string $base, string $user, string $pass, string $realm): SDK {
    $httpFactory = new Psr17Factory();
    $httpClient  = new GuzzlePsr18(new GuzzleClient([ 'http_errors' => false ]));
    return new SDK(
        baseUrl: $base,
        httpClient: $httpClient,
        requestFactory: $httpFactory,
        streamFactory: $httpFactory,
        auth: new UserAuth($user, $pass, $realm)
    );
}

// Build source/destination SDKs from env
$src = make_sdk(
    getenv('SOURCE_BASE_URL') ?: '',
    getenv('SOURCE_USERNAME') ?: '',
    getenv('SOURCE_PASSWORD') ?: '',
    getenv('SOURCE_REALM')    ?: ''
);
$dst = make_sdk(
    getenv('DEST_BASE_URL') ?: '',
    getenv('DEST_USERNAME') ?: '',
    getenv('DEST_PASSWORD') ?: '',
    getenv('DEST_REALM')    ?: ''
);

// --- USERS ---
// 1) Fetch users from source (across all pages)
// 2) Re-create on destination (minimal example copies a handful of fields)
echo "Copying users...\n";
$userCount = 0;
foreach ($src->users()->listAll() as $u) {
    $payload = [
        'first_name' => $u['first_name'] ?? '',
        'last_name'  => $u['last_name']  ?? '',
        'email'      => $u['email']      ?? null,
        'username'   => $u['username']   ?? null,
        // You may need to set a temp password/reset flow on the dest system
        // 'password' => 'TempP@ssw0rd!'
    ];
    try {
        $created = $dst->users()->create($payload);
        $userCount++;
    } catch (\Throwable $e) {
        // In real migrations, log + continue; handle duplicates/validation
        fwrite(STDERR, "Failed to create user: " . $e->getMessage() . PHP_EOL);
    }
}
echo "Users copied: {$userCount}\n";

// --- DEVICES ---
echo "Copying devices...\n";
$deviceCount = 0;
foreach ($src->devices()->listAll() as $d) {
    $payload = [
        'name'        => $d['name'] ?? 'Device',
        'device_type' => $d['device_type'] ?? 'sip_device',
        // You may need to handle credentials/mac/manual provisioning mapping
        // 'sip' => ['username' => ..., 'password' => ...],
    ];
    try {
        $created = $dst->devices()->create($payload);
        $deviceCount++;
    } catch (\Throwable $e) {
        fwrite(STDERR, "Failed to create device: " . $e->getMessage() . PHP_EOL);
    }
}
echo "Devices copied: {$deviceCount}\n";

// --- NUMBERS ---
// Note: Depending on permissions/policies, numbers may be account-level or inventory-level.
// This example demonstrates reading and then assigning to a callflow or device on dest.
echo "Re-creating number assignments...\n";
$numberCount = 0;
foreach ($src->numbers()->listAll() as $n) {
    $id = $n['id'] ?? null;
    if (!$id) continue;
    // Example: assign each number to a "Main" callflow (replace with your logic/IDs)
    try {
        // If your deployment requires first importing/reserving the number into the dest account,
        // do that here before assignment.
        $dst->numbers()->assign($id, [
            // Illustrative payload; adjust to your Kazoo version
            'callflow_id' => 'REPLACE_WITH_DEST_CALLFLOW_ID'
        ]);
        $numberCount++;
    } catch (\Throwable $e) {
        fwrite(STDERR, "Failed to assign number {$id}: " . $e->getMessage() . PHP_EOL);
    }
}
echo "Numbers re-assigned: {$numberCount}\n";

echo "Account copy example complete.\n";
