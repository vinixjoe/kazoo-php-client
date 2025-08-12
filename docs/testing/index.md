# Testing

- Unit tests: PHPUnit 11
- Static analysis: PHPStan max level
- HTTP mocking: use PSR‑18 test doubles (no real network)

```bash
vendor/bin/phpunit
vendor/bin/phpstan analyse src --level=max
```
