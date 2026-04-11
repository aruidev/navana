# Bookmarks API v1 - Technical Documentation

## 1. Purpose and Scope

This document explains how the Bookmarks API v1 works technically in the Navana project.

It is written for developers joining from zero context, and covers:

- How routes are exposed through Apache and the front controller.
- How each endpoint is resolved and executed.
- How authentication is enforced.
- How data is read and written in MySQL.
- How response payloads are built.
- How caching works, where it is stored, and where it does not apply.
- Key files and responsibilities.

The API is implemented in plain PHP (no framework), following the project MVC conventions.

## 2. High-Level Architecture

The API uses the same request pipeline as the HTML application:

1. Apache rewrites incoming clean URLs to `index.php?route=...`.
2. `index.php` acts as front controller.
3. The route string is looked up in `app/helpers/routes.php`.
4. The mapped API controller file is required.
5. The controller calls Services.
6. Services call DAOs.
7. DAOs execute prepared SQL via PDO.
8. Controllers serialize entities to JSON and return with HTTP status codes.

No framework router, no ORM, no JSON middleware stack.

## 3. URL Exposure and Routing

### 3.1 Apache Rewrite Layer

File: `.htaccess`

Relevant behavior:

- Existing files/directories are served directly.
- Unknown paths are rewritten to `index.php?route=$1`.

So this URL:

`/api/v1/bookmarks?page=1`

is internally transformed to:

`index.php?route=api/v1/bookmarks&page=1`

### 3.2 Front Controller Dispatch

File: `index.php`

`index.php`:

- Reads `$_GET['route']`.
- Normalizes dynamic API routes that include an ID:
	- `api/v1/bookmarks/{id}` -> `api/v1/bookmarks/show` + `$_GET['id']`
	- `api/v1/user/bookmarks/{id}` -> `api/v1/user/bookmarks/item` + `$_GET['id']`
- Loads route map via `navanaRoutes()`.
- Dispatches to either view or controller mapping.
- If route starts with `api/` and is not matched, returns JSON 404 via `sendJsonError(...)`.
- For non-API unknown routes, it renders HTML 404 view.

### 3.3 Route Table

File: `app/helpers/routes.php`

API v1 route keys:

- `api/v1/bookmarks` -> `app/controller/api/v1/BookmarksApiController.php`
- `api/v1/bookmarks/show` -> `app/controller/api/v1/BookmarksApiController.php`
- `api/v1/user/bookmarks` -> `app/controller/api/v1/UserBookmarksApiController.php`
- `api/v1/user/bookmarks/item` -> `app/controller/api/v1/UserBookmarksApiController.php`

Important detail: dynamic URL patterns are not declared in the route table. They are normalized in `index.php` first.

## 4. Response Layer (JSON Helpers)

File: `app/helpers/route_helpers.php`

Two reusable helpers standardize API output:

- `sendJson(array $payload, int $statusCode = 200): void`
	- Sets HTTP status.
	- Sets `Content-Type: application/json; charset=utf-8`.
	- Outputs JSON and exits.

- `sendJsonError(string $code, string $message, int $statusCode): void`
	- Returns consistent error payload:

```json
{
	"error": {
		"code": "not_found",
		"message": "Bookmark not found."
	}
}
```

This is the only error format used by the API controllers.

## 5. Authentication Model for API

Authentication is session-cookie based.

File: `app/controller/api/v1/UserBookmarksApiController.php`

Flow:

1. Controller requires `app/model/session.php`.
2. Calls `startSession()`.
3. Checks `isset($_SESSION['user_id'])`.
4. If not present, returns `401 unauthorized` JSON.

There is no JWT, no Bearer token, and no API key auth for user endpoints.

## 6. Endpoint Catalog

## 6.1 Public Endpoints

### GET `/api/v1/bookmarks`

Controller: `app/controller/api/v1/BookmarksApiController.php`

Purpose:

- Returns paginated public bookmark list (items table).

Accepted query params:

- `page` (int, min 1, default 1)
- `perPage` (allowed: 6, 12, 24; default 12)
- `term` (trimmed, max length 100)
- `order` (`ASC` or `DESC`, default `DESC`)

Response:

