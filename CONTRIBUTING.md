# Contributing to Parish Records Directory (PRD)

Thanks for your interest in contributing.

## Goals of this project

- We aim to Stay **lightweight** and runnable on ordinary shared hosting  

- **No Composer required for end users** (dev tooling is of course fine for contributors)  
- Clear, straight-forward modern MVC PHP over heavy frameworks  
- Please keep the **permission matrix** as the single source of truth for access control  

## Getting started (development)

1. Please fork and clone the repository.
2. Please copy `config.local.example` → `config.local.php` and set local database credentials.
3. Please create/import a development database.
4. Please point a local web server (or PHP built-in server) at the project root.

    ```bash
    php -S localhost:8080
    ```

Please log in as an admin and confirm the permission matrix and modules look right.

## What not to commit

- `config.local.php` (credentials)

- Please Don't Commit Real SMTP passwords or production secrets

## Coding norms

- **Permissions:** Please use `has_permission()`, `guest_has_permission()`, or `require_permission()` / `require_admin_page()`. Please do not add new hardcoded role-name checks if a permission key exists.

- **Modules:** new optional features should respect `is_module_enabled($pdo, 'module_key')`.
- **Public vs admin:** public pages must work for guests when the guest role has the relevant permission; Please do not force login unless the feature is intentionally authenticated-only.
- **CSRF:** Please use `csrf_field()` on forms and `verify_csrf_token()` on POST handlers. Please make full use of functionality within `includes/security_engine.php`
- **Output:** Please escape with `htmlspecialchars` (or existing helpers) for user-facing content.
- **MVC Compliance** We are Committed to MVC compliant code with pragmatism - shared helpers are very welcome when the same logic appears in several places. We aim to be predictable and as as helpful as we can to each other.
- **Language** - We use dot notification and a `__` function and are highly committed to supporting speakers of languages including in third-world countries where we dream of this solution playing it's part in changing lives. Please ensure that our project is inclusive.
- **Accessibility** - We aim for the highest standards of inclusion for those with disabilities - particularly those who rely on screen readers. Please ensure full aria compliance and anything else you can do to level the playing field for those with disabilities.

## Adding a feature (checklist)

- Please decide whether a new feature needs a module toggle and/or new permission keys.
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

A very big thank you for your time and dedication! Happy coding!
