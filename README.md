# Parish Records Directory (PRD)

A lightweight, shared-hosting-friendly PHP application for building and publishing searchable record directories.

Create your own tables and columns from the admin UI, control who can view or edit them, and offer a clean public search — no frameworks, no Composer for end users. Originally built for parish and historical records, but suitable for any structured collection you want to catalogue and share (local history, membership lists, inventories, research datasets, and similar).

Built to run on ordinary PHP + MySQL/MariaDB hosting with **no Composer requirement for end users**.

## Features

- Dynamic multi-table schema (create tables and columns from the admin UI)
- Role- and permission-based access (including a controllable **guest** public view)
- Public search with filters, sort, pagination, and CSV export
- Edit suggestions + moderation workflow
- Volunteer interest and feedback portals (module-togglable)
- Leaderboard / points (optional)
- 2FA, onboarding, audit logs
- Maintenance mode and site notices

## Requirements

- PHP 8.0+ (with PDO MySQL)
- MySQL or MariaDB
- Apache or Nginx (Apache `.htaccess` included)

## Installation (manual)

1. Upload the project files to your web space.
2. Create an empty MySQL/MariaDB database and a user with full rights on it.
3. Copy `db/db.php.example` to `db/db.php` and edit the database credentials.
4. Import your schema / run any provided SQL migrations (or use the forthcoming installer).
5. Visit the site in a browser and log in with your admin account.

**Important:** Never commit `db/db.php` — it contains credentials. It is listed in `.gitignore`.

## Configuration

- **Site settings, modules, and the permission matrix:** Admin → Site Settings  
- **Public access:** grant or revoke `view_as_guest` (and related keys) on the **guest** role  
- **Modules:** turn features on/off under Settings → Modules  

## Project layout (high level)

```text
admin/          Admin UI and actions
api/            AJAX endpoints (search, export)
assets/         CSS and front-end static files
db/             Connection, auth helpers, mail, maintenance
includes/       Shared utility functions
partials/       Header, nav, footer, notices
user/           Login, profile, data entry, 2FA, etc.
actions/        Public form handlers
```

## Security notes

-   CSRF protection on state-changing forms
    
-   Permission checks via a central matrix (`db/auth_helpers.php`)
    
-   Guest public access is controlled only through the **guest** role permissions
    
-   Credentials live only in `db/db.php` (not in the repo)
    

## Contributing

<p>See <a href="CONTRIBUTING.md">CONTRIBUTING.md</a>.</p>


## Licence

<p>MIT — see <a href="LICENSE">LICENSE</a>.</p>
