# SHREEJI INSTRUMENTS - Calibration Certificate Generator
## Setup & Deployment Guide

---

## 📋 Project Overview

This is a complete PHP/MySQL-based calibration certificate generation system built with:
- **Backend:** PHP 7.4+ (XAMPP)
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, Vanilla JavaScript (ES6+)
- **PDF Generation:** jsPDF
- **File Storage:** Cloudinary

---

## 📁 Directory Structure

```
shreeji instruments/calibration certificate/
├── includes/                 # Shared PHP components
│   ├── config.php           # Database & Cloudinary config
│   ├── header.php           # HTML header & navbar
│   ├── footer.php           # HTML footer
│   └── cloudinary.php       # Cloudinary upload functions
├── api/                     # Backend APIs (JSON responses)
│   ├── auth.php             # Login/logout/session
│   ├── contact.php          # Contact form submission
│   └── save_certificates.php # Certificate save & PDF upload
├── certificates/            # Individual certificate templates (25 PHP files)
│   ├── autolevel.php
│   ├── ctm.php
│   ├── full_lab.php
│   └── ... (22 more instruments)
├── assets/
│   ├── css/
│   │   ├── style.css        # Main stylesheet
│   │   └── general.css      # Certificate form styles
│   ├── js/
│   │   ├── app.js           # Global utilities
│   │   └── general.js       # Certificate form functions
│   └── images/              # Logo, header, footer, stamp, sign
├── uploads/                 # Local file storage (optional)
├── index.php                # Home page - list all instruments
├── login.php                # Login page
├── dashboard.php            # Admin dashboard
├── contact.php              # Contact form page
└── database.sql             # MySQL schema (run once)
```

---

## ⚙️ Setup Instructions

### Step 1: Prepare XAMPP Environment

1. **Install XAMPP** (if not already installed)
   - Download from: https://www.apachefriends.org/download.html
   - Install to default location

2. **Start XAMPP Services**
   - Open XAMPP Control Panel
   - Start **Apache** and **MySQL** services

### Step 2: Create Database

1. **Open phpMyAdmin**
   - Go to: http://localhost/phpmyadmin
   - Login (default: no password for root)

2. **Import Database Schema**
   - Click "Import" tab
   - Upload `database.sql` from the project root
   - Click "Go"
   - You should see the `shreeji_instruments` database created

3. **Verify Database**
   - Click on `shreeji_instruments` database
   - You should see these tables:
     - users (with admin: 9999999999 / admin123)
     - certificates
     - instrument_types
     - parties
     - ctm_readings
     - cube_serials
     - contact_messages
     - certificate_counter

### Step 3: Configure Application

1. **Update config.php** (already configured for XAMPP default)
   - File: `/includes/config.php`
   - **Database settings** (lines 8-12):
     ```php
     define('DB_HOST',     'localhost');  // ✓ Already set
     define('DB_USER',     'root');       // ✓ Already set
     define('DB_PASS',     '');           // ✓ Already set (empty)
     define('DB_NAME',     'shreeji_instruments');
     ```

2. **Set up Cloudinary Integration** (IMPORTANT!)
   - Sign up at: https://cloudinary.com (free account available)
   - Go to Dashboard > Settings > API Keys
   - Update `/includes/config.php` (lines 15-18):
     ```php
     define('CLOUDINARY_CLOUD_NAME', 'YOUR_CLOUD_NAME');    // e.g., deread6ss
     define('CLOUDINARY_API_KEY',    'YOUR_API_KEY');
     define('CLOUDINARY_API_SECRET', 'YOUR_API_SECRET');
     define('CLOUDINARY_UPLOAD_PRESET', 'shreeji_instruments');
     ```
   
   - **Create Unsigned Upload Preset:**
     - Go to Settings > Upload > Add upload preset
     - Name it: `shreeji_instruments`
     - Signing Mode: **Unsigned**
     - Save

### Step 4: Copy Project to XAMPP

1. **Copy to htdocs**
   ```bash
   Copy the entire "calibration certificate" folder to:
   C:\xampp\htdocs\shreeji instruments\calibration certificate\
   ```

2. **Update APP_URL in config.php** (if needed)
   - File: `/includes/config.php` (line 22)
   - Current value: `http://localhost/shreeji%20instruments/calibration%20certificate`
   - This should match your XAMPP web root path with URL-encoded spaces

### Step 5: Verify Installation

1. **Test Database Connection**
   - Go to: http://localhost/shreeji%20instruments/calibration%20certificate/
   - You should see the home page with instrument cards
   - Click on any instrument - it should load the certificate form

2. **Test Login**
   - Click "Login" in navbar
   - Use credentials: `9999999999` / `admin123`
   - You should see the Dashboard

3. **Test Contact Form**
   - Go to: http://localhost/shreeji%20instruments/calibration%20certificate/contact.php
   - Fill form and submit
   - Message should be saved to database

---

## 🔑 Default Credentials

- **Username (Phone):** 9999999999
- **Password:** admin123
- **Role:** Admin

---

## 📝 Using the Application

### For Users (Creating Certificates)

1. **Select Instrument**
   - Go to Home page
   - Click on any instrument card
   - Fill in the calibration form

2. **Generate PDF**
   - Click "Preview" to see PDF
   - Click "Print" to print directly
   - Click "Share PDF" to download/share
   - Click "Generate Sticker" to create info sticker

3. **Save Certificate**
   - Click "Save" button
   - PDF is automatically uploaded to Cloudinary
   - Certificate data is saved to database

4. **View Certificate**
   - Go to Dashboard
   - Click "View PDF" to open saved certificate

### For Admin (Dashboard)

1. **Login**
   - Click "Login" in navbar
   - Use default admin credentials

