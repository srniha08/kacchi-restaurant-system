-- Create Database
CREATE DATABASE IF NOT EXISTS kacchi_restaurant;
USE kacchi_restaurant;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menu items table with UNIQUE constraint
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(50) NOT NULL,
    image_url VARCHAR(500),
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_name_category (name, category) -- Prevents duplicates
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_address TEXT NOT NULL,
    special_instructions TEXT,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'preparing', 'delivered') DEFAULT 'pending',
    order_number VARCHAR(20) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    menu_item_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
);

-- Insert default admin user
INSERT INTO admin_users (username, password) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert sample menu items (using INSERT IGNORE to avoid duplicates)
INSERT IGNORE INTO menu_items (name, description, price, category, image_url) VALUES
('Shahi Ghorer Kacchi', 'The royal treatment in every bite with aromatic basmati rice', 220.00, 'Signature Kacchi', ''),
('Mutton Kacchi (Royal Treatment)', 'Premium mutton with aromatic rice and secret spices', 250.00, 'Signature Kacchi', ''),
('Kacchi Biryani (Grandma''s Secret)', 'Traditional recipe passed down generations with perfect rice', 200.00, 'Signature Kacchi', ''),
('Emotional Morog Polao', 'A taste of nostalgia that hits right in the feels with fluffy rice', 180.00, 'Weekly Special', ''),
('Borhani the Life Saver', 'The perfect companion to your Kacchi', 40.00, 'Sides & Drinks', ''),
('Mojo for Mojo', 'Refresh yourself with this classic drink', 30.00, 'Sides & Drinks', ''),
('Salad on the Side', 'Fresh veggies to balance your meal', 20.00, 'Sides & Drinks', '');