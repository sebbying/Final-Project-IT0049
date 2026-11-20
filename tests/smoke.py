#!/usr/bin/env python3
"""HTTP integration checks against a temporary SQLite DB; never the user's DB.
Requires PHP 8.2+, sqlite3, the app's PHP extensions, and composer install.
Run from any directory: python3 tests/smoke.py
"""
import html
import http.cookiejar
import os
from pathlib import Path
import re
import shutil
import socket
import sqlite3
import struct
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request
import uuid
import zlib

ROOT = Path(__file__).resolve().parents[1]
PHP = os.environ.get("PHP_BINARY") or shutil.which("php")
if not PHP:
    raise SystemExit("PHP is required. Install dependencies, then run this script with PHP on PATH.")
checks = 0


def expect(condition, message):
    global checks
    if not condition:
        raise AssertionError(message)
    checks += 1


def png():
    def chunk(kind, body):
        return struct.pack(">I", len(body)) + kind + body + struct.pack(">I", zlib.crc32(kind + body) & 0xffffffff)
    return b"\x89PNG\r\n\x1a\n" + chunk(b"IHDR", struct.pack(">IIBBBBB", 2, 3, 8, 2, 0, 0, 0)) + chunk(b"IDAT", zlib.compress((b"\x00" + b"\x35\x88\x55" * 2) * 3)) + chunk(b"IEND", b"")


class Browser:
    def __init__(self, base):
        self.base = base
        self.opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))

    def request(self, path, data=None, files=None):
        headers = {}
        body = None
        if data is not None:
            if files:
                boundary = "pos" + uuid.uuid4().hex
                pieces = []
                for name, value in data.items():
                    pieces.append((f'--{boundary}\r\nContent-Disposition: form-data; name="{name}"\r\n\r\n{value}\r\n').encode())
                for name, (filename, content, mime) in files.items():
                    pieces.extend([(f'--{boundary}\r\nContent-Disposition: form-data; name="{name}"; filename="{filename}"\r\nContent-Type: {mime}\r\n\r\n').encode(), content, b"\r\n"])
                pieces.append(f"--{boundary}--\r\n".encode())
                body = b"".join(pieces)
                headers["Content-Type"] = "multipart/form-data; boundary=" + boundary
            else:
                body = urllib.parse.urlencode(data).encode()
        request = urllib.request.Request(self.base + path, data=body, headers=headers)
        try:
            response = self.opener.open(request, timeout=15)
        except urllib.error.HTTPError as error:
            response = error
        raw = response.read()
        return response.status, urllib.parse.urlparse(response.geturl()).path, raw.decode("utf-8", errors="replace"), raw

    def form(self, get_path, post_path, data, files=None):
        status, _, page, _ = self.request(get_path)
        expect(status == 200, f"Form GET failed: {get_path} ({status})\n{page[:1200]}")
        token = re.search(r'name="csrf_token"\s+value="([^"]+)"', page)
        expect(token is not None, "CSRF token missing from " + get_path)
        return self.request(post_path, dict(data, csrf_token=html.unescape(token.group(1))), files)