```json
{
	"data": [
		{
			"id": 1,
			"title": "...",
			"description": "...",
			"link": "https://...",
			"tag": "...",
			"createdAt": "...",
			"updatedAt": "...",
			"ownerUserId": 7
		}
	],
	"meta": {
		"pagination": {
			"page": 1,
			"perPage": 12,
			"total": 43,
			"totalPages": 4
		},
		"filters": {
			"term": "",
			"order": "DESC"
		}
	}
}
```

### GET `/api/v1/bookmarks/{id}`

Controller: `app/controller/api/v1/BookmarksApiController.php`

Purpose:

- Returns single bookmark by item id.

Validation:

- `id` must be positive integer.

Errors:

- `400 invalid_id`
- `404 not_found`

## 6.2 Authenticated User Endpoints

### GET `/api/v1/user/bookmarks`

Controller: `app/controller/api/v1/UserBookmarksApiController.php`

Purpose:

- Returns paginated list of bookmarks saved by current authenticated user.

Same query params and normalization rules as public list endpoint.

Errors:

- `401 unauthorized` when session is missing.

### POST `/api/v1/user/bookmarks/{id}`

Controller: `app/controller/api/v1/UserBookmarksApiController.php`

Purpose:

- Saves one bookmark for current user.

Behavior:

- Validates item existence first (`items` table).
- Writes to `saved_items` via `INSERT IGNORE`.
- Returns idempotent success shape:

```json
{
	"data": {
		"itemId": 10,
		"saved": true
	}
}
```

### DELETE `/api/v1/user/bookmarks/{id}`

Controller: `app/controller/api/v1/UserBookmarksApiController.php`

Purpose:

- Removes one bookmark from current user.

Behavior:

- Validates item existence first.
- Deletes from `saved_items` by `(user_id, item_id)`.
- Returns:

```json
{
	"data": {
		"itemId": 10,
		"saved": false
	}
}
```

## 7. Data Model and Persistence

Schema file: `db_schema/Pt05_Alex_Ruiz.sql`

Main tables used by API:

- `items`
	- Core bookmark resource.
	- Includes `id`, `title`, `description`, `link`, `tag`, timestamps, `user_id` owner.

- `saved_items`
	- User bookmark relation table.
	- Composite PK `(user_id, item_id)` prevents duplicates.
	- FK cascade removes saved rows when user/item is deleted.

## 8. Service and DAO Flow by Endpoint

## 8.1 Public List and Detail

Controller: `BookmarksApiController.php`

- For list:
	- Calls `ItemService::getItemsPaginated(...)`.
	- Service computes offset and calls DAO:
		- `ItemDAO::getPaginated(...)`
		- `ItemDAO::count(...)`

- For detail:
	- Calls `ItemService::getItemById(...)`.
	- Service delegates to `ItemDAO::getById(...)`.

## 8.2 User Saved Bookmarks

Controller: `UserBookmarksApiController.php`

- For user list:
	- Calls `SavedItemService::getSavedItemsPaginated(...)`.
	- Service computes offset and calls DAO:
		- `SavedItemDAO::getSavedPaginated(...)`
		- `SavedItemDAO::countSavedByUser(...)`

- For save:
	- Validates item exists through `ItemService::getItemById(...)`.
	- Calls `SavedItemService::saveItem(...)` -> `SavedItemDAO::save(...)` (`INSERT IGNORE`).

- For unsave:
	- Validates item exists.
	- Calls `SavedItemService::unsaveItem(...)` -> `SavedItemDAO::unsave(...)` (`DELETE`).

## 9. Serialization Contract

Both API controllers use the same local mapper `itemToApiArray(Item $item): array`.

Output fields:

- `id` (int)
- `title` (string)
- `description` (string)
- `link` (string)
- `tag` (string)
- `createdAt` (string)
- `updatedAt` (string)
- `ownerUserId` (int|null)

No additional computed fields are currently returned (for example, no `isSaved` in public list).

## 10. Validation and Error Semantics

Current API validation rules:

- HTTP method hard checks per endpoint:
	- Public controller: GET only.
	- User list: GET only.
	- User item endpoint: POST and DELETE only.

- ID validation:
	- Must be positive integer.

