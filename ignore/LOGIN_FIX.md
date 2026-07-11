# 🔧 LOGIN SETUP GUIDE

## ⚡ Quick Fix

1. **Visit the Setup Page:**
   Open `http://localhost/shreeji%20instruments/calibration%20certificate/setup.php` in your browser

2. **This will:**
   - ✅ Check database connection
   - ✅ Verify tables exist
   - ✅ Create/verify admin user with correct password hash
   - ✅ Test the login flow

3. **Once Setup Completes:**
   - Login at: `http://localhost/shreeji%20instruments/calibration%20certificate/login.php`
   - **Phone:** `9999999999`
   - **Password:** `admin123`

---

## 🗄️ Manual Database Setup (If Needed)

### Option 1: Using phpMyAdmin (Easiest)
1. Open `http://localhost/phpmyadmin`
2. Click **Import** tab
3. Select `database.sql` from your project folder
4. Click **Go** to import
5. Done! Now use the setup page above

### Option 2: Using MySQL Command Line
```bash
mysql -u root < database.sql
```

---

## 🐛 Troubleshooting

### Issue: "Invalid credentials" on login
**Solution:** Run the setup page above - it will automatically create the admin user

### Issue: "Database connection failed"
**Check:**
- XAMPP MySQL is running
- Database credentials in `includes/config.php` are correct:
  ```php
  define('DB_HOST',     'localhost');
  define('DB_USER',     'root');
  define('DB_PASS',     '');  // Empty for XAMPP
  define('DB_NAME',     'shreeji_instruments');
  ```

### Issue: "Table users not found"
**Solution:** Import `database.sql` via phpMyAdmin or command line

---

## 🔐 Changing Admin Password

Edit `setup.php` line 112 and change:
```php
$testPassword = 'admin123';
```

Then visit `setup.php` again to reset with the new password.

---

## 🎯 Testing the Login

1. Open browser console (`F12` → Console tab)
2. Go to `login.php`
3. Enter credentials: `9999999999` / `admin123`
4. Check console for any errors
5. If setup page shows all ✅, contact support if login still fails

---

## ✅ Verification Checklist

- [ ] Setup page shows all green checkmarks ✅
- [ ] Can login with 9999999999 / admin123
- [ ] Redirects to dashboard after login
- [ ] Session persists when navigating to other pages
- [ ] Logout works properly
