-- v8b patch: link finance ↔ donations
-- Run in phpMyAdmin → SQL tab

-- Add donation_id reference column to finance
ALTER TABLE `finance` ADD COLUMN IF NOT EXISTS `donation_id` int(11) DEFAULT NULL AFTER `member_id`;

-- Add index for faster lookup
ALTER TABLE `finance` ADD INDEX IF NOT EXISTS `donation_id` (`donation_id`);

SELECT 'Patch v8b applied!' as status;
