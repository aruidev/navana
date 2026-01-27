### Implementación “Remember me” con token

**Archivos involucrados**
- RememberMeService.php: emite tokens (`issueToken`), valida/consume (`consumeToken`), limpia por usuario (`clearUserTokens`) y expira (`clearExpired`).
- RememberMeTokenDAO.php: persistencia de tokens (crear, buscar por selector, borrar por selector, borrar por usuario, borrar expirados).
- session.php: arranque de sesión; si no hay usuario en sesión y existe cookie `remember_me`, intenta `consumeToken`, restaura `$_SESSION['user_id']`, rota token emitiendo uno nuevo y renueva la cookie; borra cookie si falla. Requiere `RememberMeService`.
- UserController.php: en login exitoso, si `remember_me` está marcado, llama a `issueToken` y setea la cookie `remember_me`; si no, limpia la cookie. En logout, limpia tokens del usuario con `clearUserTokens` y elimina la cookie.
- `db_schema/remember_me_tokens.sql`: tabla `remember_me_tokens` con `user_id`, `selector`, `validator_hash`, `expires_at` (FK a `users`).

**Flujo**
1) Login con checkbox marcado: `UserController` crea token vía `RememberMeService::issueToken`, guarda en tabla y setea cookie `selector:validator` con expiración, `httponly`, `samesite=Lax`.
2) Acceso posterior sin sesión: `session.php` lee la cookie, usa `RememberMeService::consumeToken` para validar selector/validator y caducidad; si es válido, restaura `$_SESSION['user_id']`, rota token (borra el usado, emite uno nuevo) y renueva cookie; si falla, borra cookie.
3) Logout: `UserController` borra tokens del usuario (`clearUserTokens`) y elimina cookie.
4) Mantenimiento: `RememberMeService::clearExpired` purga tokens vencidos. En este proyecto se llama al inicio de cada sesión desde `session.php` (una vez por request).

**Seguridad aplicada**
- Token dividido en `selector` (para lookup) y `validator` (solo hash SHA-256 en DB).
- Comparación con `hash_equals`, invalida en mismatch o expirado.
- Rotación tras uso; borrado al cerrar sesión.
- Cookie `httponly`, `samesite=Lax` (usar `secure=true` en HTTPS).