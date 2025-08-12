<?php
declare(strict_types=1);

namespace Kazoo;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Kazoo\Auth\AuthInterface;
use Kazoo\Http\Retry;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Kazoo\Http\ResponseDecoder;
use Kazoo\Http\PathBuilder;
use Kazoo\Config\Options;
use Kazoo\Exceptions\HttpException;
use Kazoo\Exceptions\RateLimitException;
use Kazoo\Exceptions\InvalidAuthException;
use Kazoo\Exceptions\NotFoundException;
use Kazoo\Exceptions\ValidationException;
use Kazoo\Resources\Accounts;
use Kazoo\Resources\Users;
use Kazoo\Resources\Devices;
use Kazoo\Resources\Callflows;
use Kazoo\Resources\Numbers;
use Kazoo\Resources\Channels;
use Kazoo\Resources\CDRs;
use Kazoo\Resources\Devices;
use Kazoo\Resources\Callflows;

/**
 * Core SDK entry point.
 */
final class SDK
{
    private array $redactHeaders = ['authorization', 'x-auth-token'];

    /** @var \Kazoo\Config\Options */
    private $options;
    /** @var \Kazoo\Http\PathBuilder */
    private $paths;

    private string $baseUrl;
    private ?string $accountId = null;
    /** @var 'auto'|'legacy'|'v5_4' */
    private string $apiVersionMode = 'auto';
    private ClientInterface $httpClient;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;
    private AuthInterface $auth;
    private Retry $retry;
    private ?LoggerInterface $logger = null;
    private ?CacheInterface $cache = null;
    private ?string $accountId = null;
    private ResponseDecoder $decoder;


    public function __construct(
        string $baseUrl,
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        AuthInterface $auth,
        ?Options $options = null
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->auth = $auth;
        $this->retry = new Retry();
        $this->decoder = new ResponseDecoder();
        $this->options = $options ?? new Options('auto', null);
        $this->paths = new PathBuilder($this->options);
    }

}

    /**
     * Decode a JSON response body to array; return [] if invalid.
     * @return array<string,mixed>
     */
    private function decodeBodyArray(ResponseInterface $response): array
    {
        try {
            return $this->decoder->toArray($response);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Low-level HTTP call used by resources.
     *
     * @param array<string,string> $headers
     * @param array<string,mixed>|null $json
     */
    public function request(string $method, string $path, array $headers = [], ?array $json = null): array
    {
        $url = $this->baseUrl . '/' . ltrim($path, '/');
        $body = null;

        $merged = array_merge([
            'Accept' => 'application/json',
        ], $headers);

        $tokenHeader = $this->auth->authenticate($this, $method, $path, $merged, $json);
        if ($tokenHeader) {
            $merged = array_merge($merged, $tokenHeader);
        }

        if ($json !== null) {
            $merged['Content-Type'] = 'application/json';
            $body = $this->streamFactory->createStream(json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        $request = $this->requestFactory->createRequest($method, $url);
        foreach ($merged as $k => $v) {
            $request = $request->withHeader($k, $v);
        }
        if ($body) {
            $request = $request->withBody($body);
        }

        $attempt = 0;
        while (true) {
            $attempt++;
            $response = $this->httpClient->sendRequest($request);
            $status = $response->getStatusCode();

            if ($status === 429) {
                $retryAfter = (int)($response->getHeaderLine('Retry-After') ?: '1');
                if (!$this->retry->shouldRetry($attempt, $status)) {
                    throw new RateLimitException('Too many requests', $status, $request, $response);
                }
                $this->retry->sleep($attempt, $retryAfter);
                continue;
            }

            if ($status >= 500 && $status < 600) {
                if (!$this->retry->shouldRetry($attempt, $status)) {
                    throw HttpException::fromResponse('Server error', $request, $response);
                }
                $this->retry->sleep($attempt);
                continue;
            }

            if ($status >= 400) {
                $payload = $this->decodeBodyArray($response);
                $error = (string)($payload['error'] ?? '');
                $message = (string)($payload['message'] ?? 'HTTP error');
                // Map by status first
                if ($status === 404 || $error === 'not_found') {
                    throw NotFoundException::fromResponse($message ?: 'Not Found', $request, $response);
                }
                if ($status === 401 || $status === 403 || $error === 'invalid_auth') {
                    throw InvalidAuthException::fromResponse($message ?: 'Invalid authentication', $request, $response);
                }
                if ($status === 400 || $status === 422 || $error === 'validation') {
                    throw ValidationException::fromResponse($message ?: 'Validation error', $request, $response);
                }
                throw HttpException::fromResponse($message, $request, $response);
            }

            return $this->decoder->toArray($response);
        }
    }

    /**
     * Iterate all items from a paginated collection using Kazoo's next_start_key.
     * The endpoint must return { data: [...], next_start_key?: string }.
     *
     * @return iterable<array<string,mixed>>
     */
    public function paginate(string $path, array $query = []): iterable
    {
        $startKey = null;
        while (true) {
            $q = $query;
            $q['paginate'] = $q['paginate'] ?? 'true';
            if ($startKey !== null) {
                $q['start_key'] = $startKey;
            }
            $qs = $q ? ('?' . http_build_query($q)) : '';
            $resp = $this->request('GET', $path . $qs);
            $items = $resp['data'] ?? [];
            if (is_array($items)) {
                foreach ($items as $item) {
                    if (is_array($item)) {
                        yield $item;
                    }
                }
            }
            $next = $resp['next_start_key'] ?? null;
            if (!is_string($next) || $next === '') {
                break;
            }
            $startKey = $next;
        }
    }

    /** @return \Kazoo\Resources\Accounts */
    
    /**
     * Create a paginator bound to an API path.
     *
     * @param string $path e.g. '/v2/devices'
     * @return \Kazoo\Util\Paginator
     */
    public function paginator(string $path): \Kazoo\Util\Paginator
    {
        return new \Kazoo\Util\Paginator(function(array $q) use ($path) : array {
            $qs = $q ? ('?' . http_build_query($q)) : '';
            return $this->request('GET', $path . $qs);
        });
    }

    public function setAccountId(string $accountId): void
    {
        $this->options = $this->options->withAccountId($accountId);
        $this->paths->setOptions($this->options);
    }

    public function accountId(): ?string
    {
        return $this->options->accountId();
    }

    /** @param 'auto'|'v5_4'|'legacy' $mode */
    public function setApiVersionMode(string $mode): void
    {
        $this->options = $this->options->withApiVersionMode($mode);
        $this->paths->setOptions($this->options);
    }

public function setLogger(?LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function setCache(?CacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    public function setAccountId(?string $accountId): void
    {
        $this->accountId = $accountId;
    }

    public function accountId(): ?string
    {
        return $this->accountId;
    }

    public function accounts(): Accounts
    {
        return new Accounts($this);
    }

    /** @return \Kazoo\Resources\Users */
    public function baseUrl(): string
    {
        return $this->baseUrl;
    }

    public function users(): Users
    {
        return new Users($this);
    }

    /** @return \Kazoo\Resources\Devices */
    public function devices(): Devices
    {
        return new Devices($this);
    }

    /** @return \Kazoo\Resources\Callflows */
    public function callflows(): Callflows
    {
        return new Callflows($this);
    }

    /** @return \Kazoo\Resources\Numbers */
    public function numbers(): Numbers
    {
        return new Numbers($this);
    }

    public function cdrs(): CDRs
    {
        return new CDRs($this);
    }
}
