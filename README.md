# HuGe Financial Technologies - Applied Web Project Part 2

COS10026 Web Technology Project, Semester 2, 2026
**Group:** The Big G
|**Members** | **ID** |
|---|---|
| Sinhak Lor | 106637569 |
| Chandara Yoeun | 106195915 |
| Marco Tan | 102838708 |
| Jacob Marullu | 106511359 | 

A recruitment website for HuGe Financial Technologies, extended from a static
site (Part 1) into a dynamic PHP and MySQL application.

## How to run the site

1. Install and start a local server with PHP and MySQL (for example XAMPP).
2. Copy this folder into the server's web folder (for XAMPP: `htdocs/project2`).
3. In phpMyAdmin, create a database named `huge_db`.
4. Import `huge_db.sql` into that database (it contains every table and the sample data).
5. Open `http://localhost/project2/index.php`.

Database settings are in `settings.php`: host `localhost`, user `root`, no password.

## Manager login (for the marker)

- Page: `http://localhost/project2/login.php`
- Username: `admin`
- Password: `admin`

## What each page does

| File | Purpose |
|---|---|
| `index.php` | Home page |
| `jobs.php` | Job listings loaded from the `jobs` table, with a search bar |
| `search.php` | Job search results from the database |
| `apply.php` | Job application form |
| `process_eoi.php` | Validates the form on the server and saves the Expression of Interest |
| `about.php` | About page and team contributions loaded from the database |
| `login.php`, `logout.php` | Manager login and logout |
| `manage.php` | HR manager page (login required) |
| `header.inc`, `nav.inc`, `footer.inc` | Shared page parts included on every page |
| `settings.php` | Database connection settings |
| `auth.inc`, `session.inc` | Login check and session/security helpers |

## Database tables

`eoi`, `jobs`, `users`, [about tables, e.g. `about_info`, `team_members`]

## Security features

- Server-side validation and sanitising of all form input
- Prepared statements for database queries
- Passwords stored hashed (`password_hash` / `password_verify`)
- Login-protected manager page, with CSRF tokens on its forms
- Output escaped with `htmlspecialchars`

## Acknowledgements

**GenAI use:** The code for this project was primarily generated with Claude (Anthropic). Group members described what each page or feature needed, Claude wrote the code (including the HTML, CSS, PHP, SQL and the validation and login logic), and members ran it locally, tested it in the browser and database, and asked Claude to fix bugs and make changes. Claude also explained how the code works. Example prompts included "make the header a burger menu when the screen gets small" and "write process_eoi.php that validates the form on the server and stores it in an eoi table".

## Links

- GitHub: https://github.com/106637569/HuGe_Part-2
- Jira: https://loolsinhak.atlassian.net/jira/software/projects/HUGE/boards/36?filter=&groupBy=none&atlOrigin=eyJpIjoiY2U0YTI4OTQyZjMxNDU3YTk3YmVjM2NkNTJiMGJhMjkiLCJwIjoiaiJ9
