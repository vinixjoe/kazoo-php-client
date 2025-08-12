# Logging (PSR-3)

Pass a PSR-3 logger for request summaries and retry notices.

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('kazoo');
$logger->pushHandler(new StreamHandler('php://stdout'));
$kazoo->setLogger($logger);
```
Sensitive headers like `Authorization` are redacted.
