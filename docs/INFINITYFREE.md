# Host the application on InfinityFree

These steps use a new free subdomain at its root URL, such as `https://your-pos.ct.ws/`. Copy your actual assigned address; this guide does not assume a particular available domain suffix. Complete local dependency installation first.

## 1. Create the hosting account and database

1. Sign in at [InfinityFree](https://dash.infinityfree.com/) and create a free hosting account with an available subdomain. Wait for the account to become active, then open its control panel once.
2. Free subdomains have automatic SSL. Use the assigned subdomain directly, without an added `www.` prefix, and verify that its `https://` address opens without a certificate warning. A custom domain uses InfinityFree's separate SSL setup process.
3. Open **Control Panel → MySQL Databases**. Enter a short database name such as `pos`, then create it.
4. Copy the complete **MySQL Host Name**, **MySQL DB Name**, and **MySQL User Name** shown for that database. Find the database/hosting account password in the client area's account details. This is the hosting account password, which can differ from the password used to sign in to the InfinityFree dashboard.
5. Open **phpMyAdmin** from the hosting control panel. Select the new empty database. Choose **Import**, select this project's `database/infinityfree.sql`, and click **Import/Go**. Confirm that `products`, `customers`, `users`, and `sales` exist. The sample products/customers are included. The users table starts empty so you can create your own first account.

Import the SQL once into a new database. Do not run migrations on top of this import. It has no `CREATE DATABASE` statement, so it does not require database-creation privileges inside phpMyAdmin.

## 2. Build the upload directory on your computer

In the project's PowerShell terminal, run:

```powershell
powershell -ExecutionPolicy Bypass -File .\Build-InfinityFree.ps1
```

On Linux/macOS, run `php tools/build.php`.

The result is **`dist/infinityfree/htdocs/`**, containing `app/`, `public/`, `vendor/`, `writable/`, a root `.htaccess`, and a generated production `.env`. The builder includes installed Composer dependencies and official starter configuration. It creates fresh session/cache/log/upload directories and a new setup token. It does not copy local passwords, local sessions, or local uploaded images.

The SQL import is a fresh hosted installation with its own first staff account. To preserve all local data instead, export your local database with phpMyAdmin and import that export into the empty hosted database *instead of* `infinityfree.sql`, then copy your local `writable/uploads/` images separately. Do not export MySQL system databases. Existing staff keep their passwords, so first-account setup is already closed in this alternative.

Repeated builds preserve the staging directory's `.env`; check its values before each upload. When removing or renaming source files, remove the corresponding obsolete staged/server file as part of your update, since the builder merges application files.

## 3. Edit the production .env

Open **`dist/infinityfree/htdocs/.env`**, not your project's development `.env`. Replace every `YOUR_...` placeholder using the values you copied from your hosting account:

```dotenv
CI_ENVIRONMENT = production
app.baseURL = 'https://YOUR_ACTUAL_DOMAIN/'
app.indexPage = ''
app.appTimezone = 'Asia/Manila'
app.forceGlobalSecureRequests = true
cookie.secure = true

database.default.hostname = 'YOUR_MYSQL_HOST'
database.default.database = 'YOUR_FULL_DATABASE_NAME'
database.default.username = 'YOUR_MYSQL_USERNAME'
database.default.password = 'YOUR_MYSQL_PASSWORD'
database.default.DBDriver = MySQLi
database.default.DBPrefix = ''
database.default.port = 3306
database.default.charset = utf8mb4
database.default.DBCollat = utf8mb4_unicode_ci
database.default.DBDebug = false

POS_SETUP_TOKEN = 'KEEP_THE_RANDOM_TOKEN_GENERATED_BY_THE_BUILDER'
```

The token above explains the field; keep your actual generated 64-character token in the real file.

| Setting | What to enter |
| --- | --- |
| `CI_ENVIRONMENT` | `production`, so detailed errors are not shown publicly |
| `app.baseURL` | The exact HTTPS site URL, with a trailing slash; no `/public/` suffix |
| `app.indexPage` | Empty string `''`; the supplied rewrite rules handle routes |
| `app.forceGlobalSecureRequests` | `true` after confirming HTTPS works |
| `cookie.secure` | `true` for HTTPS sessions |
| `database.default.hostname` | The assigned MySQL hostname, often beginning with `sql`; **not** `localhost` or the phpMyAdmin URL |
| `database.default.database` | Full database name including the account prefix |
| `database.default.username` | Assigned MySQL user, including its account prefix |
| `database.default.password` | Hosting/MySQL account password, inside quotes |
| `database.default.DBDebug` | `false` in production |
| `POS_SETUP_TOKEN` | The builder's unique random token, used once for your first staff account |

Leave the database driver as `MySQLi`. This CodeIgniter app does not use Laravel's `APP_KEY`, `APP_URL`, or `php artisan` commands. MySQL connections to InfinityFree's database must originate from the hosted application; do not put the remote hosting credentials into your local setup expecting external MySQL access.

## 4. Upload with FTP

1. Install an FTP client such as [FileZilla Client](https://filezilla-project.org/).
2. In the InfinityFree client area, copy **FTP Details**: host, username, and password. Use those exact account values in FileZilla. Use the FTP service, usually port 21; the account's settings determine supported encryption. This is separate from the MySQL hostname.
3. Connect and open the website's correct **`htdocs`** directory on the right. For an additional domain, open that domain's own `htdocs`. Keep all application files inside this directory: files outside `htdocs` can be deleted automatically by the hosting service.
4. In the left pane, open your local **`dist/infinityfree/htdocs`** directory.
5. In a new site's server `htdocs`, remove only its default placeholder page such as `index2.html`/`index.html` if present. Do not remove unrelated websites or account folders.
6. Upload the **contents** of the local `htdocs`, including hidden dotfiles. Do not create an extra `htdocs/htdocs` or `htdocs/pos-codeigniter` nesting level. Do not upload the original project ZIP.
7. Check FileZilla's failed transfers and retry failed items. Confirm that both `htdocs/.htaccess` and `htdocs/public/.htaccess`, the real `htdocs/.env`, and `htdocs/vendor/codeigniter4/framework/system/Boot.php` exist on the server.

The server layout is:

| Server location | Contents |
| --- | --- |
| `htdocs/.env` | Your private production configuration |
| `htdocs/.htaccess` | Private-path blocking and forwarding into `public/` |
| `htdocs/app/` | Application and prepared starter configuration |
| `htdocs/public/` | `index.php`, its `.htaccess`, and static assets |
| `htdocs/vendor/` | Framework and required Composer packages |
| `htdocs/writable/` | Sessions, prepared uploads, logs, cache, and deny rule |

The root rewrite file is already supplied. It blocks sensitive paths before forwarding public requests. Keep the additional `writable/.htaccess` deny rule. Do not replace the root rewrite file with a rule that serves existing private files directly.

## 5. Create the first hosted staff account

1. Open `https://YOUR_ACTUAL_DOMAIN/setup` in a normal browser.
2. Copy the generated `POS_SETUP_TOKEN` value from the production `.env`, without quotes, into the setup form. Choose a username, full name, password of at least 12 characters, and matching confirmation.
3. Sign in at `/login`. Once any staff record exists, `/setup` redirects to sign-in and cannot create another initial account. Add further staff from **Staff → Add staff member**.
4. After setup, you can clear `POS_SETUP_TOKEN` to `''` in the production `.env`. Normal sign-in and staff management do not need it.
5. Run through `ACCEPTANCE.md`, particularly product/stock changes, both upload types, customer/staff CRUD, sale rejection, sales history, logout, and protected URLs.

To preserve sessions and images during later code uploads, update `app/`, `public/`, and necessary dependencies; keep the site's `.env` and existing `writable/` contents. Do not reimport the installation SQL into an existing live database.

## Troubleshooting

| Symptom | Check |
| --- | --- |
| `Boot.php` or a dependency class is missing | Run local setup first; rebuild and upload the complete `vendor/` directory. Check FTP failed transfers. |
| HTTP 500 / “Whoops” | Read the newest `htdocs/writable/logs/` file through FTP; check database settings, missing transfers, PHP extensions, and writable folders. Keep production error display off. |
| Routes other than the landing page fail | Both `.htaccess` files must be present in the correct locations. Do not open `/public/` URLs when the base URL is the domain root. |
| Default InfinityFree page still appears | Correct domain's `htdocs`, default placeholder page, DNS/account activation, and cached page. |
| Database connection fails | Full account-prefixed DB/user names, assigned SQL hostname, hosting account password, and a completed import. |
| `Table already exists` on import | You imported into a nonempty database. Use a new empty database rather than dropping a live one. |
| Setup token is rejected | Use the production token from the uploaded `.env`; omit surrounding quotes. Confirm the file is not named `.env.txt`. A template `REPLACE_...` token is deliberately rejected. |
| Login loops or session cannot be written | Exact HTTPS base URL, secure cookies used over HTTPS, browser cookies enabled, and existing `writable/session`. |
| Image upload fails | File is JPEG/PNG/WebP, at most 2 MB, within pixel limits, and `writable/uploads/products` or `avatars` exists. Check GD/fileinfo support and the application log. |
| An uploaded file vanishes | All application files must be inside `htdocs`. FTP the expanded directory; InfinityFree enforces file size/type limits. |
| Editing a product says stock changed | Reload the edit page to get current stock, then apply your intended stock count. A sale happened after the form was opened. |
| Automated remote HTTP checks hit a browser challenge | Verify the free-hosted site in a normal browser. Run the included automated checks locally or in GitHub Actions. |

Use normal account directory/file permissions first. If logs report a specific unwritable directory, adjust that directory through your FTP client's permissions feature according to the hosting account's supported settings; do not make the whole project publicly writable.

Sources: [MySQL setup](https://forum.infinityfree.com/t/how-to-setup-a-new-mysql-database/49342), [remote connection limits](https://forum.infinityfree.com/t/common-mysql-connection-errors/49338/2), [FTP upload guide](https://forum.infinityfree.com/t/how-to-upload-files-with-ftp/49306), [htdocs and file limits](https://forum.infinityfree.com/t/why-are-my-files-deleted-after-uploading-them/49310), [automatic free-subdomain SSL](https://forum.infinityfree.com/t/changes-to-ssl-for-free-subdomains/110169/1), and [CodeIgniter configuration](https://codeigniter.com/user_guide/general/configuration.html). Checked November 20, 2026.
