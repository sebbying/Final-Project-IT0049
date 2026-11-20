# Acceptance checklist before submission

Run this on your actual MySQL/MariaDB installation and again on the hosted site. Record your own observed results; an unchecked item is not a passing result.

| Done | Check | Expected result |
| --- | --- | --- |
| [ ] | Open `/products`, `/customers`, `/staff`, `/sales`, and `/sales/new` while signed out | Redirected to sign-in |
| [ ] | Sign in with a wrong password | Clear generic error; access denied |
| [ ] | Create a product at ₱9.99 with stock 10 and a PNG image | Product appears with a prepared image |
| [ ] | Edit its name, price, stock, and image | Saved changes appear in the list |
| [ ] | Create, edit, and delete a customer | Valid records save; deleted customer leaves the active list |
| [ ] | Submit an invalid customer email | Validation error; invalid change is not saved |
| [ ] | Create staff with an avatar and a 12+ character password | Staff appears; avatar displays; new credentials work |
| [ ] | Edit staff with an empty password field | Current password still works |
| [ ] | Change staff password | New password works; previous sessions are revoked on their next request |
| [ ] | Try adding a duplicate username | Rejected without a raw SQL error |
| [ ] | Upload a `.php` or text file as an image | Rejected without creating a product/staff record |
| [ ] | Sell 3 units of a ₱9.99 product with stock 10 | Total ₱29.97; stock becomes 7; one history entry |
| [ ] | Sell to a selected customer, then sell to Walk-in | Both appear correctly in history |
| [ ] | Request 8 units when stock is 7 | Clear insufficient-stock message; stock/history unchanged |
| [ ] | Submit zero, a negative number, or a decimal quantity | Rejected; stock/history unchanged |
| [ ] | Open an edit form, sell one unit in another tab, then save the old stock form | Stale stock edit rejected |
| [ ] | From two separate staff sessions, attempt to sell the final unit at the same time | At most one sale succeeds; stock never becomes negative |
| [ ] | Rename/delete a previously selected customer or archive a sold product | Historical sale remains and shows original names |
| [ ] | Delete another staff member while they are signed in | Their next management request is redirected to sign-in; historical sales remain |
| [ ] | Try deleting your own account | Rejected |
| [ ] | Record a sale with forged posted `sold_by`/`total_price` values | Server uses logged-in staff and saved product price |
| [ ] | Put `<script>alert(1)</script>` in a product name | Shown as text; script does not execute |
| [ ] | Submit a management POST without a valid CSRF token | Rejected; no data change |
| [ ] | Sign out, then open a protected URL directly | Access denied |
| [ ] | Request `/.env`, `/app/Config/Database.php`, `/vendor/composer/installed.json`, and `/writable/logs/` on the hosted site | Access denied/404; no credentials, source, directory listing, or logs returned |
| [ ] | Visit hosted `/setup` after creating the first user | Redirects to sign-in |
| [ ] | Open GitHub source and Actions | Raw application, SQL/migration/seeder, README, generated lockfile, and passing check results are present; real `.env` absent |
| [ ] | Open hosted URL in another browser and at a mobile width | HTTPS and layout work; application matches submitted source |

The supplied HTTP tests use an isolated SQLite database and also force a sale-insert failure to check rollback after the stock-decrement step. The simultaneous-sale check above is specifically for the production MySQL/InnoDB environment.
