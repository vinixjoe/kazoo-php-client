# Numbers

**Assign a number to a device**
```php
$kazoo->numbers()->assign('+15551234567', ['device_id' => 'device_id']);
```

**Unassign (release) a number**
```php
$kazoo->numbers()->unassign('+15551234567');
```
