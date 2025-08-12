# Accounts

**Current account**
```php
print_r($kazoo->accounts()->current());
```
**Create account (example)**
```php
$resp = $kazoo->accounts()->create(['name' => 'Acme, Inc.']);
```
