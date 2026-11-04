# Shelfwise Library Management System

A responsive admin library app built with core PHP, MySQL, Bootstrap 5, and Bootstrap Icons. No framework or Composer dependencies are needed.

## Requirements

- XAMPP with Apache, PHP 7.4+ and MySQL/MariaDB
- Modern browser and internet access for Bootstrap CDN assets

## Run with XAMPP

1. Copy the `library` folder into `C:\xampp\htdocs\`.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin`, select **Import**, choose `database/library.sql`, and import it. This creates the `library_management` database and tables.
4. Check `includes/db.php`. Defaults are `localhost`, user `root`, and a blank password. Change these if your local MySQL credentials differ.
5. Open `http://localhost/library/admin/setup_admin.php` and create the first administrator. This setup page is disabled automatically once an admin exists.
6. Sign in at `http://localhost/library/admin/login.php`.

## Features

- Protected admin session, password hashing, CSRF-protected forms, prepared statements, and escaped output
- Dashboard metrics for total books, students, active and returned loans, and available copies
- Add, edit, search, list, and delete books; edit updates inventory safely against active loans
- Add, edit, list, and delete students
- Transaction-safe issue and return actions that update available inventory
- Issued, returned, and overdue reports
- Responsive off-canvas sidebar and mobile-friendly Bootstrap layout

## Notes

- Books and students referenced by loan history cannot be deleted because foreign-key constraints preserve the records.
- To reset a local test install, drop `library_management` in phpMyAdmin and import the SQL file again.
- For production, enable HTTPS, use a strong database password, and configure PHP session security.
