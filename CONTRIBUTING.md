# Contributing to Parish Records Directory (PRD)

Thanks for your interest in contributing.

## Goals of this project

- Stay **lightweight** and runnable on ordinary shared hosting  

- **No Composer required for end users** (dev tooling is fine for contributors)  
- Prefer clear, straight-forward PHP over heavy frameworks  
- Keep the **permission matrix** as the single source of truth for access control  

## Getting started (development)

1. Please fork and clone the repository.
2. Please copy `db/db.php.example` → `db/db.php` and set local database credentials.
3. Please create/import a development database.
4. Please point a local web server (or PHP built-in server) at the project root.

    ```bash
    php -S localhost:8080
    ```

Please log in as an admin and confirm the permission matrix and modules look right.

## What not to commit

- `db/db.php` (credentials)

- Real SMTP passwords or production secrets
- Vendor trees that end users should not need (unless explicitly agreed)

## Coding norms

- **Permissions:** Please use `has_permission()`, `guest_has_permission()`, or `require_permission()` / `require_admin_page()`. Please do not add new hardcoded role-name checks if a permission key exists.

- **Modules:** new optional features should respect `is_module_enabled($pdo, 'module_key')`.
- **Public vs admin:** public pages must work for guests when the guest role has the relevant permission; Please do not force login unless the feature is intentionally authenticated-only.
- **CSRF:** Please use `csrf_field()` on forms and `verify_csrf_token()` on POST handlers.
- **Output:** Please escape with `htmlspecialchars` (or existing helpers) for user-facing content.
- We prefer full, readable files over deep abstraction. Small shared helpers are welcome when the same logic appears in several places.

## Adding a feature (checklist)

- Please decide whether it needs a module toggle and/or new permission keys.

- Please register permission keys via the existing helpers (they auto-appear in Settings).
- Please wire nav links only when the user has the right permission (and module is on).
- Please keep action scripts thin; put reusable logic in `includes/functions.php` or `db/auth_helpers.php` as appropriate.
- If you touch public search/AJAX, please keep the JSON contract in `api/search.php` stable or document the change.

## Pull requests

- Please describe what changed and why.

- Please note any new permission keys or module settings.
- Please call out any migration / schema steps.
- Please keep PRs focused where possible.

## Questions

Please Open an issue for design discussion before large refactors (framework migrations, major schema changes, etc.).

## Thank You

A very big thank you for your time! Happy coding!
