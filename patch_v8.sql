-- জাগরণ পাঠাগার v8 Patch
-- Run this in phpMyAdmin BEFORE uploading new files
-- এই SQL টি নতুন ফাইল আপলোডের আগে phpMyAdmin এ রান করুন

SET NAMES utf8mb4;

-- Add book_count to donations table
ALTER TABLE `donations` ADD COLUMN IF NOT EXISTS `book_count` int(11) DEFAULT 0 AFTER `amount`;

-- Update existing book donations (set book_count = 1 if type=book and book_count=0)
UPDATE `donations` SET `book_count` = 1 WHERE `type` = 'book' AND (`book_count` IS NULL OR `book_count` = 0);

-- Verify
SELECT 'Patch applied successfully!' as status;
