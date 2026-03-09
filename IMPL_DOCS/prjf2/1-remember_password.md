
### “Remember me” Implementation with Token

**Involved files**
- RememberMeService.php: issues tokens (`issueToken`), validates/consumes (`consumeToken`), clears by user (`clearUserTokens`), and expires (`clearExpired`).
- RememberMeTokenDAO.php: token persistence (create, find by selector, delete by selector, delete by user, delete expired).
- session.php: session startup; if there is no user in session and a `remember_me` cookie exists, tries `consumeToken`, restores `$_SESSION['user_id']`, rotates the token by issuing a new one and renews the cookie; deletes the cookie if it fails. Requires `RememberMeService`.
- UserController.php: on successful login, if `remember_me` is checked, calls `issueToken` and sets the `remember_me` cookie; if not, clears the cookie. On logout, clears the user's tokens with `clearUserTokens` and deletes the cookie.
- `db_schema/remember_me_tokens.sql`: `remember_me_tokens` table with `user_id`, `selector`, `validator_hash`, `expires_at` (FK to `users`).

**Flow**
1) Login with checkbox checked: `UserController` creates a token via `RememberMeService::issueToken`, saves it in the table, and sets a `selector:validator` cookie with expiration, `httponly`, `samesite=Lax`.
2) Subsequent access without session: `session.php` reads the cookie, uses `RememberMeService::consumeToken` to validate selector/validator and expiration; if valid, restores `$_SESSION['user_id']`, rotates the token (deletes the used one, issues a new one), and renews the cookie; if it fails, deletes the cookie.
3) Logout: `UserController` deletes the user's tokens (`clearUserTokens`) and removes the cookie.
4) Maintenance: `RememberMeService::clearExpired` purges expired tokens. In this project, it is called at the start of each session from `session.php` (once per request).

**Applied security**
- Token split into `selector` (for lookup) and `validator` (only SHA-256 hash in DB).
- Comparison with `hash_equals`, invalidates on mismatch or expiration.
- Rotation after use; deletion on logout.
- Cookie `httponly`, `samesite=Lax` (use `secure=true` on HTTPS).