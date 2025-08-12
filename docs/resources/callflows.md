# Callflows

**Create a simple callflow**
```php
$cf = $kazoo->callflows()->create([
  'name' => 'Main',
  'numbers' => ['+15551234567'],
  'flow' => ['module' => 'device', 'data' => ['id' => 'device_id_here']]
]);
```

**List callflows**
```php
foreach ($kazoo->callflows()->listAll(['paginate' => 'true']) as $cf) {
  // ...
}
```
