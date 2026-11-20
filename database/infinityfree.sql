-- IT0049 CodeIgniter POS. Import ONCE into a NEW, EMPTY database.
-- Select the database in phpMyAdmin first. No CREATE DATABASE privilege is needed.
-- No default staff/password is published. Create the first account at /setup.
SET NAMES utf8mb4;

CREATE TABLE products (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    image VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    deleted_at DATETIME DEFAULT NULL,
    KEY products_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE customers (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    deleted_at DATETIME DEFAULT NULL,
    KEY customers_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    deleted_at DATETIME DEFAULT NULL,
    UNIQUE KEY users_username (username),
    KEY users_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sales (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    customer_id INT DEFAULT NULL,
    sold_by INT NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    created_at DATETIME NOT NULL,
    product_name VARCHAR(100) DEFAULT NULL,
    customer_name VARCHAR(100) DEFAULT NULL,
    staff_name VARCHAR(100) DEFAULT NULL,
    KEY sales_created_at (created_at),
    CONSTRAINT sales_product_fk FOREIGN KEY (product_id) REFERENCES products(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT sales_customer_fk FOREIGN KEY (customer_id) REFERENCES customers(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT sales_staff_fk FOREIGN KEY (sold_by) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO products (name, price, stock_quantity, created_at) VALUES
('House blend coffee · 250 g', 289.00, 25, NOW()),
('Ceramic daily mug', 199.50, 12, NOW()),
('Oat cookies · 6 pack', 89.75, 4, NOW()),
('Cold brew · 330 ml', 129.00, 18, NOW());
INSERT INTO customers (full_name, email, phone, created_at) VALUES
('Alex Reyes', 'alex@example.test', '09171234567', NOW()),
('Sam Cruz', 'sam@example.test', '09181234567', NOW());
