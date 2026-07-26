-- Database schema update for AI Based Smart Farming Information Portal

-- Ensure users table has the role field if it doesn't already
-- ALTER TABLE users ADD COLUMN role ENUM('Farmer', 'Customer') NOT NULL AFTER username;

-- 1. Farm Products Table
CREATE TABLE IF NOT EXISTS farm_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    category ENUM('Vegetables', 'Fruits', 'Grains', 'Dairy', 'Other') NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    unit VARCHAR(50) DEFAULT 'kg',
    quantity_available DECIMAL(10, 2) NOT NULL,
    product_image VARCHAR(255) DEFAULT 'default_product.jpg',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 2. Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    farmer_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('Pending', 'Confirmed', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES farm_products(id) ON DELETE CASCADE
);

-- 3. Subscription Plans
CREATE TABLE IF NOT EXISTS subscription_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_name VARCHAR(255) NOT NULL,
    duration_days INT NOT NULL, -- e.g. 7 for weekly, 30 for monthly
    price DECIMAL(10, 2) NOT NULL,
    description TEXT
);

-- 4. Customer Subscriptions
CREATE TABLE IF NOT EXISTS customer_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    plan_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('Active', 'Expired', 'Cancelled') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id) ON DELETE CASCADE
);

-- 5. Machinery Rental Items
CREATE TABLE IF NOT EXISTS machinery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL, -- The farmer who owns it
    machinery_name VARCHAR(255) NOT NULL,
    rental_price_per_day DECIMAL(10, 2) NOT NULL,
    availability_status ENUM('Available', 'Rented', 'Maintenance') DEFAULT 'Available',
    description TEXT,
    image VARCHAR(255) DEFAULT 'default_machinery.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 6. AI Crop Recommendations (Caching/Logging)
CREATE TABLE IF NOT EXISTS ai_recommendations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    soil_type VARCHAR(100) NOT NULL,
    weather_condition VARCHAR(100) NOT NULL,
    recommended_crop VARCHAR(255) NOT NULL,
    confidence_score DECIMAL(5, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 7. Chat Messages
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);
