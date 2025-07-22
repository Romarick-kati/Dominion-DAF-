-- Create Database
CREATE DATABASE IF NOT EXISTS warehouse_management;
USE warehouse_management;

-- Users Table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'manager', 'employee') DEFAULT 'employee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Suppliers Table
CREATE TABLE suppliers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Warehouses Table
CREATE TABLE warehouses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(255) NOT NULL,
    capacity INT DEFAULT 0,
    manager VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT DEFAULT 0,
    supplier_id INT,
    warehouse_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE SET NULL
);

-- Orders Table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    status ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Inventory Table (for tracking stock movements)
CREATE TABLE inventory (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    warehouse_id INT NOT NULL,
    quantity_change INT NOT NULL,
    movement_type ENUM('IN', 'OUT', 'ADJUSTMENT') NOT NULL,
    reason VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE
);

-- Insert Sample Data

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, email, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@warehouse.com', 'admin'),
('manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager@warehouse.com', 'manager'),
('employee', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee@warehouse.com', 'employee');

-- Insert sample suppliers
INSERT INTO suppliers (name, email, phone, address) VALUES 
('ABC Supply Co.', 'contact@abcsupply.com', '123-456-7890', '123 Supply Street, Business District, City, State 12345'),
('XYZ Materials Ltd.', 'info@xyzmaterials.com', '098-765-4321', '456 Material Avenue, Industrial Zone, City, State 67890'),
('Global Parts Inc.', 'sales@globalparts.com', '555-123-4567', '789 Parts Boulevard, Commerce Center, City, State 13579'),
('Premium Goods LLC', 'orders@premiumgoods.com', '444-987-6543', '321 Premium Lane, Trade District, City, State 24680');

-- Insert sample warehouses
INSERT INTO warehouses (name, location, capacity, manager) VALUES 
('Main Warehouse', 'Downtown District, 100 Main Street', 10000, 'John Smith'),
('Secondary Storage', 'Industrial District, 200 Industrial Ave', 5000, 'Jane Doe'),
('North Branch', 'North Zone, 300 North Road', 7500, 'Mike Johnson'),
('South Depot', 'South Area, 400 South Way', 3000, 'Sarah Wilson');

-- Insert sample products
INSERT INTO products (name, description, price, quantity, supplier_id, warehouse_id) VALUES 
('Office Chair Pro', 'Ergonomic office chair with lumbar support and adjustable height', 150.00, 50, 1, 1),
('LED Desk Lamp', 'Energy-efficient LED desk lamp with adjustable brightness', 45.99, 100, 2, 1),
('Wireless Mouse', 'Bluetooth wireless mouse with ergonomic design', 29.99, 200, 3, 2),
('Mechanical Keyboard', 'RGB mechanical keyboard with blue switches', 89.99, 75, 3, 2),
('Monitor Stand', 'Adjustable monitor stand with storage drawer', 35.50, 120, 1, 1),
('Cable Management Kit', 'Complete cable management solution for office desks', 15.99, 300, 4, 3),
('Desk Organizer', 'Bamboo desk organizer with multiple compartments', 25.99, 150, 4, 3),
('Ergonomic Footrest', 'Adjustable ergonomic footrest for office comfort', 42.99, 80, 1, 4);

-- Insert sample orders
INSERT INTO orders (product_id, quantity, total_amount, customer_name, customer_email, status) VALUES 
(1, 2, 300.00, 'Alice Johnson', 'alice@email.com', 'Delivered'),
(2, 1, 45.99, 'Bob Smith', 'bob@email.com', 'Shipped'),
(3, 5, 149.95, 'Carol Davis', 'carol@email.com', 'Processing'),
(4, 1, 89.99, 'David Brown', 'david@email.com', 'Pending'),
(5, 3, 106.50, 'Eva Wilson', 'eva@email.com', 'Delivered');

-- Insert inventory movements
INSERT INTO