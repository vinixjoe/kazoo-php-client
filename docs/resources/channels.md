# Channels

Common actions:
```php
$chan = $kazoo->channels()->get('channel_id');
$kazoo->channels()->hold('channel_id');
$kazoo->channels()->unhold('channel_id');
$kazoo->channels()->transfer('channel_id', '+15558675309');
$kazoo->channels()->hangup('channel_id');
```
