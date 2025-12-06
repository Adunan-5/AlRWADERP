# Equipment Module - Activation & Testing Guide

## 🚀 Quick Start - Activate Module & Run Migrations

You have **THREE methods** to activate the module and run migrations. Choose the one that works best for you.

---

## ✅ METHOD 1: Web-Based Migration Runner (Recommended)

**Easiest and provides detailed feedback**

### Steps:

1. **Open your web browser**

2. **Navigate to:**
   ```
   https://rwaderp.local/modules/equipments/run_migrations.php
   ```

3. **The page will automatically:**
   - Check module registration
   - Run all 8 migration files (101-108)
   - Create 10 new tables
   - Insert default document types
   - Create upload directories
   - Show detailed success/error messages

4. **What to look for:**
   - ✓ Green checkmarks for each table created
   - Migration version should be: **108**
   - Equipment document types: **6 records**
   - Operator document types: **7 records**

5. **IMPORTANT: After successful migration**
   ```
   Delete the file: modules/equipments/run_migrations.php
   ```
   This is for security - don't leave it accessible!

---

## ✅ METHOD 2: Via Admin Panel

**Standard Perfex CRM method**

### Steps:

1. **Login to Admin Panel**
   ```
   https://rwaderp.local/admin
   ```

2. **Navigate to Modules**
   ```
   Admin → Setup → Modules
   ```

3. **Find "Equipments" module**

4. **If module is ACTIVE:**
   - Click **Deactivate**
   - Wait for confirmation
   - Click **Activate**

5. **If module is INACTIVE:**
   - Just click **Activate**

6. **Migrations run automatically** on activation via `install.php`

7. **Verify activation:**
   - Refresh the page
   - Check if menu "Equipments" appears in left sidebar
   - Should have submenus: Equipments, Operators, Mobilization, Timesheets, Agreements, Settings

---

## ✅ METHOD 3: Database Direct Method

**For advanced users / if web methods fail**

### Steps:

1. **Access phpMyAdmin or database client**

2. **Run verification query:**
   ```sql
   SELECT * FROM tblmodules WHERE module_name = 'equipments';
   ```

3. **If module NOT registered:**
   ```sql
   INSERT INTO tblmodules (module_name, installed_version, active, migrations_version)
   VALUES ('equipments', '1.0.8', 1, 0);
   ```

4. **Run each migration manually:**

   Open each file in `modules/equipments/migrations/` and execute the SQL from the `up()` method:

   - 101_version_101.php → Creates `tbloperators`
   - 102_version_102.php → Creates document type tables + inserts defaults
   - 103_version_103.php → Creates document storage tables
   - 104_version_104.php → Creates `tblequipment_operators`
   - 105_version_105.php → Creates `tblequipment_mobilization`
   - 106_version_106.php → Creates `tblequipment_timesheet`
   - 107_version_107.php → Creates `tblequipment_timesheet_details`
   - 108_version_108.php → Creates `tblequipment_agreements`

5. **Update migration version:**
   ```sql
   UPDATE tblmodules
   SET migrations_version = 108
   WHERE module_name = 'equipments';
   ```

---

## 🔍 Verification - Confirm Everything Works

After activation, verify the setup:

### 1. Check Menu Structure

**Navigate to:** Admin Panel

**Look for new menu:**
```
📦 Equipments (Main Menu)
   ├── 🔧 Equipments
   ├── 👥 Operators
   ├── 📍 Mobilization
   ├── 📅 Timesheets
   ├── 📄 Agreements
   └── ⚙️ Settings
```

### 2. Verify Database Tables

**Run the verification SQL script:**

```bash
# In phpMyAdmin or MySQL client
Open file: modules/equipments/verify_tables.sql
Execute all queries
```

**Expected Results:**
- 10 new tables created
- 6 equipment document types
- 7 operator document types
- Migration version = 108

### 3. Check Upload Directories

**Verify these directories exist:**
```
uploads/equipments/
├── equipment_documents/
├── operator_documents/
├── agreements/
└── timesheets/
```

Each should have a `.htaccess` file for security.

### 4. Test Permissions

**Navigate to:** Admin → Setup → Roles

**Look for new permissions:**
- Equipments (view, create, edit, delete)
- Operators (view, create, edit, delete)
- Mobilization (view, create, edit, delete)
- Equipment Timesheets (view, create, edit, delete, **approve**)
- Equipment Agreements (view, create, edit, delete)

### 5. Check Logs (If Issues Occur)

**Log Location:**
```
application/logs/log-[YYYY-MM-DD].php
```

**Look for:**
- "Equipments module" entries
- "Migration" entries
- Any ERROR or EXCEPTION messages

---

## ✅ Success Checklist

After activation, you should have:

- [x] Module status = ACTIVE
- [x] Migration version = 108
- [x] 10 new database tables
- [x] 13 default document types (6 equipment + 7 operator)
- [x] Upload directories created
- [x] Menu appears in admin panel
- [x] Permissions registered in roles
- [x] No errors in logs

---

## ❌ Troubleshooting

### Problem: "Table already exists" error

**Solution:**
You may have run migrations twice. Check:
```sql
SELECT migrations_version FROM tblmodules WHERE module_name = 'equipments';
```

If version shows 108, migrations are complete. Just activate module.

### Problem: Menu doesn't appear

**Solution:**
1. Hard refresh browser (Ctrl+F5)
2. Check permissions - ensure your role has "view" permission for equipments
3. Clear cache: Admin → Setup → Clear Cache

### Problem: "Permission denied" when creating directories

**Solution:**
Manually create directories with write permissions:
```bash
mkdir uploads/equipments
mkdir uploads/equipments/equipment_documents
mkdir uploads/equipments/operator_documents
mkdir uploads/equipments/agreements
mkdir uploads/equipments/timesheets
chmod 755 uploads/equipments -R
```

### Problem: Foreign key constraint fails

**Solution:**
The `tblequipments` table must exist first (created by previous module version).
If it doesn't exist, create it first before running migrations.

### Problem: "Class App_module_migration not found"

**Solution:**
Ensure file exists: `application/libraries/App_module_migration.php`
This should already exist in your Perfex CRM installation.

---

## 🎯 What's Next?

Once activation is successful, you can proceed with:

1. **Add Operators** - Create operator records with document tracking
2. **Setup Document Types** - Customize document types if needed
3. **Create Mobilizations** - Deploy equipment to client sites
4. **Enter Timesheets** - Monthly billing timesheets
5. **Generate Invoices** - Auto-create invoices from approved timesheets

---

## 📞 Need Help?

If you encounter issues:

1. Check the `run_migrations.php` output for detailed error messages
2. Review log files in `application/logs/`
3. Run `verify_tables.sql` to see which tables are missing
4. Share error messages for debugging assistance

---

## 🔒 Security Note

**After successful migration, DELETE these files:**

- `modules/equipments/run_migrations.php` ⚠️ **Important!**
- `modules/equipments/ACTIVATION_GUIDE.md` (optional)
- `modules/equipments/README_MIGRATIONS.md` (optional)
- `modules/equipments/verify_tables.sql` (optional)

These are for installation only and should not remain in production.

---

**Ready to test?** Access the migration runner now:

👉 **https://rwaderp.local/modules/equipments/run_migrations.php**

Good luck! 🚀
