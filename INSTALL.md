# জাগরণ পাঠাগার Library System v8
## সম্পূর্ণ ইনস্টলেশন নির্দেশিকা

---

## নতুন কী এলো (v8)
- ✅ বই দানের সংখ্যা আলাদাভাবে ট্র্যাক হয় (book_count)
- ✅ দাতা সনদে অর্থ + বই উভয় দান দেখায়
- ✅ দান বিবরণীতে বই দানের হিসাব আলাদাভাবে
- ✅ টপ দানকারীতে অর্থ + বই উভয় দেখায়
- ✅ বাল্ক আইডি কার্ড প্রিন্ট (JP001–JP100 পরিসর দিয়ে)
- ✅ দান যোগে স্বয়ংক্রিয় ফিল্ড পরিবর্তন (অর্থ/বই/অন্যান্য)

---

## প্রথমবার ইনস্টল (Fresh Install)

### ধাপ ১ — ডেটাবেস তৈরি
InfinityFree cPanel → **MySQL Databases**:
1. নতুন ডেটাবেস তৈরি করুন
2. নতুন user তৈরি + password দিন
3. User কে DB তে যোগ করুন → **All Privileges**
4. নিচের তথ্য নোট করুন:
```
DB Host:     sql204.infinityfree.com
DB Name:     if0_41463272_library
DB User:     if0_41463272
DB Password: আপনার পাসওয়ার্ড
```

### ধাপ ২ — config.php সম্পাদনা
`includes/config.php` খুলুন:
```php
define('DB_HOST', 'sql204.infinityfree.com');
define('DB_USER', 'if0_41463272');
define('DB_PASS', 'আপনার_পাসওয়ার্ড');
define('DB_NAME', 'if0_41463272_library');
```

### ধাপ ৩ — ফাইল আপলোড
InfinityFree **File Manager → htdocs/** এ সব ফাইল আপলোড করুন:
```
htdocs/
├── index.php
├── login.php
├── books.php
├── page.php
├── members-public.php
├── donors-public.php
├── INSTALL.md
├── install.sql
├── patch_v8.sql
├── admin/
├── member/
├── includes/
├── assets/
└── uploads/logo/    ← এই ফোল্ডার নিশ্চিত করুন
```

### ধাপ ৪ — SQL Import
phpMyAdmin → আপনার DB → **Import** → `install.sql` → **Go**

### ধাপ ৫ — লগইন
```
URL:      https://yourdomain.com/admin/
Username: admin
Password: password
```
**প্রথম লগইনে পাসওয়ার্ড বদলান!** Admin → Settings

---

## আপগ্রেড (v7 থেকে v8)

### ⚠️ গুরুত্বপূর্ণ — আগে SQL Patch চালান!

**ধাপ ১ — SQL Patch চালান (আগে!)**
phpMyAdmin → আপনার DB → **SQL** ট্যাব → নিচেরটি paste করুন → **Go**:
```sql
ALTER TABLE `donations` ADD COLUMN IF NOT EXISTS `book_count` int(11) DEFAULT 0 AFTER `amount`;
UPDATE `donations` SET `book_count` = 1 WHERE `type` = 'book' AND (`book_count` IS NULL OR `book_count` = 0);
```

**ধাপ ২ — নতুন ফাইল আপলোড করুন**
এই ফাইলগুলো replace করুন (htdocs/ এ):
```
admin/add_donation.php   ← বই দানের সংখ্যা
admin/add_donor.php      ← বই দান সাপোর্ট
admin/donor_certificate.php ← অর্থ+বই উভয়
admin/donor_statement.php   ← অর্থ+বই উভয়
admin/donor_detail.php      ← অর্থ+বই উভয়
admin/donors.php            ← বই কলাম যোগ
admin/bulk_cards.php        ← নতুন! বাল্ক প্রিন্ট
admin/index.php             ← টপ দানকারী আপডেট
includes/sidebar.php        ← বাল্ক কার্ড মেনু
includes/install.sql        ← আপডেট
index.php                   ← টপ দানকারী আপডেট
donors-public.php           ← বই দান দেখায়
```

---

## বাল্ক কার্ড প্রিন্ট
**Admin → বাল্ক কার্ড প্রিন্ট**

1. পরিসর দিন (যেমন: শুরু: 1, শেষ: 50 = JP0001–JP0050)
2. "প্রিভিউ দেখুন" চাপুন
3. "প্রিন্ট করুন" চাপুন — নতুন ট্যাবে খুলবে, স্বয়ংক্রিয় প্রিন্ট শুরু হবে
4. প্রিন্ট সেটিং: Scale 100%, Margins: None বা Minimum

---

## URL সমূহ

| পেজ | URL |
|---|---|
| হোমপেজ | `/` |
| সব বই | `/books.php` |
| সদস্য তালিকা | `/members-public.php` |
| দাতা তালিকা | `/donors-public.php` |
| লগইন | `/login.php` |
| অ্যাডমিন | `/admin/` |
| বাল্ক কার্ড | `/admin/bulk_cards.php` |

---

## সমস্যা সমাধান

| সমস্যা | সমাধান |
|---|---|
| book_count কলাম নেই | `patch_v8.sql` চালান |
| বাল্ক কার্ড দেখাচ্ছে না | member_id ঠিকমতো আছে কিনা দেখুন |
| দান যোগ হচ্ছে না | book type এ book_count > 0 দিন |
| কার্ড প্রিন্টে কাটা যাচ্ছে | Scale: 100%, Fit to page বন্ধ করুন |
