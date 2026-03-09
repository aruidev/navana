
## reCAPTCHA v2 (checkbox)

- **When it appears**: after 3 failed login attempts within ≤15 min. The counter resets when the window expires or after a successful login.
- **View**: the form displays the reCAPTCHA v2 (checkbox) widget and loads the official script only when necessary.
- **Flow**:
	1) User submits login; if the threshold is exceeded, they must check the CAPTCHA.
	2) The CAPTCHA token is validated server-side against Google's API.
	3) If validation fails, login is blocked and the counter increases; if it passes, credentials are checked.
	4) On successful login, the counter resets and the session/remember-me proceeds.
- **Configuration**: keys `recaptcha_site_key` and `recaptcha_secret_key` in `environments/env.php` and `environments/env.prod.php`.
- **Goal**: mitigate simple brute force by requiring human interaction after several failures, while keeping zero friction for legitimate users.

### Technical details
- **Attempt control**: helpers in `app/model/session.php` (`incrementLoginAttempts`, `resetLoginAttempts`, `isLoginCaptchaRequired`) store the counter and timestamp in `$_SESSION` with a 15-minute TTL.
- **CAPTCHA validation**: class `app/model/services/RecaptchaService.php` sends the token to Google's `siteverify` API using the environment's `recaptcha_secret_key`.
- **Control**: `app/controller/UserController.php#L33-L123` applies the logic: if the threshold is exceeded, requires a token and validates it; on failure, increments attempts and shows error; on success, resets attempts and continues with session/remember-me.
- **Presentation**: `app/view/login.php#L1-L75` decides whether to show the widget based on `isLoginCaptchaRequired()` and loads Google's script only then; also displays the CAPTCHA error message.
- **Configuration**: keys declared in `environments/env.php` and `environments/env.prod.php`; read in login to render the widget and in the controller to verify tokens.
