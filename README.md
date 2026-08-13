# Parish Records Directory (PRD)

A lightweight, shared-hosting-friendly PHP application for building and publishing searchable record directories.

Create your own tables and columns from the admin UI, control who can view or edit them, and offer a clean public search — no frameworks, no Composer for end users. Originally built for parish and historical records, but suitable for any structured collection you want to catalogue and share (local history, membership lists, inventories, research datasets, and similar).

Built to run on ordinary PHP + MySQL/MariaDB hosting with **no Composer requirement for end users**. We ship the /vendor directory and dependencies with the app.

## Features

- Dynamic multi-table schema (create dynamic tables and columns from the admin UI).
- Role- and permission-based access (including a controllable **guest** public view).
- Public search with Ajax filters, sort, pagination, and Json and CSV export options.
- Submit update suggestions + moderation workflow
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
3. Run install/index.php.
4. Visit the site in a browser and log in with your admin account created during the install process.

**Important:** If Helping to Develop - Please never commit `/config.local.php` — it contains credentials. It is listed in `.gitignore`.

## Configuration

- **Site settings, modules, and the permission matrix:** Admin → Site Settings  
- **Public access:** grant or revoke `view_as_guest` (and related keys) on the **guest** role  
- **Modules:** turn features on/off under Settings → Modules  

## Project layout (high level)

```text
app/            Admin/User UI and actions.
  Controllers/  Handles user requests and logic.
  Models/       Database interaction logic.
  Services/     Business logic, data processing, and external handlers.
  Views/        UI templates and layouts
config/         Bla bla
api/            AJAX endpoints (search, export)
db/             Connection, auth helpers, mail, maintenance
includes/       Shared utility functions and helpers including security_engine.php light-weight firewall functionality.
install/        Installer
lang/           Languages
logs/           Json Structured Error Log - Also powers the error log within the Admin UI.
partials/       Header, nav, footer, notices, suggest edit modal.
public/         Web root containing index.php (entry point / homepage)
routes/         Contains web.php - uses Fastroot.
vendor/         Contains third party packages that ship with PRD to allow PRD to work 
```

## Security notes

-   CSRF protection on state-changing forms

- Username availability is limited to 3 in a 24 hour period from an IP address - otherwise a Username is automatically allocated.
    
-   Permission checks via a central matrix (`db/auth_helpers.php`)
    
-   Guest public access is controlled only through the **guest** role permissions
    
-   Credentials live only in `config.local.php` (not in the repo)
    

## Contributing

<p>See <a href="CONTRIBUTING.md">CONTRIBUTING.md</a>.</p>


## Licence

<p>MIT — see <a href="LICENSE">LICENSE</a>.</p>
