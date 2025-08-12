# CDRs

**Filter by date range**
```php
$from = '2025-01-01T00:00:00Z';
$to   = '2025-01-31T23:59:59Z';
foreach ($kazoo->cdrs()->listAll(['created_from' => $from, 'created_to' => $to]) as $cdr) {
  // ...
}
```
