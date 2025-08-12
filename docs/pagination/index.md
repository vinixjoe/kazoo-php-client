# Pagination

Kazoo paginates using `next_start_key`. You have three options:

1. Use resource helpers like `listAll()`:
   ```php
   foreach ($kazoo->devices()->listAll(['paginate' => 'true']) as $d) {
       // process
   }
   ```

2. Use `SDK::paginate()` on any path:
   ```php
   foreach ($kazoo->paginate('/v2/users', ['paginate' => 'true']) as $u) {
       // process
   }
   ```

3. Use the tiny `Paginator` helper (build010):
   ```php
   $p = $kazoo->paginator('/v2/callflows');
   $p->each(function(array $cf) {
     // ...
   });
   ```