2. **View Dashboard**
   - See statistics (total certs, due soon, etc.)
   - View charts showing trends
   - See recent certificates
   - View top instruments

---

## 🔧 Troubleshooting

### Database Connection Failed
**Error:** "DB connection failed"
- **Solution:** 
  - Ensure MySQL is running (check XAMPP Control Panel)
  - Verify database credentials in `/includes/config.php`
  - Ensure `shreeji_instruments` database exists in phpMyAdmin

### Cloudinary Upload Not Working
**Error:** "Cloudinary upload failed"
- **Solution:**
  - Verify Cloudinary credentials in `/includes/config.php`
  - Ensure upload preset `shreeji_instruments` exists and is Unsigned
  - Check browser console for API errors

### 404 Error on Certificate Pages
**Error:** "HTTP 404 Not Found"
- **Solution:**
  - Ensure certificate PHP files exist in `/certificates/`
  - Check URL matches file names (e.g., `/certificates/autolevel.php`)
  - Verify all 25 certificate files are converted to `.php`

### Forms Not Submitting
**Error:** "Form submission fails silently"
- **Solution:**
  - Check browser console (F12 > Console) for JavaScript errors
  - Ensure `general.js` is loaded (check Network tab)
  - Verify API endpoints are accessible

### Login Not Working
**Error:** "Invalid credentials" or "Unauthorised"
- **Solution:**
  - Ensure users table exists in database
  - Verify default admin user exists (phone: 9999999999)
  - Check passwords are hashed correctly
  - Clear browser cookies and try again

---

## 🚀 Deployment Checklist

Before going live:

- [ ] Database imported and verified
- [ ] Cloudinary credentials configured
- [ ] APP_URL updated to match server path
- [ ] All 25 certificate templates converted to PHP
- [ ] Login page working with demo credentials
- [ ] Dashboard accessible after login
- [ ] PDF generation working (preview, print, download)
- [ ] Cloudinary upload working
- [ ] Contact form submissions saving to database
- [ ] Mobile responsiveness tested
- [ ] All image assets (logo, stamp, sign) accessible
- [ ] Session management working correctly
- [ ] Error pages show helpful messages

---

## 📱 Mobile Responsiveness

The application is fully responsive and works on:
- Desktop (1920px, 1440px)
- Tablet (768px, 1024px)  
- Mobile (375px, 414px, 480px)

All forms, buttons, and navigation adapt to screen size.

---

## 🔐 Security Notes

- All user inputs are sanitized with `clean()` function
- SQL injection prevented using PDO prepared statements
- Passwords hashed with bcrypt (cost 12)
- Sessions managed securely
- CORS headers configured for API endpoints
- Cloudinary credentials stored server-side only

---

## 📞 Support & Contact

For issues or questions:
- **Email:** shreejiinstrument83@gmail.com
- **Phone:** +91 99049-04610
- **Website:** https://www.shreejiinstruments.com
- **Address:** Shop 9, Karnavati Shopping Center, Ghodasar, Ahmedabad – 380050

---

## 📄 File Manifest

### Root Files
- `index.php` - Home page listing instruments
- `login.php` - Login page
- `dashboard.php` - Admin dashboard
- `contact.php` - Contact form page
- `database.sql` - MySQL schema

### Include Files (5 files)
- `/includes/config.php` - Configuration & database setup
- `/includes/header.php` - HTML header
- `/includes/footer.php` - HTML footer
- `/includes/cloudinary.php` - Cloudinary upload helper

### API Files (3 files)
- `/api/auth.php` - Authentication API
- `/api/contact.php` - Contact form API
- `/api/save_certificates.php` - Certificate save API

### Certificate Templates (25 files)
- `/certificates/autolevel.php`
- `/certificates/aggregate_impact.php`
- `/certificates/ctm.php`
- `/certificates/cone_penetro.php`
- `/certificates/core_cutter.php`
- `/certificates/cube_mould.php`
- `/certificates/digital_thermo.php`
- `/certificates/elongation.php`
- `/certificates/flakness.php`
- `/certificates/full_lab.php`
- `/certificates/hydrometer.php`
- `/certificates/isi_cube.php`
- `/certificates/measuring_cyl.php`
- `/certificates/oven.php`
- `/certificates/ph_meter.php`
- `/certificates/pycnometer.php`
- `/certificates/rapid_moisture.php`
- `/certificates/sand_pouring.php`
- `/certificates/sieves.php`
- `/certificates/slumcone.php`
- `/certificates/total_station.php`
- `/certificates/vernier_caliper.php`
- `/certificates/water_bath.php`
- `/certificates/weight_balance.php`
- `/certificates/weigh_batcher.php`

### Asset Files
- `/assets/css/style.css` - Main stylesheet
- `/assets/css/general.css` - Certificate forms stylesheet
- `/assets/js/app.js` - Global utilities
- `/assets/js/general.js` - Certificate functions
- `/assets/images/logo.png`, `footer.jpeg`, `header.jpeg`, `stamp.jpeg`, `sign.jpeg`

---

## ✅ Project Status

**Completed:**
- ✓ Project structure organized
- ✓ Database schema created
- ✓ Authentication system (login/logout)
- ✓ 25 certificate templates converted to PHP
- ✓ PDF generation and download
- ✓ Cloudinary integration
- ✓ Dashboard with statistics
- ✓ Contact form
- ✓ Responsive design

**Version:** 1.0
**Last Updated:** June 10, 2026
**Status:** Ready for Deployment

---

## 🎉 Next Steps

1. Import `database.sql` into phpMyAdmin
2. Configure Cloudinary credentials
3. Test the application locally
4. Deploy to production server
5. Add more admin users if needed
6. Customize certificate templates as needed

Enjoy using SHREEJI INSTRUMENTS Calibration Certificate Generator! 🎊
