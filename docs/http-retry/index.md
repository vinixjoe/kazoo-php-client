# HTTP, Retries & Backoff

The SDK:
- Retries 429 using `Retry-After` when provided.
- Retries transient 5xx with exponential backoff.
- Throws typed exceptions for rate limiting and HTTP errors.

**Tip:** For bulk jobs, sleep between batches to avoid 429s.
