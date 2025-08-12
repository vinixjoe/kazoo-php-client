
# Compatibility (Legacy vs 5.4)

The client supports **legacy** and **Kazoo 5.4** installs.

- **Routing:** If an `account_id` is known, resources use **account-scoped** routes
  (`/v2/accounts/{ACCOUNT_ID}/...`). Otherwise the client falls back to legacy
  non-scoped routes.
- **Auth:** `UserAuth` attempts hashed `PUT /v2/user_auth` first; if not supported,
  it falls back to legacy `POST /v2/user_auth` with credentials.
- **Numbers:** Uses `/phone_numbers` under account scope (5.4) or legacy `/numbers` when
  account context is unknown.

## Detecting server mode
```php
$probe = $kazoo->probeVersion();
/*
$probe = [
  'mode' => 'v5_4' | 'legacy',
  'account_scoped' => true|false,
  'account_id' => '... or null'
];
*/
```

## Forcing a mode (optional)
```php
$kazoo->setApiVersionMode('legacy'); // or 'v5_4' or 'auto' (default)
```

## Setting account context explicitly
```php
$kazoo->setAccountId('YOUR_ACCOUNT_ID'); // forces account-scoped routing
```
