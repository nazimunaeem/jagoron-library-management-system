# 📚 Jagoron Library Management System
### জাগরণ পাঠাগার ব্যবস্থাপনা সিস্টেম

A free, open-source library management system built with PHP & MySQL — designed for small public libraries.

**Live Demo:** [jagoronpathagar.page.gd](https://jagoronpathagar.page.gd)

---

## ✨ Features

| Module | Features |
|--------|----------|
| 📚 Books | Catalog, search, issue/return, overdue tracking |
| 👥 Members | Registration, ID card (3×2"), monthly fees |
| 🎁 Donors | Donation tracking (money + books), certificates |
| 💰 Finance | Income/expense ledger, CSV export |
| 🌐 Public | OPAC, member list, donor list, CMS pages |
| ⚙️ Admin | Multi-admin, settings, bulk card printing |

---

## 🚀 Quick Install

1. Upload all files to `htdocs/`
2. Edit `includes/config.php` with your DB credentials
3. Import `install.sql` in phpMyAdmin
4. Visit your site → login at `/admin/` with `admin` / `password`

See [INSTALL.md](INSTALL.md) for full instructions.

---

## 🌍 Language Support

The system ships with **Bengali (বাংলা)** and **English** built in.

To add a new language, copy `lang/en.php` → `lang/xx.php` and translate the values.

See [lang/README.md](lang/README.md) for details.

---

## 🛠️ Requirements

- PHP 7.4+
- MySQL 5.7+ or MariaDB 10.3+
- Any shared hosting (InfinityFree, HelioHost, etc.)

---

## 📄 License

MIT License — free to use, modify and distribute.

---

*Built for [জাগরণ পাঠাগার](https://jagoronpathagar.page.gd), Debiganj, Panchagarh, Bangladesh.*