- Pagination/filter normalization:
	- `page >= 1`
	- `perPage` in `{6,12,24}` else fallback to `12`
	- `term` max 100 chars
	- `order` forced to `ASC` or `DESC`

Common error codes used:

- `invalid_id` (400)
- `unauthorized` (401)
- `not_found` (404)
- `method_not_allowed` (405)

Global API route miss:

- Any unmatched `api/*` route returns JSON `not_found` from front controller.

## 11. Caching: What Exists and Where

This section is important for understanding runtime behavior.

### 11.1 Bookmarks API Response Caching

There is currently no response cache layer for Bookmarks API endpoints.

- No route-level cache.
- No HTTP cache headers are explicitly set.
- No Redis or memory cache for bookmark list/detail responses.

Every API request reads from MySQL through DAO methods.

### 11.2 Safe Browsing Cache (Related but Separate)

File: `app/services/SafeBrowsingService.php`

This cache is not for API read endpoints. It is used when creating/updating items through safety checks in `ItemService`.

How it works:

- In-request cache (memory):
	- Property: `$requestCache`
	- Lifetime: current PHP request only.

- File cache (cross-request):
	- Directory: `api-cache/safe-browsing/`
	- File name: `sha256(normalizedUrl) + ".json"`
	- Stored payload:
		- `status` (`safe` or `unsafe`)
		- `threat_types` (array)
		- `expires_at` (unix timestamp)

TTL behavior:

- Default cache TTL: 21600 seconds (6 hours).
- If API returns `cacheDuration`, it is parsed and clamped.
- Minimum TTL 60 seconds, maximum 86400 seconds in parser path.

Eviction:

- On read, if `expires_at < now`, file is deleted and treated as miss.

Summary:

- Bookmarks GET endpoints: no cache.
- URL safety verification for item writes: file + request cache under `api-cache/safe-browsing`.

## 12. Key Files Map

Routing and dispatch:

- `index.php`
- `.htaccess`
- `app/helpers/routes.php`

JSON response helpers:

- `app/helpers/route_helpers.php`

API controllers:

- `app/controller/api/v1/BookmarksApiController.php`
- `app/controller/api/v1/UserBookmarksApiController.php`

Services:

- `app/services/ItemService.php`
- `app/services/SavedItemService.php`

DAOs:

- `app/model/dao/ItemDAO.php`
- `app/model/dao/SavedItemDAO.php`

Session and auth state:

- `app/model/session.php`

Database and connection:

- `db_schema/Pt05_Alex_Ruiz.sql`
- `app/model/connection.php`

Config bootstrap:

- `bootstrap.php`
- `environments/env.local.php` (active by default through `ENV_NAME`)

## 13. End-to-End Sequence Examples

### 13.1 GET `/api/v1/bookmarks/42`

1. Apache rewrites path to `index.php?route=api/v1/bookmarks/42`.
2. `index.php` maps dynamic route to `api/v1/bookmarks/show`, sets `$_GET['id']=42`.
3. Route table resolves to `BookmarksApiController.php`.
4. Controller validates GET + id.
5. Controller calls `ItemService::getItemById(42)`.
6. Service calls `ItemDAO::getById(42)` via PDO prepared statement.
7. Entity is converted to API array.
8. `sendJson(...)` returns 200 with JSON.

### 13.2 POST `/api/v1/user/bookmarks/42`

1. Apache rewrites to route query.
2. `index.php` maps to `api/v1/user/bookmarks/item` and injects `id`.
3. Route table resolves to `UserBookmarksApiController.php`.
4. Controller starts session and checks `$_SESSION['user_id']`.
5. Controller validates item id and confirms item exists.
6. Calls `SavedItemService::saveItem(userId, 42)`.
7. DAO executes `INSERT IGNORE INTO saved_items (user_id, item_id)`.
8. Returns `{ data: { itemId: 42, saved: true } }`.

## 14. Current Constraints and Design Decisions

Implemented on purpose for v1 simplicity:

- Session auth only for user endpoints.
- No API tokens.
- No OpenAPI schema file yet.
- No bookmark response caching layer.
- Deterministic defaults for paging and sorting.
- Explicit JSON error code strings for client-side handling.

These constraints keep the implementation short, readable, and aligned with the project architecture.
