-- HealthSure Database Schema
-- Health Insurance Management System

CREATE DATABASE IF NOT EXISTS healthsure_db;
USE healthsure_db;

-- Users table (for authentication)
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'agent', 'customer') NOT NULL,
    status ENUM('active', 'blocked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Customers table
CREATE TABLE customers (
    customer_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    agent_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Agents table
CREATE TABLE agents (
    agent_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    branch VARCHAR(100),
    license_number VARCHAR(50),
    hire_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Policies table (Superclass)
CREATE TABLE policies (
    policy_id INT PRIMARY KEY AUTO_INCREMENT,
    policy_name VARCHAR(200) NOT NULL,
    policy_type ENUM('health', 'life', 'family') NOT NULL,
    description TEXT,
    base_premium DECIMAL(10,2) NOT NULL,
    coverage_amount DECIMAL(12,2) NOT NULL,
    duration_years INT NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Health Policy table (Specialization)
CREATE TABLE health_policies (
    policy_id INT PRIMARY KEY,
    hospital_coverage TEXT,
    pre_existing_conditions BOOLEAN DEFAULT FALSE,
    network_hospitals TEXT,
    cashless_limit DECIMAL(10,2),
    FOREIGN KEY (policy_id) REFERENCES policies(policy_id) ON DELETE CASCADE
);

-- Life Policy table (Specialization)
CREATE TABLE life_policies (
    policy_id INT PRIMARY KEY,
    nominee_name VARCHAR(200),
    nominee_relation VARCHAR(50),
    term_years INT,
    maturity_benefit DECIMAL(12,2),
    death_benefit DECIMAL(12,2),
    FOREIGN KEY (policy_id) REFERENCES policies(policy_id) ON DELETE CASCADE
);

-- Family Policy table (Specialization)
CREATE TABLE family_policies (
    policy_id INT PRIMARY KEY,
    no_of_dependents INT DEFAULT 0,
    maternity_cover BOOLEAN DEFAULT FALSE,
    dependent_age_limit INT DEFAULT 25,
    family_floater_sum DECIMAL(12,2),
    FOREIGN KEY (policy_id) REFERENCES policies(policy_id) ON DELETE CASCADE
);

-- Policy Holders (Links customers, policies, and agents)
CREATE TABLE policy_holders (
    holder_id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    policy_id INT NOT NULL,
    agent_id INT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    premium_amount DECIMAL(10,2) NOT NULL,
    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE,
    FOREIGN KEY (policy_id) REFERENCES policies(policy_id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES agents(agent_id) ON DELETE SET NULL
);

-- Claims table
CREATE TABLE claims (
    claim_id INT PRIMARY KEY AUTO_INCREMENT,
    holder_id INT NOT NULL,
    claim_amount DECIMAL(10,2) NOT NULL,
    claim_reason TEXT NOT NULL,
    claim_date DATE NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_amount DECIMAL(10,2) DEFAULT 0,
    documents TEXT,
    admin_notes TEXT,
    processed_by INT,
    processed_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (holder_id) REFERENCES policy_holders(holder_id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Payments table
CREATE TABLE payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    holder_id INT,
    claim_id INT,
    payment_type ENUM('premium', 'claim_settlement') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'card', 'bank_transfer', 'online') NOT NULL,
    transaction_id VARCHAR(100),
    payment_date DATE NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (holder_id) REFERENCES policy_holders(holder_id) ON DELETE CASCADE,
    FOREIGN KEY (claim_id) REFERENCES claims(claim_id) ON DELETE CASCADE
);

-- Support Queries table
CREATE TABLE support_queries (
    query_id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('open', 'in_progress', 'resolved') DEFAULT 'open',
    response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
);

-- Add foreign key for agent assignment to customers
ALTER TABLE customers ADD FOREIGN KEY (agent_id) REFERENCES agents(agent_id) ON DELETE SET NULL;

-- Insert default admin user
INSERT INTO users (email, password_hash, role) VALUES 
('admin@healthsure.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample policies
INSERT INTO policies (policy_name, policy_type, description, base_premium, coverage_amount, duration_years) VALUES
('Basic Health Plan', 'health', 'Comprehensive health coverage for individuals', 5000.00, 500000.00, 1),
('Family Health Shield', 'family', 'Complete health protection for your family', 12000.00, 1000000.00, 1),
('Term Life Insurance', 'life', 'Life insurance with term benefits', 8000.00, 2000000.00, 20),
('Premium Health Plus', 'health', 'Enhanced health coverage with premium benefits', 15000.00, 1500000.00, 1);

-- Insert health policy details
INSERT INTO health_policies (policy_id, hospital_coverage, pre_existing_conditions, network_hospitals, cashless_limit) VALUES
(1, 'All major hospitals covered', FALSE, 'Apollo, Fortis, Max Healthcare', 300000.00),
(4, 'Premium hospital network', TRUE, 'Apollo, Fortis, Max Healthcare, AIIMS', 1000000.00);

-- Insert family policy details
INSERT INTO family_policies (policy_id, no_of_dependents, maternity_cover, dependent_age_limit, family_floater_sum) VALUES
(2, 4, TRUE, 25, 1000000.00);

-- Insert life policy details
INSERT INTO life_policies (policy_id, nominee_name, nominee_relation, term_years, maturity_benefit, death_benefit) VALUES
(3, 'To be updated', 'spouse', 20, 0.00, 2000000.00);
