<?php
/**
 * Jagoron Library Management System
 * Language File: English (en)
 *
 * To add a new language:
 * 1. Copy this file: lang/en.php → lang/fr.php (or bn.php, ar.php, etc.)
 * 2. Translate all values (right side of =>)
 * 3. In includes/config.php, set: define('LANG', 'fr');
 * 4. Use t('key') in your PHP files to output translated text
 *
 * Do NOT change the keys (left side of =>)
 */
return [

    // ===== LIBRARY =====
    'library_name'      => 'Jagoron Pathagar',
    'tagline'           => 'A Book, An Awakening',
    'address'           => 'Rathbazar (Khaleque\'s Corner), Debiganj, Panchagarh',

    // ===== NAVIGATION =====
    'home'              => 'Home',
    'all_books'         => 'All Books',
    'members'           => 'Members',
    'donors'            => 'Donors',
    'login'             => 'Login',
    'logout'            => 'Logout',
    'admin_panel'       => 'Admin Panel',
    'my_account'        => 'My Account',
    'dashboard'         => 'Dashboard',
    'settings'          => 'Settings',
    'back'              => '← Back',

    // ===== BOOKS =====
    'books'             => 'Books',
    'book_catalog'      => 'Book Catalog',
    'add_book'          => 'Add Book',
    'edit_book'         => 'Edit Book',
    'book_title'        => 'Book Title',
    'author'            => 'Author',
    'publisher'         => 'Publisher',
    'isbn'              => 'ISBN',
    'category'          => 'Category',
    'year'              => 'Year',
    'copies'            => 'Copies',
    'available'         => 'Available',
    'shelf'             => 'Shelf',
    'description'       => 'Description',
    'book_id'           => 'Book ID',
    'available_copies'  => 'Available',
    'not_available'     => 'Not Available',
    'issued_to'         => 'Issued to',
    'latest_books'      => 'Latest Books',
    'all_books_link'    => 'View All Books',

    // ===== MEMBERS =====
    'member'            => 'Member',
    'add_member'        => 'Add Member',
    'edit_member'       => 'Edit Member',
    'member_list'       => 'Member List',
    'member_id'         => 'Member ID',
    'name'              => 'Name',
    'father_name'       => 'Father\'s Name',
    'phone'             => 'Phone',
    'address_field'     => 'Address',
    'email'             => 'Email',
    'join_date'         => 'Join Date',
    'membership_type'   => 'Type',
    'status'            => 'Status',
    'active'            => 'Active',
    'pending'           => 'Pending',
    'suspended'         => 'Suspended',
    'regular'           => 'Regular',
    'student'           => 'Student',
    'senior'            => 'Senior',
    'donor_member'      => 'Donor Member',
    'approval'          => 'Approval',
    'approve'           => 'Approve',
    'reject'            => 'Reject',
    'id_card'           => 'ID Card',
    'bulk_cards'        => 'Bulk Card Print',
    'reg_fee'           => 'Registration Fee',
    'monthly_fee'       => 'Monthly Fee',

    // ===== BORROW / RETURN =====
    'issue_book'        => 'Issue Book',
    'return_book'       => 'Return Book',
    'borrow_date'       => 'Issue Date',
    'due_date'          => 'Due Date',
    'return_date'       => 'Return Date',
    'overdue'           => 'Overdue',
    'on_time'           => 'On Time',
    'fine'              => 'Fine',
    'fine_per_day'      => 'Fine per day',
    'waive_fine'        => 'Waive Fine',
    'reissue'           => 'Re-issue (+5 days)',
    'days_late'         => 'Days Late',
    'issue_days'        => 'Default Issue Period (days)',

    // ===== DONORS =====
    'donor'             => 'Donor',
    'donor_list'        => 'Donor List',
    'add_donor'         => 'Add Donor',
    'add_donation'      => 'Add Donation',
    'donation'          => 'Donation',
    'money_donation'    => 'Money',
    'book_donation'     => 'Books',
    'other_donation'    => 'Other',
    'donation_amount'   => 'Amount',
    'book_count'        => 'Number of Books',
    'total_donated'     => 'Total Donated',
    'total_money'       => 'Total Money',
    'total_books'       => 'Total Books',
    'certificate'       => 'Certificate',
    'statement'         => 'Statement',
    'donor_certificate' => 'Donor Certificate',
    'donation_statement'=> 'Donation Statement',
    'top_donors'        => 'Top Donors',

    // ===== FINANCE =====
    'finance'           => 'Finance',
    'income'            => 'Income',
    'expense'           => 'Expense',
    'balance'           => 'Balance',
    'total_income'      => 'Total Income',
    'total_expense'     => 'Total Expense',
    'add_entry'         => 'Add Entry',
    'date'              => 'Date',
    'amount'            => 'Amount (৳)',
    'details'           => 'Description',
    'export_csv'        => 'Export CSV',

    // ===== ADMIN =====
    'admin'             => 'Admin',
    'admins'            => 'Admins',
    'add_admin'         => 'Add Admin',
    'username'          => 'Username',
    'password'          => 'Password',
    'old_password'      => 'Old Password',
    'new_password'      => 'New Password',
    'change_password'   => 'Change Password',
    'save_settings'     => 'Save Settings',
    'allow_delete'      => 'Enable Delete Option',

    // ===== PAGES / CMS =====
    'pages'             => 'Pages',
    'add_page'          => 'Add Page',
    'page_title'        => 'Title',
    'page_slug'         => 'Slug (URL)',
    'page_content'      => 'Content',
    'published'         => 'Published',
    'draft'             => 'Draft',
    'about'             => 'About',
    'rules'             => 'Rules',
    'committee'         => 'Committee',

    // ===== MESSAGES =====
    'success_added'     => 'Added successfully!',
    'success_updated'   => 'Updated successfully!',
    'success_deleted'   => 'Deleted successfully!',
    'error_required'    => 'Required fields are missing.',
    'error_not_found'   => 'Record not found.',
    'confirm_delete'    => 'Are you sure you want to delete?',
    'no_records'        => 'No records found.',
    'login_error'       => 'Invalid username or password.',
    'login_btn'         => 'Login',

    // ===== STATS =====
    'total_books_stat'  => 'Total Books',
    'total_members_stat'=> 'Active Members',
    'issued_books'      => 'Issued Books',
    'overdue_books'     => 'Overdue',
    'top_readers'       => 'Top Readers',
    'most_read'         => 'Most Read',

    // ===== CARD =====
    'card_front'        => 'Front',
    'card_back'         => 'Back',
    'print_card'        => 'Print Card',
    'save_pdf'          => 'Save as PDF',
    'card_size_note'    => 'Card size: 3×2 inches. Print at 100% scale.',
    'rules_title'       => 'Library Rules',
];
