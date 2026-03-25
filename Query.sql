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
    total DECIMAL(15, 2),
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

/* OMS upgrade: bo sung cac truong va bang chuyen sau cho don hang */
ALTER TABLE account
    ADD COLUMN IF NOT EXISTS Email VARCHAR(100) DEFAULT NULL AFTER Username,
    ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER Role,
    ADD COLUMN IF NOT EXISTS google_id VARCHAR(191) DEFAULT NULL AFTER Password,
    ADD COLUMN IF NOT EXISTS auth_provider VARCHAR(30) NOT NULL DEFAULT 'local' AFTER google_id,
    ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) DEFAULT NULL AFTER auth_provider;

ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS Account_Id INT NULL AFTER Id,
    ADD COLUMN IF NOT EXISTS status_code VARCHAR(50) DEFAULT 'PENDING' AFTER status,
    ADD COLUMN IF NOT EXISTS payment_status VARCHAR(50) DEFAULT 'UNPAID' AFTER Payment_Method,
    ADD COLUMN IF NOT EXISTS tracking_code VARCHAR(100) DEFAULT NULL AFTER payment_status,
    ADD COLUMN IF NOT EXISTS carrier VARCHAR(100) DEFAULT NULL AFTER tracking_code,
    ADD COLUMN IF NOT EXISTS estimated_delivery DATETIME DEFAULT NULL AFTER carrier,
    ADD COLUMN IF NOT EXISTS delivered_at DATETIME DEFAULT NULL AFTER estimated_delivery,
    ADD COLUMN IF NOT EXISTS completed_at DATETIME DEFAULT NULL AFTER delivered_at,
    ADD COLUMN IF NOT EXISTS cancelled_at DATETIME DEFAULT NULL AFTER completed_at,
    ADD COLUMN IF NOT EXISTS return_requested_at DATETIME DEFAULT NULL AFTER cancelled_at,
    ADD COLUMN IF NOT EXISTS refunded_at DATETIME DEFAULT NULL AFTER return_requested_at,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER Created_at;

CREATE TABLE IF NOT EXISTS order_items
(
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Order_Id INT NOT NULL,
    Product_Id INT NULL,
    Product_Name VARCHAR(150) NOT NULL,
    Product_Image VARCHAR(255) DEFAULT NULL,
    Original_Price DECIMAL(15, 2) NOT NULL DEFAULT 0,
    Sale_Price DECIMAL(15, 2) NOT NULL DEFAULT 0,
    Tax_Amount DECIMAL(15, 2) NOT NULL DEFAULT 0,
    Quantity INT NOT NULL DEFAULT 1,
    Subtotal DECIMAL(15, 2) NOT NULL DEFAULT 0,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Order_Id) REFERENCES orders(Id) ON DELETE CASCADE,
    FOREIGN KEY (Product_Id) REFERENCES product(Id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS order_logs
(
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Order_Id INT NOT NULL,
    Action VARCHAR(100) NOT NULL,
    From_State VARCHAR(50) DEFAULT NULL,
    To_State VARCHAR(50) DEFAULT NULL,
    Action_By INT DEFAULT NULL,
    Reason TEXT DEFAULT NULL,
    Metadata TEXT DEFAULT NULL,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Order_Id) REFERENCES orders(Id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS return_requests
(
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Order_Id INT NOT NULL,
    Requested_By INT DEFAULT NULL,
    Status VARCHAR(50) NOT NULL DEFAULT 'REQUESTED',
    Reason TEXT NOT NULL,
    Evidence_Paths TEXT DEFAULT NULL,
    Admin_Note TEXT DEFAULT NULL,
    Reviewed_By INT DEFAULT NULL,
    Reviewed_At DATETIME DEFAULT NULL,
    Received_At DATETIME DEFAULT NULL,
    Refunded_At DATETIME DEFAULT NULL,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Updated_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (Order_Id) REFERENCES orders(Id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reviews
(
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Order_Id INT NOT NULL,
    Order_Item_Id INT NOT NULL,
    Account_Id INT NOT NULL,
    Rating INT NOT NULL,
    Content TEXT NOT NULL,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_order_item_review (Order_Item_Id, Account_Id),
    FOREIGN KEY (Order_Id) REFERENCES orders(Id) ON DELETE CASCADE,
    FOREIGN KEY (Order_Item_Id) REFERENCES order_items(Id) ON DELETE CASCADE,
    FOREIGN KEY (Account_Id) REFERENCES account(Id) ON DELETE CASCADE
);
