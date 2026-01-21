-- Migration: Update prices table to use selling_price and mrp instead of price and unit
-- This migration modifies the prices table structure

-- Add new columns
ALTER TABLE prices 
ADD COLUMN selling_price DECIMAL(10,2) NULL COMMENT 'Actual selling price' AFTER price,
ADD COLUMN mrp DECIMAL(10,2) NULL COMMENT 'Maximum Retail Price';

-- Copy existing price data to selling_price
UPDATE prices SET selling_price = price WHERE selling_price IS NULL;

-- Make selling_price NOT NULL after data migration
ALTER TABLE prices 
MODIFY COLUMN selling_price DECIMAL(10,2) NOT NULL COMMENT 'Actual selling price';

-- Drop the old price and unit columns (optional - uncomment if you want to remove them)
-- ALTER TABLE prices DROP COLUMN price;
-- ALTER TABLE prices DROP COLUMN unit;

-- Or rename them for backward compatibility (recommended approach)
ALTER TABLE prices 
CHANGE COLUMN price old_price DECIMAL(10,2) NULL COMMENT 'Deprecated - use selling_price',
CHANGE COLUMN unit old_unit VARCHAR(20) NULL COMMENT 'Deprecated - removed from new entries';
