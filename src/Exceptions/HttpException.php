<?php
declare(strict_types=1);

namespace Kazoo\Exceptions;

use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpException extends Exception
{
    private RequestInterface $request;
    private ResponseInterface $response;

    public function __construct(string $message, int $code, RequestInterface $request, ResponseInterface $response)
    {
        parent::__construct($message, $code);
        $this->request = $request;
        $this->response = $response;
    }

    public static function fromResponse(string $message, RequestInterface $request, ResponseInterface $response): self
    {
        return new self($message, $response->getStatusCode(), $request, $response);
    }

    public function getRequest(): RequestInterface { return $this->request; }
    public function getResponse(): ResponseInterface { return $this->response; }
}
