# Mama Ntilie — Local Food Ordering & Vendor Management

A PHP + MySQL learning project for connecting customers with food vendors in Kigamboni, Tanzania. It includes customer accounts, vendor accounts, meal listings, stock-aware ordering, order status updates, expense tracking and a simple daily business report.

## Built with
- PHP 8+
- MySQL / MariaDB
- HTML5 and CSS3
- MySQLi prepared statements

## Run locally with XAMPP
1. Install and open XAMPP. Start **Apache** and **MySQL**.
2. Copy this entire `mama-ntilie` folder into `C:\xampp\htdocs\`.
3. Open phpMyAdmin at `http://localhost/phpmyadmin`.
4. This project uses a separate database named `food_ordering_db`. In phpMyAdmin, open the Import tab from the home page (do not select your old database first), choose `schema.sql`, then click Go. The SQL file creates `food_ordering_db` and its tables. Your existing databases are left untouched.
5. Check `config.php`. The defaults match a typical local XAMPP installation (`root`, blank password). If your MySQL credentials differ, update them.
6. Visit `http://localhost/mama-ntilie/`.
7. Register one account as **Food vendor** and another as **Customer**. Sign in as the vendor first and add meals; then sign in as the customer to place an order.

## Important project decisions
- Vendor accounts are stored in `users` with `role='vendor'`; the project does not need a separate `vendors` table.
- Passwords are stored using PHP `password_hash()` and checked with `password_verify()`.
- Database writes use prepared statements. Ordering uses a database transaction and locks the selected meal row before reducing stock.
- Forms that change data use a session CSRF token.
- Menu image uploads accept JPG, PNG and WEBP up to 2 MB. Uploaded files go into `uploads/`.
- Vendor registration is open for this learning/demo version. Before a public launch, add vendor approval and stronger operational controls.

## GitHub
Create a new empty repository on GitHub, then from this folder run:

```bash
git init
git add .
git commit -m "Build Mama Ntilie food ordering system"
git branch -M main
git remote add origin https://github.com/YOUR-USERNAME/mama-ntilie.git
git push -u origin main
```

Replace `YOUR-USERNAME` with your GitHub username. Do not upload real customer data, passwords, production credentials, or private database backups.

## Current scope / next improvements
This is a solid local-development foundation, not a production payment or delivery platform. Online payments, delivery assignment, customer notifications, vendor verification, audit logs, automated tests and deployment configuration are not implemented yet. Add these deliberately as separate features.
