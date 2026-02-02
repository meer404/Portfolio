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
    image_url VARCHAR(500),
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

-- Site Settings table for dynamic content management
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default site settings
INSERT INTO site_settings (setting_key, setting_value) VALUES
-- Hero Section
('hero_greeting', 'Hello, I''m'),
('hero_name', 'John Doe'),
('hero_title', 'Full-Stack Web Developer'),
('hero_description', 'I craft beautiful, responsive, and user-friendly web applications that solve real-world problems and deliver exceptional user experiences.'),
('hero_image', ''),
-- About Section
('about_title', 'A Passionate Developer & Problem Solver'),
('about_paragraph1', 'With over 5 years of experience in web development, I specialize in creating modern, scalable, and user-centric applications. My journey started with a curiosity about how websites work, and it has evolved into a deep passion for crafting digital experiences.'),
('about_paragraph2', 'I believe in writing clean, maintainable code and staying up-to-date with the latest technologies. When I''m not coding, you''ll find me exploring new frameworks, contributing to open-source projects, or sharing knowledge through my blog.'),
('about_experience', '5+ Years Experience'),
('about_projects', '50+ Projects Completed'),
('about_clients', '30+ Happy Clients'),
-- Resume Section (JSON arrays for experience and education)
('resume_experience', '[{"period":"2022 - Present","title":"Senior Full-Stack Developer","company":"Tech Innovations Inc.","description":"Leading development of enterprise web applications, mentoring junior developers, and implementing best practices."},{"period":"2020 - 2022","title":"Full-Stack Developer","company":"Digital Solutions Co.","description":"Developed and maintained multiple client websites and web applications using PHP, JavaScript, and modern frameworks."},{"period":"2018 - 2020","title":"Junior Web Developer","company":"StartUp Hub","description":"Started my professional journey building responsive websites and learning modern development practices."}]'),
('resume_education', '[{"period":"2014 - 2018","title":"B.S. Computer Science","institution":"State University","description":"Graduated with honors. Focused on software engineering, database systems, and web technologies."},{"period":"2020","title":"AWS Certified Developer","institution":"Amazon Web Services","description":"Professional certification for developing and maintaining applications on AWS."},{"period":"2021","title":"Full-Stack Web Development","institution":"Udemy Certification","description":"Comprehensive course covering modern web development technologies and best practices."}]'),
('resume_file', ''),
-- Contact Section
('contact_email', 'hello@johndoe.com'),
('contact_location', 'San Francisco, CA'),
('contact_phone', '+1 (234) 567-890'),
-- Social Links
('social_github', '#'),
('social_linkedin', '#'),
('social_twitter', '#'),
('social_instagram', '#');

-- Clients table for testimonials/client showcase
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    company VARCHAR(100),
    logo_url VARCHAR(500),
    testimonial TEXT,
    rating INT DEFAULT 5,
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample clients
INSERT INTO clients (name, company, logo_url, testimonial, rating, is_featured) VALUES
('Sarah Johnson', 'Tech Innovations Inc.', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150', 'Working with John was an absolute pleasure. He delivered our e-commerce platform ahead of schedule and exceeded all expectations. His attention to detail and problem-solving skills are exceptional.', 5, 1),
('Michael Chen', 'Digital Solutions Co.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150', 'John transformed our outdated website into a modern, responsive masterpiece. His expertise in both frontend and backend development made the entire process seamless.', 5, 1),
('Emily Rodriguez', 'StartUp Hub', 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150', 'Exceptional work! John built our task management app from scratch, and it has significantly improved our team productivity. Highly recommended!', 5, 1),
('David Park', 'Creative Agency', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150', 'John''s technical skills combined with his creative approach resulted in a website that perfectly represents our brand. Great communication throughout the project.', 4, 0),
('Lisa Thompson', 'E-Store Solutions', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150', 'Professional, reliable, and incredibly talented. John delivered a high-quality product that has helped our business grow exponentially.', 5, 0);

