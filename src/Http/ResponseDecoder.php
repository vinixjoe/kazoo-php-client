<?php
declare(strict_types=1);

namespace Kazoo\Http;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class ResponseDecoder
{
    /**
     * @return array<string,mixed>
     */
    public function toArray(ResponseInterface $response): array
    {
        $body = (string)$response->getBody();
        if ($body === '') {
            return [];
        }
        $data = json_decode($body, true);
        if (json_last_error() != JSON_ERROR_NONE || !is_array($data)) {
            throw new RuntimeException('Invalid JSON from API: ' . json_last_error_msg());
        }
        return $data;
    }
}
