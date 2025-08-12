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
