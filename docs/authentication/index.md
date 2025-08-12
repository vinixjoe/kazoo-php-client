# Authentication (Overview)

The client delegates auth to implementations of `AuthInterface`.
- **UserAuth** — username/password/realm → token via `/v2/user_auth`
- **TokenAuth** — static Bearer token
- **ApiKeyAuth** — static API key header

Use `UserAuth` for most scripts; swap others without touching resource code.


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
