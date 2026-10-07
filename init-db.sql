USE pharmacy;

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'pharmacist', 'cashier') NOT NULL DEFAULT 'cashier',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS suppliers (
    supplier_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
);

CREATE TABLE IF NOT EXISTS medicines (
    medicine_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    generic_name VARCHAR(150),
    category_id INT,
    supplier_id INT,
    batch_number VARCHAR(50),
    quantity INT NOT NULL DEFAULT 0,
    unit VARCHAR(20) DEFAULT 'tablet',
    cost_price DECIMAL(10,2) NOT NULL DEFAULT 0,
    selling_price DECIMAL(10,2) NOT NULL DEFAULT 0,
    manufacturing_date DATE,
    expiry_date DATE NOT NULL,
    reorder_level INT DEFAULT 10,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE SET NULL,
    INDEX idx_expiry (expiry_date),
    INDEX idx_quantity (quantity)
);

CREATE TABLE IF NOT EXISTS sales (
    sale_id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    customer_name VARCHAR(100),
    customer_phone VARCHAR(20),
    user_id INT,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    discount DECIMAL(10,2) DEFAULT 0,
    payment_method ENUM('cash', 'card', 'mobile') DEFAULT 'cash',
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS sale_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(sale_id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(medicine_id) ON DELETE RESTRICT
);

-- ទិន្នន័យគំរូ
INSERT INTO users (username, password, full_name, role) VALUES
('admin', 'admin123', 'System Administrator', 'admin'),
('pharmacist', 'pharma123', 'Dr. Sok Dara', 'pharmacist'),
('cashier', 'cash123', 'Ms. Chan Srey', 'cashier');

INSERT INTO categories (name, description) VALUES
('ថ្នាំបំបាត់ការឈឺចាប់', 'Painkillers and analgesics'),
('ថ្នាំផ្តាសាយ', 'Cold and flu medicines'),
('វីតាមីន', 'Vitamins and supplements'),
('ថ្នាំអង់ទីប៊ីយ៉ូទិក', 'Antibiotics'),
('ថ្នាំក្អក', 'Cough medicines');

INSERT INTO suppliers (name, contact_person, phone, email, address) VALUES
('Pharma Import Co.', 'Mr. Kim Heng', '012345678', 'info@pharmaimp.com', 'Phnom Penh'),
('MediSupply Ltd.', 'Ms. Sok Chenda', '098765432', 'contact@medisupply.com', 'Phnom Penh'),
('Health Plus', 'Mr. Ly Vann', '011222333', 'sales@healthplus.com', 'Siem Reap');

INSERT INTO medicines (name, generic_name, category_id, supplier_id, batch_number, quantity, unit, cost_price, selling_price, expiry_date, reorder_level) VALUES
('Panadol', 'Paracetamol 500mg', 1, 1, 'BATCH001', 500, 'tablet', 0.05, 0.10, '2027-12-31', 50),
('Amoxicillin', 'Amoxicillin 500mg', 4, 2, 'BATCH002', 200, 'capsule', 0.15, 0.30, '2026-06-30', 30),
('Vitamin C', 'Ascorbic Acid 500mg', 3, 1, 'BATCH003', 300, 'tablet', 0.08, 0.15, '2027-03-31', 40),
('Cough Syrup', 'Dextromethorphan', 5, 3, 'BATCH004', 100, 'bottle', 1.50, 2.50, '2026-09-30', 20),
('Paracetamol', 'Paracetamol 500mg', 1, 2, 'BATCH005', 80, 'tablet', 0.05, 0.10, '2027-08-31', 50),
('Ibuprofen', 'Ibuprofen 400mg', 1, 1, 'BATCH006', 250, 'tablet', 0.10, 0.20, '2027-05-31', 40);
