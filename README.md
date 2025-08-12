# Kazoo PHP Client

A lightweight **client library** (not a framework bundle/SDK monolith) for 2600Hz Kazoo APIs.
- PHP **8.2+**
- Transport-agnostic via **PSR-18**; default example uses Guzzle 7
- Small surface: `SDK` core + resource classes (Accounts, Users to start)
- No Docker, no framework coupling — ideal for inclusion in apps, workers, and one-off scripts

## Install

```bash
composer require kazoo-php-client:^0.1@dev
```

## Use in an app or script

```php
<?php
declare(strict_types=1);
require __DIR__ . '/vendor/autoload.php';

use Kazoo\SDK;
use Kazoo\Auth\UserAuth;
use Nyholm\Psr7\Factory\Psr17Factory;
use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle7\Client as GuzzlePsr18;

$http = new GuzzlePsr18(new GuzzleClient([ 'http_errors' => false ]));
$factory = new Psr17Factory();

$kazoo = new SDK(
  baseUrl: getenv('KAZOO_BASE_URL'),
  httpClient: $http,
  requestFactory: $factory,
  streamFactory: $factory,
  auth: new UserAuth(getenv('KAZOO_USER'), getenv('KAZOO_PASS'), getenv('KAZOO_REALM'))
);

// Example: list users
foreach ($kazoo->users()->list() as $user) {
  echo $user['id'] . ' ' . ($user['first_name'] ?? '') . PHP_EOL;
}
```

## Notes
- Keep dependencies light. Swap transports freely (Symfony HttpClient, etc.).
- Ship your own retry policy if you need different semantics; `Kazoo\Http\Retry` is pluggable.
- Additional resources (Devices, Numbers, Callflows, CDRs) can be added without expanding the public surface area.
### Devices example
```php
// Create a device
$device = $kazoo->devices()->create(['name' => 'Lobby Phone', 'device_type' => 'sip_device']);
print_r($device);

// Update
$updated = $kazoo->devices()->update($device['data']['id'], ['name' => 'Front Lobby Phone']);

// Delete
$kazoo->devices()->delete($device['data']['id']);
```

### Callflows example
```php
// Create a simple callflow (example structure; adjust for your Kazoo build)
$cf = $kazoo->callflows()->create([
  'name' => 'Main',
  'numbers' => ['+15551234567'],
  'flow' => ['module' => 'device', 'data' => ['id' => 'device_id_here']]
]);
print_r($cf);

// List all callflows
foreach ($kazoo->callflows()->list() as $item) {
  echo $item['id'] . ' ' . ($item['name'] ?? '') . PHP_EOL;
}
```


### Numbers example
```php
// Single page
foreach ($kazoo->numbers()->list(['filter' => 'assigned']) as $num) {
  echo $num['id'] . PHP_EOL;
}

// All pages using the built-in paginator
foreach ($kazoo->numbers()->listAll(['filter' => 'assigned']) as $num) {
  // ...
}
```

### Paginating any resource
```php
foreach ($kazoo->paginate('/v2/devices', ['paginate' => 'true']) as $device) {
  // handle devices across all pages
}
```


### CDRs example
```php
// Single page of CDRs (replace {account_id} with actual account UUID in endpoint paths)
foreach ($kazoo->cdrs()->list(['created_from' => '2025-08-01T00:00:00Z']) as $cdr) {
  print_r($cdr);
}

// All pages
foreach ($kazoo->cdrs()->listAll(['created_from' => '2025-08-01T00:00:00Z']) as $cdr) {
  // ...
}
```

> Note: In current Kazoo API, CDR endpoints are scoped under /accounts/{account_id}/cdrs.
> Replace {account_id} in SDK resource with your account UUID, or adapt the path generation.


### Channels example
```php
// List active channels
foreach ($kazoo->channels()->list() as $ch) {
  echo $ch['id'] . ' ' . ($ch['caller_id_number'] ?? '') . ' -> ' . ($ch['callee_id_number'] ?? '') . PHP_EOL;
}

// Hang up a channel
$kazoo->channels()->hangup('channel-id-here');

// Generic action, e.g., hold/unhold or transfer (payload differs by deployment)
$kazoo->channels()->action('channel-id-here', ['action' => 'hold']);
```


### Numbers assignment helpers
```php
// Assign to a callflow
$kazoo->numbers()->assign('+15551234567', ['callflow_id' => 'cf-123']);

// Assign to a device
$kazoo->numbers()->assign('+15551234567', ['device_id' => 'dev-456']);

// Unassign
$kazoo->numbers()->unassign('+15551234567');
```


### Error handling
```php
use Kazoo\Exceptions\InvalidAuthException;
use Kazoo\Exceptions\NotFoundException;
use Kazoo\Exceptions\ValidationException;
use Kazoo\Exceptions\HttpException;

try {
  $user = $kazoo->users()->get('non-existent-id');
} catch (NotFoundException $e) {
  // 404 or {error: "not_found"}
} catch (InvalidAuthException $e) {
  // 401/403 or {error: "invalid_auth"}
} catch (ValidationException $e) {
  // 400/422 or {error: "validation"}
} catch (HttpException $e) {
  // Other HTTP errors
}
```


## Examples
- `examples/account_copy.php` — copies users/devices/numbers from a source account to a destination (two SDKs via env vars).
- `examples/number_reassignment.php` — reassigns a number to a target device or callflow on the same account.

Run with env vars as documented in each script.


### Paginator helper
```php
$p = $kazoo->paginator('/v2/devices');

// 1) Each item
$p->each(['paginate' => 'true'], function(array $device) {
  // ...
});

// 2) Chunked batches
foreach ($p->chunk(['paginate' => 'true'], 200) as $batch) {
  // process 200 devices at a time
}
```


See **docs/overview/index.md** to dive into the full documentation.
