-- Multi-Language Support Migration
-- Run this in phpMyAdmin or MySQL CLI to add Kurdish content columns

USE portfolio_db;

-- Projects: Add Kurdish columns
ALTER TABLE projects 
ADD COLUMN title_ku VARCHAR(255) AFTER title,
ADD COLUMN description_ku TEXT AFTER description;

-- Blogs: Add Kurdish columns
ALTER TABLE blogs 
ADD COLUMN title_ku VARCHAR(255) AFTER title,
ADD COLUMN content_ku TEXT AFTER content;

-- Clients: Add Kurdish column
ALTER TABLE clients 
ADD COLUMN company_ku VARCHAR(100) AFTER company;
