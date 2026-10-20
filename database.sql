-- BM Digital Store - database
-- Run this in phpMyAdmin (or: mysql -u root < database.sql)

CREATE DATABASE IF NOT EXISTS shopdb CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE shopdb;

-- Drop tables (for a clean re-run)
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- Users who can log in
CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,   -- stored hashed
    role       ENUM('customer','admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Product categories
CREATE TABLE categories (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- Items for sale
CREATE TABLE products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT DEFAULT NULL,
    name        VARCHAR(150) NOT NULL,
    description TEXT,
    price       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock       INT NOT NULL DEFAULT 0,
    image       VARCHAR(255) DEFAULT NULL,  -- file name inside /assets/uploads
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- One order per checkout
CREATE TABLE orders (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT DEFAULT NULL,
    full_name  VARCHAR(100) NOT NULL,
    address    VARCHAR(255) NOT NULL,
    phone      VARCHAR(30) NOT NULL,
    total      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status     ENUM('pending','shipped','delivered','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- The product lines inside one order
CREATE TABLE order_items (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    order_id   INT NOT NULL,
    product_id INT DEFAULT NULL,
    quantity   INT NOT NULL DEFAULT 1,
    price      DECIMAL(10,2) NOT NULL,   -- price at the moment of buying
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Sample data (prices in Rwandan francs)
INSERT INTO categories (name) VALUES ('Laptops'), ('Phones'), ('Accessories'), ('Furniture');

INSERT INTO products (category_id, name, description, price, stock, image) VALUES
(1, 'Laptop Pro 14',        'Lightweight 14-inch laptop, 16GB RAM, 512GB SSD.',  1250000.00, 12, 'laptop.svg'),
(1, 'Gaming Monster 17',    '17-inch gaming laptop with RTX graphics.',             2100000.00, 7,  'laptop.svg'),
(2, 'SmartPhone X',         '6.5 inch display, 128GB, great camera.',               750000.00, 25, 'phone.svg'),
(2, 'Budget Phone 5',       'Affordable phone for everyday use.',                   180000.00, 40, 'phone.svg'),
(3, 'Wireless Mouse',       'Ergonomic wireless mouse with USB receiver.',          25000.00, 100,'mouse.svg'),
(3, 'Mechanical Keyboard',  'RGB backlit mechanical keyboard.',                      65000.00, 80, 'keyboard.svg'),
(4, 'Office Chair',         'Ergonomic mesh office chair.',                        150000.00, 15, 'chair.svg'),
(4, 'Wooden Desk',          'Solid wood desk with two drawers.',                   280000.00, 10, 'desk.svg');

-- Admin account: email admin@shop.com / password admin123
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@shop.com', '$2y$10$sOKSUap0Aj4YzUIu/3c51O5ZKQ5CJNLtCXB0ZlUaLJwl8KzA7UidK', 'admin'),
('Jane Doe', 'jane@shop.com', '$2y$10$o3sSZqwAl5RYUrsU.0E/CeeMFKcXjAn6DfvaM/CMon.xYJqzG5Fge', 'customer');
