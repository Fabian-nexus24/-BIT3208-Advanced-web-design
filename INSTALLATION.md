# FarmConnect Kenya - Installation Guide

## Quick Start Guide

Follow these steps to get FarmConnect Kenya running on your local machine using XAMPP or Laragon.

---

## Prerequisites

Before you begin, ensure you have:

1. **XAMPP** (https://www.apachefriends.org/) or **Laragon** (https://laragon.org/) installed
2. **PHP 8.0 or higher**
3. **MySQL 5.7 or higher**
4. **A modern web browser** (Chrome, Firefox, Safari, Edge)

---

## Installation Steps

### Step 1: Extract Project Files

1. Download the `farmconnect.zip` file
2. Extract it to your local server directory:

   **For XAMPP (Windows):**
   ```
   C:\xampp\htdocs\farmconnect\
   ```

   **For XAMPP (Mac):**
   ```
   /Applications/XAMPP/htdocs/farmconnect/
   ```

   **For Laragon (Windows):**
   ```
   C:\laragon\www\farmconnect\
   ```

### Step 2: Start Your Local Server

**For XAMPP:**
1. Open XAMPP Control Panel
2. Click **Start** next to Apache
3. Click **Start** next to MySQL

**For Laragon:**
1. Open Laragon
2. Click the **Start** button

### Step 3: Create the Database

1. Open your browser and navigate to:
   ```
   http://localhost/phpmyadmin
   ```

2. You should see the phpMyAdmin interface

3. **Method A: Import SQL File**
   - Click the **Import** tab
   - Click **Choose File**
   - Select `database.sql` from the farmconnect folder
   - Click **Go**

4. **Method B: Manual SQL Entry**
   - Click the **SQL** tab
   - Copy the contents of `database.sql`
   - Paste into the SQL query box
   - Click **Go**

### Step 4: Verify Database Creation

1. In phpMyAdmin, you should see `farmconnect_db` in the left sidebar
2. Click on it to expand and verify tables:
   - `farmers`
   - `products`
   - `admins`

### Step 5: Configure Database Connection (if needed)

The database connection is pre-configured, but if you used different credentials:

1. Open `config/db.php` in a text editor
2. Update these values:
   ```php
   $host = 'localhost';      // Your MySQL host
   $db   = 'farmconnect_db';  // Your database name
   $user = 'root';            // Your MySQL username
   $pass = '';                // Your MySQL password
   ```
3. Save the file

### Step 6: Access the Application

Open your web browser and navigate to:

```
http://localhost/farmconnect/
```

You should see the FarmConnect Kenya homepage!

### Step 7: Enable customer orders (Phase 6 — if not using full `database/schema.sql`)

If order placement shows *"Orders table is missing"*, create the `orders` table:

**Option A — PHP (recommended, uses `config/db.php`):**

```bash
cd C:\laragon\www\farmconnect
php tools/migrate_phase6_orders.php
```

**Option B — phpMyAdmin:**

1. Select database `farmconnect_kenya`
2. Import `database/migrations/phase6_orders.sql`

The `orders` table links to `customers`, `farmers`, and `products` with foreign keys. New orders default to status `pending` and payment `cash_on_delivery`.

**Phase 6B — order workflow (after Part 1):**

```bash
php tools/migrate_phase6b_orders.php
```

This updates order statuses to Pending / Accepted / Rejected / Delivered. Stock is deducted when the farmer accepts an order.

---

## First Time Setup

### Create Your First Farmer Account

1. Click **Register** in the navigation menu
2. Fill in the registration form:
   - Full Name
   - Phone Number
   - Email Address
   - Password (at least 6 characters)
   - Farming Location
3. Click **Register**
4. You'll be redirected to the login page
5. Log in with your new credentials

### Access the Admin Panel

1. Go to `http://localhost/farmconnect/login.php`
2. Enter these credentials:
   - **Email/Username:** `admin`
   - **Password:** `admin123`
3. You'll be taken to the admin dashboard

---

## Folder Permissions

Ensure the `uploads/` folder has write permissions:

**For Windows (XAMPP/Laragon):**
- Right-click `uploads` folder
- Select **Properties**
- Go to **Security** tab
- Click **Edit**
- Select your user and check **Full Control**
- Click **Apply**

**For Mac/Linux:**
```bash
chmod 755 /path/to/farmconnect/uploads/
```

---

## Troubleshooting

### Issue: "Connection refused" error

**Solution:**
- Verify MySQL is running in XAMPP/Laragon Control Panel
- Check that port 3306 is not blocked
- Restart your local server

### Issue: "Database does not exist" error

**Solution:**
- Verify you imported `database.sql` correctly
- Check phpMyAdmin to confirm `farmconnect_db` exists
- Try importing again using Method B (Manual SQL Entry)

### Issue: "Access denied for user 'root'" error

**Solution:**
- Verify MySQL username and password in `config/db.php`
- Check your XAMPP/Laragon MySQL credentials
- Reset MySQL password if forgotten

### Issue: Images not uploading

**Solution:**
- Verify `uploads/` folder exists
- Check folder permissions (should be 755 or writable)
- Verify file size limits in `php.ini`
- Ensure only JPG/PNG files are uploaded

### Issue: Session/Login problems

**Solution:**
- Clear browser cookies (Ctrl+Shift+Delete)
- Clear browser cache
- Try a different browser
- Verify PHP sessions are enabled

### Issue: Blank page or 500 error

**Solution:**
- Check PHP error logs in XAMPP/Laragon
- Verify all PHP files are properly saved
- Check database connection in `config/db.php`
- Ensure PHP 8.0+ is installed

---

## Testing the Application

### Test Farmer Workflow

1. **Register:** Create a new farmer account
2. **Login:** Log in with your credentials
3. **Add Product:** Add a test product
4. **Edit Product:** Modify the product details
5. **View:** Check if product appears on public page
6. **Delete:** Remove the product

### Test Admin Workflow

1. **Login as Admin:** Use admin credentials
2. **View Farmers:** Check farmers list
3. **View Products:** Check products list
4. **Delete Product:** Remove a product from admin panel
5. **View Statistics:** Check dashboard statistics

### Test Public Features

1. **Homepage:** Browse featured products
2. **Products Page:** View all products
3. **Search:** Search for specific products
4. **Filter:** Filter by location
5. **Responsive:** Test on mobile/tablet view

---

## Performance Tips

1. **Optimize Images:** Compress product images before uploading
2. **Database Maintenance:** Regularly clean up old products
3. **Clear Cache:** Clear browser cache periodically
4. **Monitor Logs:** Check error logs for issues

---

## Security Checklist

Before going live/commercial:

- [ ] Change default admin password
- [ ] Enable HTTPS/SSL
- [ ] Set up regular database backups
- [ ] Implement rate limiting
- [ ] Add CSRF tokens to forms
- [ ] Validate all file uploads
- [ ] Use environment variables for credentials
- [ ] Implement user account verification
- [ ] Add password reset functionality
- [ ] Set up error logging

---

## Next Steps

After successful installation:

1. **Customize:** Modify colors, text, and branding
2. **Add Features:** Implement payment integration
3. **Scale:** Deploy to a production server
4. **Monitor:** Set up monitoring and analytics
5. **Maintain:** Regular updates and backups

---

## Getting Help

If you encounter issues:

1. Check the **Troubleshooting** section above
2. Review the main **README.md** file
3. Check PHP error logs
4. Verify database connection
5. Test with a fresh browser window

---

## Support

For additional help:
- Email: info@farmconnect.co.ke
- Check the README.md for more information

---

**Happy farming! 🌾**
