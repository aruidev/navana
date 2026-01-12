DROP DATABASE IF EXISTS Pt05_Alex_Ruiz;
CREATE DATABASE IF NOT EXISTS Pt05_Alex_Ruiz;
USE Pt05_Alex_Ruiz;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);

-- Create items table
CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    link TEXT NOT NULL,
    tag VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    user_id INT NULL,
    INDEX (user_id),
    CONSTRAINT fk_items_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Create remember_me_tokens table
CREATE TABLE remember_me_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    selector CHAR(24) NOT NULL UNIQUE,
    validator_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    CONSTRAINT fk_rmt_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);