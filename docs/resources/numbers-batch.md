# Numbers Collection Helpers

Batch operations (5.4-style, account-scoped when available):

```php
// Add numbers
$kazoo->numbers()->addMany(['+15551234567', '+15557654321']);

// Update many
$kazoo->numbers()->updateMany([
  ['number' => '+15551234567', 'callflow_id' => 'CF1'],
  ['number' => '+15557654321', 'device_id' => 'DEV1'],
]);

// Delete many (soft)
$kazoo->numbers()->deleteMany(['+15551234567', '+15557654321']);
```
