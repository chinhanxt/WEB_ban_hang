/* Tao CSDL: my_store */
CREATE DATABASE IF NOT EXISTS my_store;
/* Mo CSDL: my_store */
USE my_store;

/* Tao bang: loai san pham (danh muc): category */
CREATE TABLE IF NOT EXISTS category
(
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Description TEXT
);

/* Tao bang san pham: product */
CREATE TABLE IF NOT EXISTS product
(
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Description TEXT,
    Price DECIMAL(10, 2) NOT NULL,
    Image VARCHAR(255) DEFAULT NULL,
    Category_Id INT,
    FOREIGN KEY (Category_Id) REFERENCES category(Id) ON DELETE CASCADE
);

/* Tao bang don dat hang: orders */
CREATE TABLE IF NOT EXISTS orders
(
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(60) NOT NULL,
    Address TEXT,
    Phone VARCHAR(20),
    Email VARCHAR(100),
    total DECIMAL(10, 2),
    Payment_Method VARCHAR(50),
    Notes TEXT,
    status VARCHAR(50) DEFAULT 'Đang chờ xử lý',
    Created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

/* Tao bang chi tiet don dat hang: order_details */
CREATE TABLE IF NOT EXISTS order_details
(
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Order_Id INT NOT NULL,
    Product_Id INT NOT NULL,
    Quantity INT NOT NULL,
    Price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (Order_Id) REFERENCES orders(Id) ON DELETE CASCADE,
    FOREIGN KEY (Product_Id) REFERENCES product(Id) ON DELETE NO ACTION
);

/* Tao bang luu thong tin nguoi dung: account */
CREATE TABLE IF NOT EXISTS account
(
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(40) NOT NULL UNIQUE,
    Fullname VARCHAR(100) NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Role ENUM('admin','user') DEFAULT 'user'
);

/* Tao bang giỏ hàng theo tài khoản: cart_items */
CREATE TABLE IF NOT EXISTS cart_items
(
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Account_Id INT NOT NULL,
    Product_Id INT NOT NULL,
    Quantity INT NOT NULL DEFAULT 1,
    Created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_account_product (Account_Id, Product_Id),
    FOREIGN KEY (Account_Id) REFERENCES account(Id) ON DELETE CASCADE,
    FOREIGN KEY (Product_Id) REFERENCES product(Id) ON DELETE CASCADE
);

/* Them du lieu vao bang loai san pham (danh muc): category */
INSERT INTO category(Name, Description) VALUES
('Điện thoại', 'Danh mục các loại điện thoại'),
('Laptop', 'Danh mục các loại laptop'),
('Máy tính bảng', 'Danh mục các loại máy tính bảng'),
('Phụ kiện', 'Danh mục các loại phụ kiện điện tử'),
('Thiết bị âm thanh', 'Danh mục các loại loa, tai nghe, micro');

/* Them du lieu vao bang san pham: product */
INSERT INTO product(Name, Description, Price, Image, Category_Id) VALUES
('Điện thoại i17', 'iPhone 17 Pro Max 256GB  Chính hãng', 12000000, 'uploads/Smartphone01.PNG', 1),
('Laptop Asus', 'Asus 15.6 4k Ultra Hd Touch-Screen Gaming', 20000000, 'uploads/Laptop03.PNG', 2);
