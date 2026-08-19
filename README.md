# Parish Records Directory (pRD)

A lightweight, shared-hosting-friendly modern PHP MVC application, transcribed into over 30 languages, for building and publishing searchable and exportable public or private record directories with granular collaborative roles ACL management and gamification.

You can create your own tables and columns from the admin UI, control who can contribute, edit and view them, — no frameworks and no PHP Composer or Terminal Access required for end users. Originally built for parish and historical records, but suitable for any structured collection you want to catalogue and share (local history, membership lists, inventories, research datasets, and similar).

Built to run on ordinary PHP + MySQL/MariaDB hosting with **no Composer requirement for end users**. We ship the /vendor directory and dependencies with the app, particularly to assist people who wish to contribute to the codebase.

---

## Features

- Dynamic multi-table schema (create dynamic tables and columns from the admin UI).
- Role- and permission-based access (including a controllable **guest** public view).
- Public search with Ajax filters, sort, pagination, and Json and CSV export options.
- Submit update suggestions + moderation workflow
- Volunteer interest and feedback portals (module-togglable)
- Leaderboard / points (optional)
- 2FA, onboarding, audit logs
- Maintenance mode and site notices
- Transcribed into 30+ Languages including representation for some of the poorest countries across the world.
---

## Requirements

- PHP 8.0+ (with PDO MySQL)
- MySQL or MariaDB
- Apache or Nginx (Apache `.htaccess` included)

---

## Installation (manual)

1. Please upload the project files to your web space.
2. Please create an empty MySQL/MariaDB database and a user with full rights on it.
3. Please run install/index.php.
4. Please visit the site in a browser and log in with your admin account created during the install process.

**Important:** If Helping to Develop - Please never commit `/config.local.php` — it contains credentials. It is listed in `.gitignore`.

---

## Configuration

- **Site settings, modules, and the permission matrix:** can be found by going to Admin → Site Settings  
- **Public access:** you can grant or revoke `view_as_guest` (and related keys) on the **guest** role  
- **Modules:** you can turn features on/off under Settings → Modules  

---

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
  

## Emergency database updates (cannot log in)

If a bad migration or config issue blocks login, but PHP can still reach MySQL:

1. On the server, please create an empty flag file:

   `touch db/ALLOW_EMERGENCY_MIGRATE`

   (or create empty file `db/ALLOW_EMERGENCY_MIGRATE` via FTP/cPanel)

2. Please visit: https://your-site.example/update-database
   (include any subfolder, e.g. /projects/prd/update-database)

3. Please run pending migrations from that screen.

4. When finished, please remove the flag (UI button if shown, or delete the file):

   `rm db/ALLOW_EMERGENCY_MIGRATE`

Please do not leave this file in place on a public server. It allows
unauthenticated access to the migrator only while the file exists.

**Please Never commit it to git**.
    

## Contributing

<p>Please see <a href="CONTRIBUTING.md">CONTRIBUTING.md</a>.</p>


## Licence

<p>MIT — Please see <a href="LICENSE">LICENSE</a>.</p>
