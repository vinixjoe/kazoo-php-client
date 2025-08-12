# Kazoo PHP Client — Overview

A lightweight, PSR‑18 compatible PHP library for the 2600Hz Kazoo APIs. It’s framework‑agnostic, typed, and designed for use in apps, workers, and one‑off scripts.

- PHP **8.2+**
- Transport‑agnostic via PSR‑18 (Guzzle shown in examples)
- Strict types, clean error model, retry/backoff, and paginated iterators

## Key Concepts
- **SDK Core** — builds requests, decodes responses, retries transient errors
- **Auth Strategies** — `UserAuth`, `TokenAuth`, `ApiKeyAuth`
- **Resources** — `Accounts`, `Users`, `Devices`, `Callflows`, `Numbers`, `Channels`, `CDRs`
- **Pagination** — `SDK::paginate()` and resource `listAll()` helpers; optional `Paginator` (build010)


> **Environment variables used in examples**
>
> - `KAZOO_BASE_URL` — e.g., `https://kazoo.example.com:8000`
> - `KAZOO_USER`, `KAZOO_PASS`, `KAZOO_REALM`
> - For cross-account examples: `SOURCE_*` and `DEST_*` variants
>
> **Bootstrap snippet used throughout:**
>
> ```php
> <?php
> declare(strict_types=1);
> require __DIR__ . '/../vendor/autoload.php';
>
> use Kazoo\SDK;
> use Kazoo\Auth\UserAuth;
> use Nyholm\Psr7\Factory\Psr17Factory;
> use GuzzleHttp\Client as GuzzleClient;
> use Http\Adapter\Guzzle7\Client as GuzzlePsr18;
>
> $factory = new Psr17Factory();
> $http    = new GuzzlePsr18(new GuzzleClient(['http_errors' => false]));
>
> $kazoo = new SDK(
>   baseUrl: getenv('KAZOO_BASE_URL'),
>   httpClient: $http,
>   requestFactory: $factory,
>   streamFactory: $factory,
>   auth: new UserAuth(getenv('KAZOO_USER'), getenv('KAZOO_PASS'), getenv('KAZOO_REALM'))
> );
> ```
