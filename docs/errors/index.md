# Error Handling

Exceptions thrown by the client:
- `InvalidAuthException` (401/403 or `invalid_auth` payloads)
- `NotFoundException` (404)
- `ValidationException` (400/422)
- `RateLimitException` (429)
- `HttpException` (fallback with access to request/response)

```php
try {
  $user = $kazoo->users()->get('abcd');
} catch (\Kazoo\Exceptions\NotFoundException $e) {
  // handle not found
}
```
