-- Migration: Add content, github_link, and technologies fields to projects table
-- Run this in phpMyAdmin or MySQL command line

ALTER TABLE `projects` 
ADD COLUMN `content` TEXT NULL AFTER `description_ku`,
ADD COLUMN `content_ku` TEXT NULL AFTER `content`,
ADD COLUMN `github_link` VARCHAR(500) NULL AFTER `project_link`,
ADD COLUMN `technologies` VARCHAR(500) NULL AFTER `github_link`;
