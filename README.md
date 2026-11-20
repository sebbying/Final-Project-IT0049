# Counterpoint POS · IT0049 Midterm Project

A CodeIgniter **4.7.6** point-of-sale application for PHP **8.2+**, MySQL/MariaDB, and InfinityFree shared hosting. Prices use Philippine pesos and displayed dates use Asia/Manila.

**Start here:** complete the local setup below, then follow [GitHub setup](docs/GITHUB.md) and [InfinityFree deployment](docs/INFINITYFREE.md).

## What is included

- Product list, search, create, edit, and archive; stock and price validation; product image upload.
- Customer create, read, update, and delete.
- Staff create, read, update, and delete; prepared avatars; hashed passwords; optional password change.
- Staff authentication, logout, CSRF protection, login throttling, and authentication on every management and image route.
- A sale form with a product, optional customer, quantity, server-calculated total, and automatic stock deduction.
- Clear insufficient-stock messages; a failed sale rolls back its database changes.
- Sales history with product, customer or Walk-in, staff, quantity, total, and date.
- An overview page, responsive screens, pagination, first-account setup, SQL export, migration, demo seeder, automated checks, and deployment scripts.

**Dependency installation is required once on your computer.** The ZIP contains application source and a pinned Composer dependency definition. It does not contain downloaded third-party framework files or `vendor/`. `Setup-Windows.ps1` downloads Composer and the framework, copies the framework's official starter configuration, and creates local writable folders. `Build-InfinityFree.ps1` then includes those installed dependencies in the upload folder. Nothing needs to be installed by Composer on InfinityFree.

## Local setup on Windows / XAMPP

1. Install XAMPP with PHP 8.2 or newer, Git for Windows, and an editor such as VS Code. Internet access is needed for dependency installation. If you already have XAMPP, check `C:\xampp\php\php.exe -v`.
2. Extract this project to `C:\xampp\htdocs\pos-codeigniter`. Open that directory in PowerShell. The directory must contain `composer.json`, `spark`, `Setup-Windows.ps1`, `app`, and `public` directly.
3. Run:

   ```powershell
   powershell -ExecutionPolicy Bypass -File .\Setup-Windows.ps1
   ```

   The script uses `C:\xampp\php\php.exe` when PHP is not in PATH. It also runs Composer through that same PHP executable, avoiding the Windows “php is not recognized” Composer problem. If the script names a missing extension, open the `php.ini` it reports, enable that extension by removing its leading semicolon, save, and rerun the script. Required modules: intl, mbstring, fileinfo, gd, mysqli, and openssl.

4. Start **Apache** and **MySQL** in XAMPP. Open `http://localhost/phpmyadmin/` and create an empty database named `ci4_pos`, with collation `utf8mb4_unicode_ci`.
5. Open the generated `.env` in the project root. The local defaults are:

   ```dotenv
   CI_ENVIRONMENT = development
   app.baseURL = 'http://localhost:8080/'
   app.indexPage = ''
   app.appTimezone = 'Asia/Manila'
   app.forceGlobalSecureRequests = false
   cookie.secure = false
   database.default.hostname = localhost
   database.default.database = ci4_pos
   database.default.username = root
   database.default.password = ''
   database.default.DBDriver = MySQLi
   database.default.port = 3306
   ```

   Keep the generated `POS_SETUP_TOKEN` line. If your MySQL password or port differs, update those settings. The real file must be named **`.env`**, not `env`, `.env.example`, or `.env.txt`.

6. Create the tables and optional demonstration records:

   ```powershell
   & 'C:\xampp\php\php.exe' spark migrate --all
   & 'C:\xampp\php\php.exe' spark db:seed DemoSeeder
   ```

   Use migrations for this local setup. Do not also import `database/infinityfree.sql` into the same database; that SQL is the alternative installation route for a new hosted database.

7. Start the development server:

   ```powershell
   & 'C:\xampp\php\php.exe' spark serve --host localhost --port 8080
   ```

   Keep this terminal open. Visit **http://localhost:8080/setup**. Copy `POS_SETUP_TOKEN` from `.env`, without the surrounding quotes, and choose your first staff username and password. Passwords must contain at least 12 characters. Then sign in at **http://localhost:8080/login**. There is no published default username or password.

8. Add a product image, add a customer, add another staff member with an avatar, and record a test sale. Confirm the product stock decreases. Try a quantity larger than the remaining stock and confirm it is rejected.

If PHP is installed somewhere else, replace `C:\xampp\php\php.exe` in these commands with that executable's path. To stop the development server, press Ctrl+C.

## macOS / Linux / an existing Composer installation

With PHP 8.2+, the required extensions, and Composer installed:

```sh
composer install --no-dev --prefer-dist
php tools/configure.php local
# Create ci4_pos and edit .env with your local database credentials.
php spark migrate --all
php spark db:seed DemoSeeder
php spark serve
```

Do not use `--no-scripts`: Composer's post-install script prepares the official starter configuration. If scripts were skipped, run `php tools/prepare.php` before starting the app.

## Database and deletion behavior

All four required tables and their relationships are retained. `deleted_at` is added to products, customers, and users for soft deletion. Products can be archived. Deleting customers or staff removes them from active lists and selections while retaining the rows referenced by sales. Deleted staff cannot sign in or use existing sessions. The current account cannot delete itself, and at least one active staff account is retained. Deleted usernames remain reserved by the unique constraint.

