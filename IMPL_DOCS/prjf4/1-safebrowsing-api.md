# Google Safe Browsing Integration

This document explains how Safe Browsing is integrated in this project, end to end, so a developer with no previous context can understand and maintain it.

## 1. Goal

The integration validates bookmark URLs before create and update operations.

Main objective:
- Prevent storing known malicious URLs.
- Apply a configurable fail policy when verification cannot be completed.

The current implementation checks URLs at write time (create/edit), not at read time (render/list).

## 2. Where it lives

Main components:
- `app/services/SafeBrowsingService.php`: API client + normalization + decision logic + cache.
- `app/services/ItemService.php`: business flow that calls Safe Browsing before persisting.
- `app/controller/ItemController.php`: user flow and flash messages.
- `environments/env.local.php`, `environments/env.prod.php`, `environments/env.example.php`: runtime configuration.

## 3. Request model and API endpoint

Current endpoint:
- `https://safebrowsing.googleapis.com/v4/threatMatches:find`

Current HTTP method:
- `POST`

Authentication:
- API key in query string: `?key=YOUR_KEY`

Payload sent to Google:
- `client.clientId = navana`
- `client.clientVersion = 1.0.0`
- `threatInfo.threatTypes = [MALWARE, SOCIAL_ENGINEERING, UNWANTED_SOFTWARE, POTENTIALLY_HARMFUL_APPLICATION]`
- `threatInfo.platformTypes = [ANY_PLATFORM]`
- `threatInfo.threatEntryTypes = [URL]`
- `threatInfo.threatEntries = [{ url: normalizedUrl }]`

Reason for v4:
- In this environment, v5 `urls:search` returned protobuf, which broke JSON parsing.
- v4 `threatMatches:find` returns JSON reliably with the current stack.

## 4. Full runtime flow

### 4.1 Create flow

1. User submits bookmark form.
2. `ItemController` calls `ItemService::insertItemWithSafetyCheck(...)`.
3. `ItemService` calls `SafeBrowsingService::checkUrlSafety(link)`.
4. If `canPersist` is `false`, controller sets flash error and redirects to insert form.
5. If `canPersist` is `true`, `ItemService` saves normalized URL through DAO.

### 4.2 Update flow

1. User submits edit form.
2. `ItemController` calls `ItemService::updateItemWithSafetyCheck(...)`.
3. Same `checkUrlSafety` path is executed.
4. If blocked, flash error + redirect to edit form.
5. If allowed, DAO updates with normalized URL.

## 5. Decision engine in SafeBrowsingService

`checkUrlSafety(string $url)` returns a unified array:
- `status`: decision state (`safe`, `unsafe`, `invalid`, `error`, `not_configured`)
- `isSafe`: boolean safety flag
- `canPersist`: whether create/update may continue
- `message`: user-facing message for blocked/failure cases
- `normalizedUrl`: canonicalized URL when available
- `source`: where decision came from (`local`, `cache`, `api`, `policy`)
- `threatTypes`: detected threat categories

Decision order:
1. Normalize URL.
2. Check in-memory cache (request-level).
3. Check file cache (cross-request).
4. Check config (API key present).
5. Call Google API.
6. Parse response and map to `safe` or `unsafe`.
7. If API/parse/config fails, apply strict-mode policy.

## 6. URL normalization

Normalization rules:
- Trim input.
- If scheme is missing, prepend `https://`.
- Parse with `parse_url`.
- Accept only `http` or `https`.
- Require non-empty host.
- Rebuild final URL with scheme, host, optional port, path, query, fragment.
- Validate final result with `FILTER_VALIDATE_URL`.

If any rule fails:
- `status = invalid`
- `canPersist = false`

## 7. Cache design

The system has 2 cache layers.

### 7.1 In-memory cache (request scope)

Variable:
- `private array $requestCache`

Behavior:
- Stores decisions during the current PHP request.
- Avoids duplicate checks for the same normalized URL in one request.

### 7.2 File cache (cross-request)

Directory:
- `api-cache/safe-browsing`

Filename:
- `sha256(normalizedUrl).json`

Stored JSON fields:
- `status` (`safe` or `unsafe`)
- `threat_types` (array)
- `expires_at` (unix timestamp)

TTL behavior:
- Uses `cacheDuration` from API when available and parseable.
- Fallback to configured TTL when API does not provide parseable duration.
- Enforced minimum: 60 seconds.
- Enforced maximum when API duration is parsed: 24 hours.

Expiration:
- Expired files are deleted on read and treated as cache miss.

## 8. Strict mode policy

Configuration key:
- `safe_browsing_strict_mode`

### strict mode = true

If verification cannot be completed (missing key, network failure, invalid API response, API error payload):
- `canPersist = false`
- create/update is blocked

### strict mode = false

If verification cannot be completed:
- `canPersist = true`
- create/update is allowed (degraded mode)

Important:
- If API confirms a URL as unsafe, it is blocked regardless of strict mode.
- If URL format is invalid, it is blocked regardless of strict mode.

## 9. Error handling and user feedback

Current behavior:
- API/network/parse/config failures are collapsed into a generic message:
	- `Could not verify URL security at this time. Please try again.`
- Controller sets this message in session flash and redirects back to form.

Why generic:
- Avoids exposing internal details to end users.
- Keeps UX clean while preserving security policy.

## 10. Configuration reference

Required keys:
- `safe_browsing_api_key`
- `safe_browsing_endpoint`
- `safe_browsing_timeout_seconds`
- `safe_browsing_cache_ttl_seconds`
- `safe_browsing_strict_mode`

Recommended defaults:
- Endpoint: `https://safebrowsing.googleapis.com/v4/threatMatches:find`
- Timeout: `5`
- Cache TTL fallback: `21600` (6 hours)
- Strict mode: `true` in production

## 11. Known limitations

- Write-time protection only. Existing records are not rechecked on read.
- Uses API key auth (no advanced privacy relay).
- Cache is file-based, no distributed cache coordination.
- No detailed technical logging yet for API failure diagnostics.

## 12. Quick verification checklist

Use this to validate behavior after changes:

1. Safe URL test
- Try `https://example.com`.
- Expected: `safe`, persisted.

2. Unsafe test URL
- Try `https://testsafebrowsing.appspot.com/`.
- Expected: blocked with unsafe message.

3. Strict mode failure policy
- Temporarily break API key or endpoint.
- With strict=true: blocked.
- With strict=false: allowed.

4. Cache behavior
- Submit same URL multiple times.
- Expected: first call from API, subsequent calls from cache until expiration.

## 13. Maintenance guidance

When editing this subsystem:
- Keep the unified return contract stable (`status`, `canPersist`, etc.).
- Do not move business decisions into DAO.
- Keep normalization deterministic and strict.
- If migrating to v5 again, validate response format (JSON vs protobuf) before switching parser logic.
- If adding logs, avoid storing sensitive data in plain text.
