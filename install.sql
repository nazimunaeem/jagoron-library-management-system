-- জাগরণ পাঠাগার Library System v7
-- Run this ONCE in phpMyAdmin → Import
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `book_id` varchar(20) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `isbn` varchar(50) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `year` year DEFAULT NULL,
  `copies` int(11) DEFAULT 1,
  `available` int(11) DEFAULT 1,
  `shelf` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `book_id` (`book_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` varchar(20) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `membership_type` enum('regular','student','senior','donor') DEFAULT 'regular',
  `join_date` date DEFAULT NULL,
  `status` enum('active','pending','suspended') DEFAULT 'pending',
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `is_donor` tinyint(1) DEFAULT 0,
  `reg_fee_paid` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_id` (`member_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `monthly_fees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `month` varchar(2) NOT NULL,
  `year` varchar(4) NOT NULL,
  `amount` int(11) NOT NULL,
  `paid_date` date NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_month_year` (`member_id`,`month`,`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `borrows` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `book_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `borrow_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `fine` int(11) DEFAULT 0,
  `fine_waived` tinyint(1) DEFAULT 0,
  `fine_paid` tinyint(1) DEFAULT 0,
  `reissued` tinyint(1) DEFAULT 0,
  `status` enum('borrowed','returned') DEFAULT 'borrowed',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `finance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `amount` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `donation_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `donations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `donor_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `type` enum('money','book','other') DEFAULT 'money',
  `amount` int(11) DEFAULT 0,
  `book_count` int(11) DEFAULT 0,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `settings` (
  `skey` varchar(100) NOT NULL,
  `svalue` text NOT NULL,
  PRIMARY KEY (`skey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_published` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default settings
INSERT IGNORE INTO `settings` (`skey`, `svalue`) VALUES
('library_name_bn', 'জাগরণ পাঠাগার'),
('library_name_en', 'Jagoron Pathagar'),
('tagline', 'একটি বই একটি জাগরণ'),
('address', 'রথবাজার (খালেকের মোড়), দেবীগঞ্জ, পঞ্চগড়'),
('reg_fee', '100'),
('monthly_fee', '30'),
('fine_per_day', '2'),
('issue_days', '15'),
('logo', ''),
('allow_delete', '0');

-- Default admin (password: password)
INSERT IGNORE INTO `admins` (`username`, `password`, `name`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator');

-- Default pages
INSERT IGNORE INTO `pages` (`slug`, `title`, `content`, `sort_order`) VALUES
('about', 'পাঠাগার সম্পর্কে', '<p><strong>জাগরণ পাঠাগার</strong> রথবাজার (খালেকের মোড়), দেবীগঞ্জ, পঞ্চগড়-এ অবস্থিত একটি গণগ্রন্থাগার।</p><p>আমাদের লক্ষ্য জ্ঞান ও শিক্ষার আলো ছড়িয়ে দেওয়া। পাঠাগারটি সকলের জন্য উন্মুক্ত।</p>', 1),
('committee', 'পরিচালনা কমিটি', '<p>কমিটির তালিকা এখানে লিখুন।</p>', 2),
('rules', 'নিয়মকানুন', '<p><strong>বই ইস্যুর নিয়ম:</strong></p><ul><li>একজন সদস্য একসাথে একটি বই নিতে পারবেন।</li><li>বই ইস্যুর মেয়াদ ১৫ দিন।</li><li>নির্ধারিত সময়ের পর প্রতিদিন ২ টাকা জরিমানা।</li><li>নিবন্ধন ফি: ১০০ টাকা (প্রথম মাস বিনামূল্যে)।</li><li>একজন সদস্য মাত্র একবার ৫ দিনের জন্য পুনরায় ইস্যু করতে পারবেন।</li></ul>', 3),
('donors', 'দাতা সদস্য তালিকা', '<p>আমাদের সম্মানিত দাতাগণ।</p>', 4),
('members-list', 'সদস্য তালিকা', '<p>সকল সদস্যের তালিকা।</p>', 5);

SET FOREIGN_KEY_CHECKS = 1;

