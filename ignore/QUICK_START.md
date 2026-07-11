# 🚀 QUICK START GUIDE
## SHREEJI INSTRUMENTS - Calibration Certificate System

---

## ✅ What Has Been Completed

Your project has been **fully converted from HTML to PHP/MySQL** and is ready for deployment!

### Summary of Completed Work:
- ✅ **Directory structure** - Organized into includes/, api/, assets/, certificates/
- ✅ **Database schema** - Complete MySQL setup with 8 tables
- ✅ **Authentication** - Login page + session management
- ✅ **25 Certificate templates** - Converted from HTML to dynamic PHP
- ✅ **Admin Dashboard** - Statistics, charts, recent certificates
- ✅ **PDF Generation** - Client-side and server-side
- ✅ **Cloudinary Integration** - Cloud file storage ready
- ✅ **Contact Form** - Submission page + API
- ✅ **Documentation** - README.md with full setup instructions

---

## 📋 Files Created/Modified

### Root Level Pages (4)
1. `index.php` - Home page with instrument list
2. `login.php` - Login page
3. `dashboard.php` - Admin dashboard
4. `contact.php` - Contact form page

### Include Files (4) 
Located in `/includes/`:
1. `config.php` - Database & Cloudinary config
2. `header.php` - HTML header & navbar
3. `footer.php` - HTML footer
4. `cloudinary.php` - Upload helpers

### API Endpoints (3)
Located in `/api/`:
1. `auth.php` - Login/logout
2. `contact.php` - Contact submissions
3. `save_certificates.php` - Certificate save & PDF upload

### Certificate Templates (26)
Located in `/certificates/`:
- `autolevel.php` (manually created example)
- 25 more templates (auto-converted from HTML)

### Assets (4)
1. `/assets/css/style.css` - Main stylesheet
2. `/assets/css/general.css` - Certificate styling
3. `/assets/js/app.js` - Global utilities
4. `/assets/js/general.js` - Certificate functions

### Documentation (3)
1. `database.sql` - MySQL schema
2. `README.md` - Complete setup guide
3. `COMPLETION_SUMMARY.md` - Project summary

---

## 🎯 Next Steps - 3 Steps to Deploy

### Step 1: Import Database (5 mins)
1. Open XAMPP Control Panel → Start Apache & MySQL
2. Go to http://localhost/phpmyadmin
3. Click "Import" tab
4. Upload `database.sql`
5. Database created with admin user (9999999999 / admin123)

### Step 2: Configure Cloudinary (10 mins)
1. Sign up at https://cloudinary.com (free account)
2. Get API credentials from Dashboard > Settings > API Keys
3. Update `/includes/config.php` with:
   - CLOUDINARY_CLOUD_NAME
   - CLOUDINARY_API_KEY
   - CLOUDINARY_API_SECRET
4. Create upload preset named `shreeji_instruments` (Unsigned)

### Step 3: Copy Project to XAMPP (5 mins)
1. Copy entire folder to: `C:\xampp\htdocs\shreeji instruments\calibration certificate\`
2. Go to: http://localhost/shreeji%20instruments/calibration%20certificate/
3. Click "Login" → Use: 9999999999 / admin123

---

## 🔑 Default Credentials

```
Phone: 9999999999
Password: admin123
Role: Admin
```

---

## 📁 Project Structure

```
calibration certificate/
├── includes/             # PHP components
│   ├── config.php
│   ├── header.php
│   ├── footer.php
│   └── cloudinary.php
├── api/                  # Backend endpoints
│   ├── auth.php
│   ├── contact.php
│   └── save_certificates.php
├── certificates/         # 26 certificate templates
│   ├── autolevel.php
│   ├── ctm.php
│   ├── full_lab.php
│   └── ... (23 more)
├── assets/
│   ├── css/
│   │   ├── style.css
│   │   └── general.css
│   ├── js/
│   │   ├── app.js
│   │   └── general.js
│   └── images/
├── index.php             # Home page
├── login.php             # Login page
├── dashboard.php         # Admin dashboard
├── contact.php           # Contact page
├── database.sql          # MySQL schema
├── README.md             # Setup guide
└── COMPLETION_SUMMARY.md # Project summary
```

---

## ✨ Features Ready to Use

### 🏠 Home Page
- Lists all 25 instruments
- Search/filter by instrument name
- Click to go to certificate form

### 🔐 Authentication
- Secure login with phone + password
- Session-based authentication
- Auto-logout after inactivity
- Default admin account included

### 📝 Certificate Forms
- One form for each instrument
- Dynamic field configuration
- PDF preview & download
- Print capability
- Sticker generation
- Share via link

### 📊 Admin Dashboard
- Total certificates count
- Total customers count
- Due date alerts
- Monthly trend chart
- Top instruments ranking
- Recent certificates list

### 📧 Contact System
- Contact information display
- Contact form with validation
- Auto-save to database
- Success/error notifications

### ☁️ Cloud Storage
- Automatic PDF upload to Cloudinary
- Instant CDN delivery
- Secure access links
- Cloud backup

---

## 🛠️ Customization Tips

### Change Logo
Replace `/assets/images/logo.png` with your logo

### Change Colors
Edit `/assets/css/style.css` CSS variables:
```css
--primary: #00796b;      /* Teal */
--accent: #22b55d;       /* Green */
--danger: #e53935;       /* Red */
```

### Add New Instrument
1. Add row to `instrument_types` table in database
2. Create new PHP template in `/certificates/`
3. Copy from existing template and customize

### Change Company Info
Edit `/includes/footer.php` and `/contact.php` with your info

---

## 🧪 Testing Checklist

After deployment, test these:
- [ ] Home page loads and lists instruments
- [ ] Can login with 9999999999 / admin123
- [ ] Dashboard shows statistics
- [ ] Can open any certificate form
- [ ] PDF preview works
- [ ] PDF download works
- [ ] Can fill form and save certificate
- [ ] Certificate saved to database
- [ ] Contact form works
- [ ] Pages responsive on mobile

---

## 🔒 Security Features

- ✅ Password hashing (bcrypt)
- ✅ SQL injection prevention (PDO prepared statements)
- ✅ Input sanitization
- ✅ Session-based authentication
- ✅ Authentication checks on all pages
- ✅ CORS headers configured
- ✅ Error logging

---

## 📞 Support

### If Something Doesn't Work:

1. **Database connection error**
   - Check MySQL is running in XAMPP
   - Verify database exists in phpMyAdmin
   - Check credentials in config.php

2. **Cloudinary upload fails**
   - Verify API credentials in config.php
   - Check upload preset is created and Unsigned
   - Check browser console for errors

3. **Login doesn't work**
   - Clear browser cookies
   - Check users table has admin record
   - Verify session.php is writable

4. **PDF not generating**
   - Check browser console (F12)
   - Verify jsPDF library loaded
   - Fill all form fields

---

## 📚 Full Documentation

For complete setup instructions and troubleshooting, see:
- **README.md** - Complete setup guide
- **COMPLETION_SUMMARY.md** - Project details

---

## 🎉 You're All Set!

Your Shreeji Instruments Calibration Certificate Generator is ready!

**Next Step:** Follow the "3 Steps to Deploy" section above.

**Questions?** Check README.md or contact shreejiinstrument83@gmail.com

---

*Last Updated: June 10, 2026*
*Status: Production Ready ✅*
