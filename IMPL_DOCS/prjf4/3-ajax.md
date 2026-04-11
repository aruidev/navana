# 3. AJAX Draft Persistence for Bookmark Forms

## Goal

The objective of this implementation is very specific:

- Prevent data loss when the user refreshes the page while writing.
- Keep the existing server flow for Save and Update unchanged.
- Use a simple, maintainable client-side solution.

This means we are not sending the full form via AJAX to the backend. Instead, we use JavaScript to persist a temporary draft locally while the user types.

## Where the solution is connected

### 1) Script loading (global)

The draft script is loaded once from the shared layout header:

- `app/view/layout/header.php`

It is included with `defer`, so HTML parsing is not blocked and initialization runs when the document is ready.

### 2) Form registration (opt-in)

Only forms that declare draft attributes are managed:

- `app/view/form_insert.php`
- `app/view/form_update.php`

Each form includes:

- `data-draft-form="bookmark"`
- `data-draft-key="..."`

This makes the script generic and reusable, while still controlling exactly which forms are tracked.

### 3) Draft engine

Core logic is in:

- `app/view/js/form-draft.js`

## Technical flow

### Step A: Safe startup

On page load, the script checks whether `localStorage` is available with a small write/remove probe.

- If storage is available: continue.
- If not available (private mode restrictions, disabled storage, etc.): silently do nothing.

This guarantees no runtime errors and no impact on normal form usage.

### Step B: Find eligible forms

The script selects forms with:

`form[data-draft-form="bookmark"][data-draft-key]`

Only those forms are initialized, so unrelated pages are ignored.

### Step C: Restore existing draft

For each eligible form:

1. Read `data-draft-key`.
2. Read JSON string from `localStorage` using that key.
3. Parse safely with try/catch.
4. Restore known fields if present:
	 - title
	 - tag
	 - description
	 - link

If JSON is invalid or missing, the script skips restore safely.

### Step D: Save while typing (debounced)

The script listens to `input` and `change` events.

To avoid excessive writes, it wraps persistence in a debounce of 400 ms:

- many keystrokes in a short period -> one save operation

This reduces storage churn and keeps performance smooth.

### Step E: Payload strategy

Each write stores only relevant fields in a compact JSON object.

Example logical shape:

{
	"title": "...",
	"tag": "...",
	"description": "...",
	"link": "..."
}

If all tracked fields are empty/whitespace, the key is removed from `localStorage` instead of saving empty data.

## Key design decisions

## 1) No backend changes required

There is no new endpoint and no controller/service refactor.

Benefits:

- Very low implementation risk
- Easy rollback
- No API contract changes

## 2) Per-form key isolation

Different forms use different keys to avoid data collision:

- Insert form: fixed key `navana:draft:add`
- Update form: scoped key `navana:draft:update:{itemId}`

This prevents one item draft from overwriting another item draft.

## 3) Progressive enhancement

If JavaScript fails or is disabled:

- Forms still submit normally via existing POST actions.
- User experience degrades gracefully, functionality remains intact.

## 4) Simplicity over complexity

The implementation uses:

- Vanilla JavaScript
- One small shared file
- HTML data attributes as configuration

No framework, no build step, no dependency overhead.

## What this solution does not do (by design)

- It does not submit data via AJAX.
- It does not validate on the client.
- It does not sync drafts across devices.
- It does not persist to database.

It is intentionally limited to local draft persistence in the browser.

## Performance and maintainability notes

- `defer` script loading avoids render blocking.
- Debounce avoids writing on every keystroke.
- Small fixed field list keeps logic predictable.
- Data attributes avoid hardcoded page-specific conditions.

Overall complexity stays low and aligned with the project principles: readability and simplicity.

## Security and privacy notes

- Draft data is stored in browser local storage, not on server.
- Any user with browser access on the same machine could inspect that data.
- Do not store secrets in these fields.

For bookmark metadata (title, description, URL, tag), this tradeoff is usually acceptable.

## Current file map

- `app/view/layout/header.php`: global script include (deferred)
- `app/view/form_insert.php`: draft attributes for Add form
- `app/view/form_update.php`: draft attributes for Edit form
- `app/view/js/form-draft.js`: autosave/restore engine

## Result

Users can refresh Add/Edit pages without losing in-progress text, while the existing Save/Update backend flow remains exactly the same.
