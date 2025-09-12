-- Database: riohotel
CREATE DATABASE IF NOT EXISTS riohotel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE riohotel;

-- Users table
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','guest','housekeeper','receptionist') NOT NULL DEFAULT 'guest',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Seed an initial admin (password: Admin@123) if not exists
INSERT INTO users (username,email,password_hash,role)
SELECT 'admin','admin@example.com',
       '$2y$10$2q6T1nZb2JYwYQ17SX47oO3TmA5fDdh3zYQcE0Y8JD7rStn5WiKdy','admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username='admin');
