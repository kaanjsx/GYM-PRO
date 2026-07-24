-- GYM PRO FINAL DATABASE
DROP DATABASE IF EXISTS gym_db;
CREATE DATABASE gym_db;
USE gym_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'pt', 'member') DEFAULT 'member',
    avatar VARCHAR(255) DEFAULT 'default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE user_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    weight DECIMAL(5,2),
    fat_ratio DECIMAL(4,2),
    date DATE DEFAULT CURRENT_DATE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pt_id INT NOT NULL,
    member_id INT NOT NULL,
    program_name VARCHAR(100),
    details JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pt_id) REFERENCES users(id),
    FOREIGN KEY (member_id) REFERENCES users(id)
);

CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pt_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pt_id) REFERENCES users(id)
);


INSERT INTO users (name, email, password, role) VALUES 
('Yönetici', 'admin@gym.com', '$2y$10$8.0/8.0/8.0/8.0/8.0/8.0.q1', 'admin'),
('Ahmet Hoca', 'pt@gym.com', '$2y$10$8.0/8.0/8.0/8.0/8.0/8.0.q1', 'pt'),
('Mehmet Üye', 'uye@gym.com', '$2y$10$8.0/8.0/8.0/8.0/8.0/8.0.q1', 'member');

INSERT INTO user_stats (user_id, weight, fat_ratio, date) VALUES 
(3, 85.0, 20.0, DATE_SUB(NOW(), INTERVAL 1 MONTH)),
(3, 83.5, 18.5, NOW());
