# Update Account Details

## Update Username

- A handler was added in `app/controller/UserController.php` that receives `POST change_username`, validates active session, sanitizes the new username, applies basic validations (not empty, different from the current one), and checks uniqueness via `UserService::usernameExists`. Uses flash for success or error and redirects back to Account Settings.
- `UserService::changeUsername` already existed; it is now used from the controller to perform the update and refresh `$_SESSION['username']` when successful.
- The view `app/view/account-settings.php` now displays the current username, includes a form to change it, and preserves the entered value when there are errors. It renders validation errors in a block and continues to show existing toasts for quick feedback.
- Protection for unauthenticated users is maintained: Account Settings shows a message and back/login links if there is no session.

---

## Update Email

- Implemented following the update/change username pattern.