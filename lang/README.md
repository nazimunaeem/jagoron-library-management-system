# 🌍 Translation / Language Guide

## Currently Available

| Code | Language | File |
|------|----------|------|
| `bn` | বাংলা (Bengali) — Default | `lang/bn.php` |
| `en` | English | `lang/en.php` |

---

## How to Add a New Language

### Step 1 — Copy the English file
```
lang/en.php  →  lang/fr.php   (French)
lang/en.php  →  lang/ar.php   (Arabic)
lang/en.php  →  lang/hi.php   (Hindi)
lang/en.php  →  lang/ur.php   (Urdu)
lang/en.php  →  lang/zh.php   (Chinese)
```

### Step 2 — Translate the values
Open your new file and translate everything on the **right side** of `=>`.
**Do NOT change the keys** (left side).

```php
// ✅ Correct — translate the value
'home' => 'Accueil',       // French
'home' => 'الرئيسية',      // Arabic

// ❌ Wrong — never change the key
'home_page' => 'Home',
```

### Step 3 — Set language in config.php
Open `includes/config.php` and add:

```php
define('LANG', 'fr');   // use your language code
```

### Step 4 — Use translations in PHP files
```php
// Load translations
$t = require 'lang/' . LANG . '.php';

// Use in HTML
echo $t['home'];
echo $t['login'];
echo $t['add_book'];
```

---

## Want to Contribute?
Submit a Pull Request with your new `lang/xx.php` file!

### Language Codes Reference
| Code | Language |
|------|----------|
| `ar` | Arabic |
| `fr` | French |
| `hi` | Hindi |
| `ur` | Urdu |
| `zh` | Chinese |
| `es` | Spanish |
| `pt` | Portuguese |
| `ru` | Russian |
| `tr` | Turkish |