(ROOT / ".tools").mkdir(exist_ok=True)
with tempfile.TemporaryDirectory(prefix="pos-smoke-", dir=ROOT / ".tools") as directory:
    temp = Path(directory)
    database = temp / "test.sqlite"
    (temp / "session").mkdir()
    with socket.socket() as sock:
        sock.bind(("127.0.0.1", 0))
        port = sock.getsockname()[1]
    base = f"http://127.0.0.1:{port}"
    setup_token = uuid.uuid4().hex + uuid.uuid4().hex
    env = dict(os.environ)
    env.update({
        "CI_ENVIRONMENT": "development", "app.baseURL": base + "/", "app.indexPage": "",
        "app.forceGlobalSecureRequests": "false", "cookie.secure": "false",
        "database.default.DBDriver": "SQLite3", "database.default.database": str(database),
        "database.default.DBPrefix": "", "database.default.DBDebug": "true",
        "session.savePath": str(temp / "session"), "cache.prefix": "smoke_" + uuid.uuid4().hex,
        "POS_SETUP_TOKEN": setup_token,
    })
    migrated = subprocess.run([PHP, "spark", "migrate", "--all"], cwd=ROOT, env=env, text=True, capture_output=True)
    expect(migrated.returncode == 0, "Migration failed:\n" + migrated.stdout + migrated.stderr)
    log = open(temp / "server.log", "w+")
    server = subprocess.Popen([PHP, "-S", f"127.0.0.1:{port}", "-t", "public", "tools/serve-router.php"], cwd=ROOT, env=env, stdout=log, stderr=log)
    db = sqlite3.connect(database)
    db.row_factory = sqlite3.Row
    browser = Browser(base)
    try:
        for attempt in range(50):
            try:
                browser.request("/login")
                break
            except (urllib.error.URLError, ConnectionError):
                time.sleep(0.1)
        else:
            raise RuntimeError("PHP server did not start.")
        _, path, _, _ = browser.request("/products")
        expect(path in ("/login", "/setup"), "Anonymous management access was allowed.")
        result = browser.form("/setup", "/setup", {"setup_token": setup_token, "username": "manager", "full_name": "Test Manager", "password": "Correct-Horse-123!", "password_confirm": "Correct-Horse-123!"})
        expect(result[1] == "/login", "Setup did not reach login: " + result[2][:1200])
        expect(db.execute("SELECT COUNT(*) FROM users").fetchone()[0] == 1, "First user missing.")
        password_hash = db.execute("SELECT password FROM users WHERE id=1").fetchone()[0]
        expect(password_hash != "Correct-Horse-123!" and password_hash.startswith("$"), "Password was not hashed.")
        expect(browser.request("/setup")[1] == "/login", "Setup did not close after account creation.")
        result = browser.form("/login", "/login", {"username": "manager", "password": "wrong"})
        expect("Invalid username or password" in result[2], "Bad credentials were not rejected.")
        result = browser.form("/login", "/login", {"username": "manager", "password": "Correct-Horse-123!"})
        expect(result[1] == "/", "Valid login failed: " + result[2][:1200])
        for path in ("/", "/products", "/customers", "/staff", "/sales", "/sales/new"):
            result = browser.request(path)
            expect(result[0] == 200, path + " failed: " + result[2][:1200])
        image = png()
        result = browser.form("/products/new", "/products", {"name": "Test tea", "price": "9.99", "stock_quantity": "10"}, {"image": ("tea.png", image, "image/png")})
        expect(result[1] == "/products", "Product insert failed: " + result[2][:1200])
        product = dict(db.execute("SELECT * FROM products WHERE name='Test tea'").fetchone())
        pid = product["id"]
        expect(re.fullmatch(r"[a-f0-9]{40}\.jpg", product["image"]) is not None, "Image name was not randomized.")
        result = browser.request("/media/products/" + product["image"])
        expect(result[0] == 200 and result[3].startswith(b"\xff\xd8"), "Image was not re-encoded to JPEG.")
        browser.form("/customers/new", "/customers", {"full_name": "Test Customer", "email": "customer@example.test", "phone": "+63 917 111 2222"})
        customer_id = db.execute("SELECT id FROM customers WHERE full_name='Test Customer'").fetchone()[0]
        result = browser.form("/sales/new", "/sales", {"product_id": pid, "customer_id": customer_id, "quantity": "3", "total_price": "0.01", "sold_by": "999"})
        expect(result[1] == "/sales", "Sale failed: " + result[2][:1200])
        sale = dict(db.execute("SELECT * FROM sales").fetchone())
        expect(sale["quantity"] == 3 and round(float(sale["total_price"]), 2) == 29.97 and sale["sold_by"] == 1, "Server trusted a forged price or staff ID.")
        expect(db.execute("SELECT stock_quantity FROM products WHERE id=?", [pid]).fetchone()[0] == 7, "Stock was not deducted exactly once.")
        result = browser.form("/sales/new", "/sales", {"product_id": pid, "customer_id": "", "quantity": "8"})
        expect("Insufficient stock" in result[2], "Overselling was not rejected clearly.")
        expect(db.execute("SELECT COUNT(*) FROM sales").fetchone()[0] == 1 and db.execute("SELECT stock_quantity FROM products WHERE id=?", [pid]).fetchone()[0] == 7, "Failed sale changed inventory/history.")
        db.execute("CREATE TRIGGER reject_test_sale BEFORE INSERT ON sales BEGIN SELECT RAISE(FAIL, 'intentional integration-test failure'); END")
        db.commit()
        result = browser.form("/sales/new", "/sales", {"product_id": pid, "customer_id": "", "quantity": "1"})
        expect("could not be saved" in result[2], "Database failure did not produce a usable message.")
        expect(db.execute("SELECT COUNT(*) FROM sales").fetchone()[0] == 1 and db.execute("SELECT stock_quantity FROM products WHERE id=?", [pid]).fetchone()[0] == 7, "Sale insert failure did not roll back its stock decrement.")
        db.execute("DROP TRIGGER reject_test_sale")
        db.commit()
        for quantity in ("0", "-1", "1.5"):
            browser.form("/sales/new", "/sales", {"product_id": pid, "customer_id": "", "quantity": quantity})
        expect(db.execute("SELECT COUNT(*) FROM sales").fetchone()[0] == 1, "Invalid quantities were accepted.")
        result = browser.form(f"/products/{pid}/edit", f"/products/{pid}", {"name": "Stale change", "price": "9.99", "stock_quantity": "10", "original_stock": "10"})
        expect("Stock changed" in result[2] and db.execute("SELECT stock_quantity FROM products WHERE id=?", [pid]).fetchone()[0] == 7, "Stale edit restored sold stock.")
        name = "Tea <script>alert(1)</script>"
        browser.form(f"/products/{pid}/edit", f"/products/{pid}", {"name": name, "price": "10.00", "stock_quantity": "7", "original_stock": "7"})
        page = browser.request("/products")[2]
        expect("&lt;script&gt;" in page and "<script>alert(1)</script>" not in page, "Product name was not escaped.")
        browser.form(f"/customers/{customer_id}/edit", f"/customers/{customer_id}", {"full_name": "Updated Customer", "email": "updated@example.test", "phone": ""})
        expect(db.execute("SELECT full_name FROM customers WHERE id=?", [customer_id]).fetchone()[0] == "Updated Customer", "Customer update failed.")
        browser.form("/staff/new", "/staff", {"username": "cashier", "full_name": "Test Cashier", "password": "Cashier-Password-123", "password_confirm": "Cashier-Password-123"}, {"avatar": ("avatar.png", image, "image/png")})
        cashier = dict(db.execute("SELECT * FROM users WHERE username='cashier'").fetchone())
        cid = cashier["id"]
        expect(cashier["avatar"].endswith(".jpg"), "Avatar was not prepared.")
        browser.form(f"/staff/{cid}/edit", f"/staff/{cid}", {"username": "cashier", "full_name": "Updated Cashier", "password": "", "password_confirm": ""})
        expect(db.execute("SELECT password FROM users WHERE id=?", [cid]).fetchone()[0] == cashier["password"], "Blank password edit replaced the hash.")
        cashier_browser = Browser(base)
        cashier_browser.form("/login", "/login", {"username": "cashier", "password": "Cashier-Password-123"})
        cashier_browser.form("/sales/new", "/sales", {"product_id": pid, "customer_id": "", "quantity": "1"})
        expect(db.execute("SELECT customer_id FROM sales ORDER BY id DESC LIMIT 1").fetchone()[0] is None, "Walk-in customer was not stored as NULL.")
        browser.form("/staff", f"/staff/{cid}/delete", {})
        expect(db.execute("SELECT deleted_at FROM users WHERE id=?", [cid]).fetchone()[0] is not None, "Staff delete failed.")
        expect(cashier_browser.request("/products")[1] == "/login", "Deleted staff retained access.")
        browser.form("/staff", "/staff/1/delete", {})
        expect(db.execute("SELECT deleted_at FROM users WHERE id=1").fetchone()[0] is None, "Current staff deleted their own account.")
        count = db.execute("SELECT COUNT(*) FROM customers").fetchone()[0]
        browser.request("/customers", {"full_name": "No CSRF", "email": "bad@example.test", "phone": ""})
        expect(db.execute("SELECT COUNT(*) FROM customers").fetchone()[0] == count, "CSRF-free request created a customer.")
        result = browser.form("/products/new", "/products", {"name": "Bad upload", "price": "1.00", "stock_quantity": "1"}, {"image": ("attack.php", b"<?php echo 123;", "image/png")})
        expect(db.execute("SELECT COUNT(*) FROM products WHERE name='Bad upload'").fetchone()[0] == 0, "Executable upload was accepted.")
        browser.form("/customers", f"/customers/{customer_id}/delete", {})
        browser.form("/products", f"/products/{pid}/delete", {})
        expect(db.execute("SELECT deleted_at FROM customers WHERE id=?", [customer_id]).fetchone()[0] is not None, "Customer delete failed.")
        expect(db.execute("SELECT deleted_at FROM products WHERE id=?", [pid]).fetchone()[0] is not None, "Product archive failed.")
        history = browser.request("/sales")[2]
        expect("Test tea" in history and "Test Customer" in history and "Updated Cashier" in history, "Historical snapshots were not preserved.")
        expect(db.execute("SELECT COUNT(*) FROM sales").fetchone()[0] == 2, "Deletes removed historical sales.")
        browser.form("/", "/logout", {})
        expect(browser.request("/products")[1] == "/login", "Logout did not revoke access.")
        print(f"PASS: {checks} HTTP, CRUD, authentication, upload, sale, inventory, and history checks.")
    except Exception:
        log.flush()
        log.seek(0)
        print("\nPHP server log:\n" + log.read()[-6000:])
        raise
    finally:
        server.terminate()
        try:
            server.wait(timeout=5)
        except subprocess.TimeoutExpired:
            server.kill()
            server.wait()
        for table, column, kind in [("products", "image", "products"), ("users", "avatar", "avatars")]:
            for row in db.execute(f"SELECT {column} FROM {table} WHERE {column} IS NOT NULL"):
                if re.fullmatch(r"[a-f0-9]{40}\.jpg", row[0]):
                    (ROOT / "writable" / "uploads" / kind / row[0]).unlink(missing_ok=True)
        db.close()
        log.close()
