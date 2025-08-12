# TokenAuth (static token)

```php
use Kazoo\Auth\TokenAuth;

// Replace the auth arg in SDK constructor:
auth: new TokenAuth(getenv('KAZOO_TOKEN'))
```
Use for short scripts or when a control plane already issues tokens.
