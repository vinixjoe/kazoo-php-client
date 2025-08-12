<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Kazoo\SDK;
use Kazoo\Auth\TokenAuth;

final class SmokeTest extends TestCase
{
    public function testSdkConstructs(): void
    {
        $factory = new Psr17Factory();
        $client = new class implements ClientInterface {
            public function sendRequest(RequestInterface $request): ResponseInterface {
                return (new Nyholm\Psr7\Response(200, ['Content-Type' => 'application/json'], '{"data":{"ok":true}}'));
            }
        };

        $sdk = new SDK(
            'https://kazoo.example.com:8000',
            $client,
            $factory,
            $factory,
            new TokenAuth('test')
        );

        $data = $sdk->accounts()->current();
        $this->assertSame(true, $data['data']['ok']);
    }
}
