# Migration Patterns

**Order of operations**
1. Users
2. Devices
3. Callflows
4. Numbers (import/assign)
5. Channels (drain/handover, optional)

**ID mapping**
- Keep a map of source → destination IDs for users/devices/callflows
- Store in a durable KV (SQLite/Redis/JSON file) during migration

**Examples to study**
- `examples/account_copy.php`
- `examples/number_reassignment.php`
