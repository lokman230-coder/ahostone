-- E-Invoice Tables
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    type ENUM('invoice','proforma','receipt') DEFAULT 'invoice',
    customer_id INT,
    customer_name VARCHAR(200) NOT NULL,
    customer_email VARCHAR(150),
    customer_tax_number VARCHAR(20),
    customer_tax_office VARCHAR(100),
    customer_address TEXT,
    subtotal DECIMAL(12,2) NOT NULL,
    tax_rate DECIMAL(5,2) DEFAULT 18.00,
    tax_amount DECIMAL(12,2) NOT NULL,
    discount_amount DECIMAL(12,2) DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'TRY',
    status ENUM('draft','sent','paid','cancelled','refunded') DEFAULT 'draft',
    due_date DATE,
    paid_at DATETIME DEFAULT NULL,
    notes TEXT,
    pdf_path VARCHAR(500),
    gib_status VARCHAR(50),
    gib_uuid VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_number (invoice_number),
    INDEX idx_customer (customer_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    product_id INT DEFAULT NULL,
    description VARCHAR(500) NOT NULL,
    quantity DECIMAL(10,2) DEFAULT 1.00,
    unit_price DECIMAL(12,2) NOT NULL,
    tax_rate DECIMAL(5,2) DEFAULT 18.00,
    tax_amount DECIMAL(12,2) DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS proforma_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proforma_number VARCHAR(50) NOT NULL UNIQUE,
    customer_id INT,
    customer_name VARCHAR(200) NOT NULL,
    customer_email VARCHAR(150),
    subtotal DECIMAL(12,2) NOT NULL,
    tax_amount DECIMAL(12,2) DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'TRY',
    valid_until DATE,
    converted_to_invoice_id INT DEFAULT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_number (proforma_number),
    FOREIGN KEY (converted_to_invoice_id) REFERENCES invoices(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
