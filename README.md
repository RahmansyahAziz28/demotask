# Gotham Crime Records
### Gotham City Police Department — Criminal Case Management System

A PHP Native CRUD web application for managing criminal cases, suspects, and investigators. Built as a college assignment using PHP, PostgreSQL, and plain CSS with a dark Gotham City police aesthetic.

---

## Requirements

- PHP 8.0 or higher
- PHP extension: `pdo`, `pdo_pgsql`
- PostgreSQL 13 or higher

---

## Setup Instructions

### 1. Create the PostgreSQL Database

Log into PostgreSQL and create the database:

```bash
psql -U postgres
```

```sql
CREATE DATABASE gotham_crime_records;
\q
```

### 2. Import the Schema and Sample Data

```bash
psql -U postgres -d gotham_crime_records -f database.sql
```

Or using a full path:

```bash
psql -U postgres -d gotham_crime_records -f /path/to/gotham-crime-records/database.sql
```

### 3. Configure Database Credentials

Open `config/database.php` and update the connection variables:

```php
$db_host = 'localhost';
$db_port = '5432';
$db_name = 'gotham_crime_records';
$db_user = 'postgres';
$db_pass = 'your_password_here';
```

**Or** set environment variables (recommended for production):

```bash
export DB_HOST=localhost
export DB_PORT=5432
export DB_NAME=gotham_crime_records
export DB_USER=postgres
export DB_PASS=your_password
```

### 4. Run Locally Using PHP's Built-in Server

From the project root directory:

```bash
cd gotham-crime-records
php -S localhost:8000
```

Then open your browser at: **http://localhost:8000**

---

## Project Structure

```
gotham-crime-records/
├── config/
│   └── database.php          # PDO PostgreSQL connection
├── includes/
│   ├── header.php            # HTML head + topbar
│   ├── sidebar.php           # Navigation sidebar
│   └── footer.php            # Closing HTML + JS
├── cases/
│   ├── index.php             # List all cases (+ search/filter)
│   ├── create.php            # Create new case
│   ├── show.php              # View case details + suspects
│   ├── edit.php              # Edit case
│   └── delete.php            # Delete case (with FK check)
├── suspects/
│   ├── index.php             # List all suspects (+ search)
│   ├── create.php            # Add suspect
│   ├── show.php              # View suspect + linked case
│   ├── edit.php              # Edit suspect
│   └── delete.php            # Delete suspect
├── investigators/
│   ├── index.php             # List all investigators (+ search)
│   ├── create.php            # Add investigator
│   ├── show.php              # View investigator + assigned cases
│   ├── edit.php              # Edit investigator
│   └── delete.php            # Delete investigator (with FK check)
├── assets/
│   └── css/
│       └── style.css         # Gotham-themed dark CSS
├── database.sql              # PostgreSQL schema + sample data
├── index.php                 # Dashboard homepage
└── README.md
```

---

## Features

- **Dashboard** — Summary statistics (total cases, suspects, investigators; breakdown by status) and recent case feed
- **Case Management** — Full CRUD with status badges, investigator assignment dropdown, search, and status filter
- **Suspect Management** — Full CRUD with case linking, search across name/ID/case number
- **Investigator Management** — Full CRUD with case count display and assigned cases list
- **Detail Views** — Each entity shows related records (cases show suspects and investigator; investigators show their cases; suspects show their linked case)
- **Safe Deletion** — Foreign key constraints prevent deleting investigators with active cases, or cases with linked suspects; friendly error messages displayed
- **Flash Messages** — Success/error feedback after all operations (auto-dismiss after 4 seconds)
- **Server-side Validation** — All required fields validated before database insert/update
- **SQL Injection Prevention** — All queries use PDO prepared statements with bound parameters
- **XSS Prevention** — All displayed user input escaped with `htmlspecialchars()`
- **Responsive** — Mobile-friendly layout with collapsible sidebar

---

## Deploying to a PHP Hosting Provider

1. Ensure your host supports **PHP 8.0+** and has **`pdo_pgsql`** enabled (check `phpinfo()`)
2. Create a PostgreSQL database through your host's control panel
3. Import `database.sql` via phpPgAdmin or `psql`
4. Upload all project files via FTP/SFTP
5. Edit `config/database.php` with your hosting database credentials
6. Set your document root to the `gotham-crime-records/` folder

---

## Checking Required PHP Extensions

Run this in a temporary PHP file:

```php
<?php
echo extension_loaded('pdo')      ? 'PDO: OK' : 'PDO: MISSING';
echo '<br>';
echo extension_loaded('pdo_pgsql') ? 'PDO_PGSQL: OK' : 'PDO_PGSQL: MISSING';
```

---

## Sample Data Included

The `database.sql` file seeds the database with:
- **5 investigators** (James Gordon, Harvey Bullock, Renee Montoya, Crispus Allen, Sarah Essen)
- **8 criminal cases** (various Gotham City crime types and statuses)
- **8 suspects** (Gotham's most wanted, linked to their respective cases)

---

*Gotham City Police Department — Internal Records System v1.0*
