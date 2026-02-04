# How to Publish Your Website

This guide will walk you through the steps to take your local XAMPP website online.

## 1. Get a Domain and Hosting
You need a place to host your files. Common providers include:
- **Bluehost, HostGator, SiteGround** (Good for PHP/MySQL).
- **Namecheap** (Good domain prices).

**Requirements for Hosting:**
- PHP 8.0 or higher
- MySQL Database support
- phpMyAdmin (for easy database management)

## 2. Prepare Your Database
1.  Open **phpMyAdmin** on your local computer (usually `http://localhost/phpmyadmin`).
2.  Select your database (`portfolio_db`).
3.  Click the **Export** tab.
4.  Keep the default settings (Quick, SQL format) and click **Export**.
5.  This will download a `.sql` file (e.g., `portfolio_db.sql`).

## 3. Upload Your Files
1.  Log in to your hosting Control Panel (cPanel).
2.  Go to **File Manager**.
3.  Navigate to `public_html` (this is the root folder for your domain).
4.  **Upload** all your project files **EXCEPT**:
    - `.git` folder
    - `.gitignore`
    - `migration_*.sql` files (unless needed)
    - `setup_*.php` files (security risk if left public)
5.  **Important:** You DO need to upload `config.php`, but you will edit it in the next step.

## 4. Set Up the Live Database
1.  In your hosting cPanel, go to **MySQL Databases**.
2.  **Create a new database** (name will likely look like `yourname_portfolio_db`).
3.  **Create a new user** and generate a strong password.
4.  **Add the user to the database** and give them "All Privileges".
5.  Go to **phpMyAdmin** in your cPanel.
6.  Select your new empty database.
7.  Click **Import**, choose the `.sql` file you exported in step 2, and click **Go**.

## 5. Configure the Live Site
1.  In File Manager, find `config.php`.
2.  Right-click and **Edit**.
3.  Update the values with your **LIVE** database details:
    ```php
    define('DB_HOST', 'localhost'); // Usually stays localhost, but check host instructions
    define('DB_NAME', 'yourserver_portfolio_db');
    define('DB_USER', 'yourserver_user');
    define('DB_PASS', 'your_strong_password');
    
    // Update the Base URL
    define('BASE_URL', 'https://yourdomain.com/');
    
    // Turn off errors for security
    error_reporting(0);
    ini_set('display_errors', 0);
    ```
4.  Save the file.

## 6. Test Your Site
Visit your domain (e.g., `https://yourdomain.com`). Your site should be live!

### Troubleshooting
- **Database Error?** Check your `DB_NAME`, `DB_USER`, and `DB_PASS` in `config.php`.
- **Images missing?** Check if you uploaded the `uploads` folder.
- **404 Errors?** Ensure `.htaccess` was uploaded. It is a hidden file, so make sure "Show Hidden Files" is enabled in cPanel.
