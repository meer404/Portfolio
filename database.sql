-- Portfolio Database Setup
-- Run this script in phpMyAdmin or MySQL CLI

CREATE DATABASE IF NOT EXISTS portfolio_db;
USE portfolio_db;

-- Projects table for portfolio items
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    project_link VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Blogs table for blog posts
CREATE TABLE IF NOT EXISTS blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Messages table for contact form submissions
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_name VARCHAR(100) NOT NULL,
    sender_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    message_text TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admins table for authentication
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (password: admin123)
-- Note: If login fails, run setup_admin.php to reset the password
INSERT INTO admins (username, password, email) VALUES
('admin', '$2y$10$xLRsYP9hJhGKfuWRqyUK0ulIbEgYU8.U.D6YZG6.JNL5n6InqyDOy', 'admin@example.com');

-- Insert sample projects
INSERT INTO projects (title, description, image_url, project_link) VALUES
('E-Commerce Platform', 'A full-featured online store with cart, checkout, and payment integration. Built with PHP and MySQL.', 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=600', 'https://github.com'),
('Task Management App', 'A productivity app for managing tasks and projects with team collaboration features.', 'https://images.unsplash.com/photo-1611224923853-80b023f02d71?w=600', 'https://github.com'),
('Weather Dashboard', 'Real-time weather application with location-based forecasts and interactive maps.', 'https://images.unsplash.com/photo-1504608524841-42fe6f032b4b?w=600', 'https://github.com'),
('Social Media Dashboard', 'Analytics dashboard for tracking social media metrics and engagement.', 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=600', 'https://github.com'),
('Portfolio Website', 'A responsive personal portfolio website built with modern web technologies.', 'https://images.unsplash.com/photo-1467232004584-a241de8bcf5d?w=600', 'https://github.com'),
('Blog Platform', 'A content management system for creating and managing blog posts.', 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?w=600', 'https://github.com');

-- Insert sample blog posts
INSERT INTO blogs (title, content) VALUES
('Getting Started with Web Development', 'Web development is an exciting field that combines creativity with technical skills. In this post, I will share my journey and tips for beginners looking to start their career in web development. From learning HTML and CSS to mastering JavaScript and backend technologies, the path is filled with continuous learning and growth.'),
('The Power of Clean Code', 'Writing clean, maintainable code is essential for any developer. Clean code not only makes your projects easier to understand but also simplifies debugging and collaboration. In this article, I discuss best practices for writing code that stands the test of time.'),
('Modern CSS Techniques', 'CSS has evolved significantly over the years. With features like Flexbox, Grid, and CSS Variables, creating complex layouts has never been easier. Let me walk you through some of my favorite modern CSS techniques that have transformed how I build websites.'),
('Building RESTful APIs with PHP', 'APIs are the backbone of modern web applications. In this tutorial, I explain how to design and build robust RESTful APIs using PHP and best practices for security and performance optimization.');
