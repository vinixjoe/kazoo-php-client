# Kazoo PHP Client – Changelog

## [build016] – 2025-08-12
### Added
- CacheFactory::fromEnv() to select cache backend (filesystem, APCu, Memcached) via env vars
- Lightweight PSR-16 adapters: FileCache, ApcuCache, MemcachedCache

### Changed
- Updated token caching docs with env-driven selection examples
- Updated README with quick usage snippet for CacheFactory

---

## [build015] – 2025-08-12
### Added
- Explicit opt-out for token caching (`disableCache()` / `setCacheEnabled(false)`)
- Environment toggle for token caching (`KAZOO_TOKEN_CACHE`)

### Changed
- SDK now exposes baseUrl() for per-cluster cache keys
- Docs updated: “Disable token caching” section

---

## [build014] – 2025-08-12
### Added
- Token caching (PSR-16 filesystem cache + Redis-ready interface)
- PSR-3 logging middleware with redaction
- Smarter retries with jitter and Retry-After support
- Numbers batch helpers (`addMany`, `updateMany`, `deleteMany`) with E.164 encoding

---

## [build013] – 2025-08-12
### Added
- Initial 5.4 compatibility probe (`SDK::probeVersion()`) with fallback for legacy clusters