# Users

**List all users**
```php
foreach ($kazoo->users()->listAll(['paginate' => 'true']) as $user) {
  echo $user['id'], PHP_EOL;
}
```

**Get a user**
```php
$user = $kazoo->users()->get('user_id');
```
