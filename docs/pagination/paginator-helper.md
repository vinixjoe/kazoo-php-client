# Paginator Helper (build010)

Methods:
- `iterate(array $query = []) : iterable`
- `each(callable $fn, array $query = []) : void`
- `chunk(int $size, callable $fn, array $query = []) : void`

```php
$kazoo->paginator('/v2/numbers')
  ->chunk(100, function(array $batch) {
    // process 100 numbers at a time
  });
```
