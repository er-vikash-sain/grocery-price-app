-- Migration: Add product image upload support
-- This migration updates the products table to better support file uploads

-- Alter the image column to store uploaded file paths
-- The column will store relative paths like 'uploads/products/filename.jpg'
ALTER TABLE products 
MODIFY COLUMN image VARCHAR(500) NULL 
COMMENT 'Relative path to uploaded product image';

-- Add an index for faster image lookups if needed
-- ALTER TABLE products ADD KEY idx_products_image (image);
