# UserAuth (username/password/realm)

```php
// see Bootstrap snippet in Overview; then:
print_r($kazoo->accounts()->current());
```
**Notes**
- Token is cached in memory for the lifetime of the `SDK` instance.
- For long‑running processes, consider persisting/reusing tokens (future build).

**Common pitfalls**
- Wrong `realm` yields `invalid_auth`. Double‑check the SIP realm.


## Disable token caching
You can opt out of token caching either via code or an environment variable:

```php
$auth = (new \Kazoo\Auth\UserAuth($user, $pass, $realm))
  ->disableCache(); // or ->setCacheEnabled(false)
```

Or set an environment variable before running your script:

```bash
export KAZOO_TOKEN_CACHE=off   # accepted values: off, 0, false, no
```
