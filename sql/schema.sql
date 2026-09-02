-- Decentralized Trust Attorneys - Demo Schema
-- Import this file via cPanel > phpMyAdmin (select your DB, then Import)

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS applications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  entity_type VARCHAR(30) NOT NULL,
  business_name VARCHAR(200) NOT NULL,
  state VARCHAR(100) NOT NULL,
  owner_name VARCHAR(150) NOT NULL,
  owner_email VARCHAR(150) NOT NULL,
  owner_phone VARCHAR(30) DEFAULT NULL,
  address VARCHAR(255) DEFAULT NULL,
  status ENUM('pending','in_review','approved','rejected') NOT NULL DEFAULT 'pending',
  admin_notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_app_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed demo admin login: username = admin   password = Admin@123
-- (change this password after importing, via a new bcrypt hash)
INSERT INTO admins (username, password_hash) VALUES
('admin', '$2b$10$I7JUnKAaOK9LseJt.zEUfOz3fDEfvtvfn1qoKUSXtGdPyLK4tvTPO')
ON DUPLICATE KEY UPDATE username = username;