The sales table additionally stores `product_name`, `customer_name`, and `staff_name` as snapshots, preserving the displayed names after later edits. The original foreign keys remain in place. Sales are append-only through the interface; history has no edit or delete action.

Every staff account has the same management access, as requested by the assignment; no separate administrator/cashier permissions are defined. Stock quantities are whole numbers from 0 to 1,000,000. Sale quantities are from 1 to 1,000,000 and must fit available stock. Prices and each sale total fit `DECIMAL(10,2)`.

`SaleService` opens a transaction, locks active staff/product/customer rows with MySQL `FOR UPDATE`, calculates the total using integer centavos, conditionally decrements available stock, and inserts the sale before committing. It ignores posted staff IDs, unit prices, and totals. A database failure rolls back the sale and inventory change. Product edits also check the stock value from when the form was opened to avoid overwriting stock changed by a sale.

## Images and authentication

JPEG, PNG, and WebP files up to 2 MB are accepted after extension, MIME, decoding, and dimension checks. Images are re-encoded as JPEG. Products fit within 900 × 900 pixels without upscaling; avatars are center-cropped to 240 × 240. Prepared files use random names beneath `writable/uploads/`, outside `public/`, and are served through an authenticated route. EXIF metadata is removed; phone photos with orientation metadata may need rotating before upload.

Passwords use PHP `password_hash` / `password_verify`. Login regenerates the session ID. Password changes revoke other sessions for that account on their next request. CSRF tokens protect all POST actions, and passwords/setup tokens are not copied into form redisplay data. Prepared database queries and escaped views protect the supplied workflows.

## Project organization

| Location | Responsibility |
| --- | --- |
| `app/Controllers/` | HTTP requests, validation, redirects, and views |
| `app/Models/` | Product, customer, staff, and sale records |
| `app/Services/` | Transactional sale workflow and image preparation |
| `app/Libraries/Money.php` | Integer-centavo calculations and decimal limits |
| `app/Filters/AuthFilter.php` | Authentication and account/session validity |
| `app/Views/` | Escaped screen templates and reusable layout |
| `app/Config/Routes.php` | Explicit GET/POST routes; automatic routing disabled |
| `app/Database/` | Migration and optional demo seeder |
| `database/infinityfree.sql` | Hosted installation without a server terminal |
| `public/` | Front controller and static assets |
| `tools/` | Setup, configuration, and hosting builder |
| `tests/` | Money boundary tests and HTTP integration checks |
| `.github/workflows/ci.yml` | PHP 8.2 and 8.4 automated checks |

## Verification

Run the money tests with:

```powershell
& 'C:\xampp\php\php.exe' tests/domain.php
```

For HTTP integration checks, enable PHP's `sqlite3` extension and install Python 3. The tests create a temporary SQLite database, run the actual migration and HTTP routes, and remove their test images afterwards. Your configured MySQL database is not used.

```powershell
$env:PHP_BINARY = 'C:\xampp\php\php.exe'
py -3 tests/smoke.py
```

On Linux/macOS use `php tests/domain.php` and `python3 tests/smoke.py`. The GitHub Actions workflow runs PHP syntax checks, money tests, SQLite HTTP tests, and the hosting build on PHP 8.2 and 8.4. Check the Actions results after pushing. MySQL/InnoDB locking and the actual hosted environment must also be checked using [the acceptance checklist](docs/ACCEPTANCE.md).

**Generation-time validation:** Composer JSON, JavaScript syntax, Python test syntax, project references, and packaging were checked. This generation workspace has no PHP executable and cannot download framework packages, so PHP syntax compilation, Composer installation, PHP/HTTP tests, and hosted execution were not run here. Included tests are checks to run, not a claim that they already passed. No GitHub repository or hosted website has been created by this download.

## Deployment and submission

1. Follow [docs/GITHUB.md](docs/GITHUB.md) to publish the raw project files and generated `composer.lock`.
2. Follow [docs/INFINITYFREE.md](docs/INFINITYFREE.md) to build and upload dependencies, import the SQL, and configure the separate production `.env`.
3. Check the live deployment against [docs/ACCEPTANCE.md](docs/ACCEPTANCE.md).
4. Submit your actual repository URL and the HTTPS site URL. Put the live URL in the repository About/Website field and replace the placeholders in `SUBMISSION.md`. If your instructor needs a test account, provide its credentials through the course's private submission channel.

## Reference documentation

- [CodeIgniter server requirements](https://codeigniter.com/user_guide/intro/requirements.html)
- [Composer installation](https://www.codeigniter.com/user_guide/installation/installing_composer.html)
- [CodeIgniter configuration and .env](https://codeigniter.com/user_guide/general/configuration.html)
- [InfinityFree database setup](https://forum.infinityfree.com/t/how-to-setup-a-new-mysql-database/49342)
- [InfinityFree FTP uploads](https://forum.infinityfree.com/t/how-to-upload-files-with-ftp/49306)
- [InfinityFree filesystem limits](https://forum.infinityfree.com/t/why-are-my-files-deleted-after-uploading-them/49310)
- [Automatic HTTPS for free subdomains](https://forum.infinityfree.com/t/changes-to-ssl-for-free-subdomains/110169/1)

Hosting details were checked against these sources on November 20, 2026. Account-specific hostnames, credentials, and assigned domain names must come from your account.
