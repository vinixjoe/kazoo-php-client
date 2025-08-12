# Devices

**Create a device**
```php
$device = $kazoo->devices()->create([
  'name' => 'Lobby Phone',
  'device_type' => 'sip_device',
]);
```

**Iterate all devices**
```php
foreach ($kazoo->devices()->listAll(['paginate' => 'true']) as $device) {
  // ...
}
```
