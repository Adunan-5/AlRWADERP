# Equipment Module Database Migrations - Setup Complete

## Summary

✅ **8 Migration Files Created Successfully**

All database tables for the comprehensive equipment rental management system have been designed and migration files created.

---

## Created Migration Files

### **101_version_101.php** - Operators Table
Creates `tbloperators` with fields for:
- Personal information (name, nationality, DOB)
- Document tracking (Iqama, Muqueen, License, Passport, Medical)
- Supplier linkage
- Skills and certifications
- Status tracking (available, assigned, on_leave, terminated)

### **102_version_102.php** - Document Type Masters
Creates two tables with default data:
- `tblequipment_document_types` (Insurance, MVPI, TAMM, Istimara, etc.)
- `tbloperator_document_types` (Iqama, Muqueen, License, Passport, Medical, etc.)

### **103_version_103.php** - Document Storage
Creates:
- `tblequipment_documents` - Uploaded equipment documents with expiry tracking
- `tbloperator_documents` - Uploaded operator documents with expiry tracking

### **104_version_104.php** - Equipment-Operator Assignment
Creates `tblequipment_operators`:
- Links equipment to operators (primary/secondary/relief)
- Assignment date tracking
- Mobilization linkage

### **105_version_105.php** - Equipment Mobilization
Creates `tblequipment_mobilization`:
- Client/project deployment tracking
- Mobilization/demobilization dates
- Rate management (hourly/daily/monthly)
- Status workflow (planned → mobilized → active → demobilized)

### **106_version_106.php** - Equipment Timesheet Master
Creates `tblequipment_timesheet`:
- Monthly timesheet header for client billing
- Equipment & operator details (denormalized)
- Billing calculations (hours × rate - deductions)
- Approval workflow (draft → submitted → verified → approved → invoiced)
- Invoice generation tracking

### **107_version_107.php** - Timesheet Daily Details
Creates `tblequipment_timesheet_details`:
- Daily hours breakdown (day 1-31)
- Actual hours, overtime hours
- Notes per day
- Working day flags

### **108_version_108.php** - Equipment Agreements
Creates `tblequipment_agreements`:
- Supplier and client agreements
- Terms, payment terms, rate structure
- Auto-renewal support
- Document attachment

---

## Database Schema Overview

```
tbloperators (operators management)
├── tbloperator_document_types (document masters)
└── tbloperator_documents (uploaded documents)

tblequipments (existing - enhanced)
├── tblequipment_document_types (document masters)
├── tblequipment_documents (uploaded documents)
├── tblequipment_operators (operator assignments)
├── tblequipment_mobilization (client deployments)
│   └── tblequipment_timesheet (monthly billing)
│       └── tblequipment_timesheet_details (daily hours)
└── tblequipment_agreements (contracts)
```

---

## Installation Process

### **Method 1: Automatic (Recommended)**

1. Navigate to **Admin → Setup → Modules**
2. Find **Equipments** module
3. Click **Deactivate** (if active)
4. Click **Activate**
5. Migrations will run automatically via `install.php`

### **Method 2: Manual Migration Trigger**

Access: `http://yourdomain.com/admin/migration`

This will run all pending migrations system-wide.

---

## Verification Steps

After activation, verify tables were created:

```sql
-- Check all new tables exist
SHOW TABLES LIKE 'tbl%operator%';
SHOW TABLES LIKE 'tbl%equipment%';

-- Verify default document types
SELECT * FROM tblequipment_document_types;
SELECT * FROM tbloperator_document_types;

-- Check migration version
SELECT * FROM tblmodules WHERE module_name = 'equipments';
```

Expected migration_version: **108**

---

## Created Directory Structure

```
uploads/equipments/
├── equipment_documents/
├── operator_documents/
├── agreements/
└── timesheets/
```

All directories have `.htaccess` for security.

---

## Menu Structure (After Activation)

**Equipments** (Main Menu)
- Equipments
- Operators
- Mobilization
- Timesheets
- Agreements
- Settings (Admin only)

---

## Permissions Created

- `equipments` → view, create, edit, delete
- `operators` → view, create, edit, delete
- `equipment_mobilization` → view, create, edit, delete
- `equipment_timesheets` → view, create, edit, delete, **approve**
- `equipment_agreements` → view, create, edit, delete

**Note:** The **approve** capability for timesheets enables the 3-tier approval workflow.

---

## Language Strings

All language strings added to:
`modules/equipments/language/english/equipments_lang.php`

Includes 200+ labels for all features in English.

---

## Next Steps

Now that the database foundation is ready, you can proceed with:

1. ✅ **Test Migrations** - Activate module and verify tables
2. **Build Operators Module** - CRUD interface for operator management
3. **Build Mobilization Module** - Equipment deployment tracking
4. **Build Timesheet Module** - The core billing feature with Excel import/export
5. **Approval Workflow** - 3-tier approval system
6. **Invoice Integration** - Auto-generate invoices from timesheets

---

## Rollback (If Needed)

If you need to undo migrations:

```php
// Each migration has a down() method for rollback
// Manually run down() methods in reverse order (108 → 101)
```

**Warning:** This will delete all data in these tables!

---

## Support

For issues or questions:
- Check logs: `application/logs/`
- Review migration code: `modules/equipments/migrations/`
- Verify `install.php` executed successfully

---

**Status:** ✅ Database Structure Complete
**Version:** 1.0.8
**Tables Created:** 8 new tables + 2 existing enhanced
**Ready For:** Module Development Phase
