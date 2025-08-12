# ApiKeyAuth (static key)

```php
use Kazoo\Auth\ApiKeyAuth;

// Replace the auth arg in SDK constructor:
auth: new ApiKeyAuth(getenv('KAZOO_API_KEY'))
```
Default header is `X-Auth-Token`; pass a second argument to change it.
