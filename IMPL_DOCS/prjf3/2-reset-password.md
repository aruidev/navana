Solo falta rellenar smtp_user, smtp_pass y smtp_from_email en los entornos.

**Reset-password and PHPMailer implementation summary:**

- A new reset view allows unauthenticated users to request a password reset by email.
- The controller validates the email and always responds generically for security.
- If the email exists, a selector/validator token is generated, stored, and sent with a 30-minute expiration.
- The reset link is built using a dynamic base URL from `getAppUrl()`, so it works in any local or deployed path.
- The user receives an email with the reset link; the confirmation view lets them set a new password.
- The controller validates the token, applies password rules, updates the password, and invalidates all reset tokens for that user.
- PHPMailer is integrated via a dedicated `MailService` class, which reads SMTP (Gmail) settings from the environment config files.
- The reset service now uses `MailService` to send emails instead of the native `mail()` function.
- Only one sender address is used, as required.