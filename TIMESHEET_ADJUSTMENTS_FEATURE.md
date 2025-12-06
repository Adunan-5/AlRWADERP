# Timesheet Salary Adjustments Feature

## Overview
This feature allows administrators to add custom allowances and deductions to employee salaries on a per-project, per-month basis through the timesheet salary details page.

## Feature Components

### 1. Database Schema
**Table:** `tbltesheet_adjustments`

**Columns:**
- `id` - Auto-increment primary key
- `staff_id` - Employee ID (foreign key to staff table)
- `project_id` - Project ID (foreign key to projects table)
- `type` - Adjustment type: 'allowance' or 'deduction'
- `date` - Date of the adjustment
- `description` - Text description of the adjustment
- `amount` - Decimal amount (15,2)
- `month_year` - First day of the month (YYYY-MM-01) for grouping
- `created_by` - Staff ID of user who created the record
- `created_at` - Timestamp of creation

### 2. User Interface

**Location:** Admin > Timesheet > Salary Details page
**URL Pattern:** `/admin/timesheet/salary_details/{project_id}/{month}`

**Modal Dialog Fields:**
1. **Type** (Required) - Dropdown: Allowance or Deduction
2. **Date** (Required) - Datepicker with calendar
3. **Project** (Required) - Dropdown of assigned projects (current project pre-selected)
4. **Description** (Required) - Textarea for explanation
5. **Amount** (Required) - Number input (decimal, minimum 0.01)

### 3. Backend API Endpoints

#### Get Assigned Projects
**URL:** `/admin/timesheet/get_assigned_projects`
**Method:** POST (AJAX)
**Parameters:** `staff_id`
**Response:**
```json
{
    "success": true,
    "projects": [
        {"id": 1, "name": "Project Name"}
    ]
}
```

#### Save Adjustment
**URL:** `/admin/timesheet/save_adjustment`
**Method:** POST (AJAX)
**Parameters:**
- `staff_id`
- `project_id`
- `type` (allowance/deduction)
- `date`
- `description`
- `amount`
- `current_month`

**Response:**
```json
{
    "success": true,
    "message": "Adjustment saved successfully"
}
```

### 4. Salary Calculation Update

The salary details calculation has been updated to include adjustments:

**Formula:**
```
Total Salary = Regular Pay + Overtime Pay + Allowances - Deductions

Where:
- Regular Pay: Based on staff pay settings (hourly or monthly)
- Overtime Pay: Overtime hours × overtime rate
- Allowances: SUM of all allowance adjustments for the month/project
- Deductions: SUM of all deduction adjustments for the month/project
```

### 5. Files Modified

#### View Files
- `application/views/admin/timesheet/salary_details.php`
  - Added modal HTML structure (lines 154-211)
  - Added button click handlers and AJAX functions (lines 458-566)
  - Updated Add/Deduct button with data attributes (lines 134-140)

#### Controller Files
- `application/controllers/admin/Timesheet.php`
  - Added `get_assigned_projects()` method (lines 1297-1325)
  - Added `save_adjustment()` method (lines 1327-1379)
  - Updated `salary_details()` to calculate adjustments (lines 1451-1471)

#### Database Migration
- `application/migrations/331_version_331.php` - New migration file
- `application/config/migration.php` - Updated version to 331

## Installation Instructions

### Step 1: Run Database Migration
Execute the SQL file to create the table:
```bash
mysql -u username -p database_name < timesheet_adjustments_table.sql
```

Or manually run the SQL commands from your database management tool.

### Step 2: Verify Migration Version
Check that the migration version is set to 331:
```sql
SELECT * FROM tblmigrations;
```

### Step 3: Test the Feature
1. Navigate to Admin > Projects > Select a Project
2. Go to Timesheet tab
3. Select a month and click "Salary Details"
4. Click the "Add/Deduct" button for any employee
5. Fill in the form and submit

## Usage Examples

### Example 1: Adding Housing Allowance
- Type: Allowance
- Date: 2025-10-15
- Project: Construction Project A
- Description: Monthly housing allowance
- Amount: 1500.00

### Example 2: Salary Advance Deduction
- Type: Deduction
- Date: 2025-10-20
- Project: Construction Project A
- Description: Salary advance taken on Oct 20
- Amount: 500.00

## Features

✅ Multi-project support - Each adjustment is tied to a specific project
✅ Date tracking - Records when the adjustment occurred
✅ Audit trail - Tracks who created each adjustment
✅ Month grouping - Adjustments automatically grouped by month
✅ Real-time calculation - Salary totals update immediately after saving
✅ Project filtering - Only shows projects the employee is assigned to
✅ Current project pre-selection - Modal opens with current project selected
✅ Form validation - All fields are required and validated
✅ User-friendly interface - Clean modal dialog with datepicker and searchable project dropdown

## Future Enhancements (TODO)

1. **Adjustment History View**
   - Add a table/list showing all adjustments for an employee
   - Include edit/delete functionality

2. **Bulk Adjustments**
   - Apply same adjustment to multiple employees at once

3. **Adjustment Categories**
   - Create predefined categories (Transport, Food, Penalty, etc.)
   - Enable reporting by category

4. **Approval Workflow**
   - Require manager approval for adjustments above certain threshold

5. **Export to Payslip**
   - Show adjustments breakdown in PDF payslip

6. **Recurring Adjustments**
   - Set up monthly recurring allowances/deductions

## Technical Notes

### Database Indexing
The table includes indexes on:
- `staff_id` - For fast employee lookups
- `project_id` - For fast project-based queries
- `month_year` - For fast month-based aggregation

### Security
- All endpoints check for AJAX requests only
- Input validation on both client and server side
- SQL injection prevention via CodeIgniter query builder
- XSS prevention via `html_escape()` in views

### Performance Considerations
- Adjustments are queried with SUM aggregation for efficiency
- Separate queries for allowances and deductions to avoid complex GROUP BY
- Uses database indexes for optimal query performance

## Support

For issues or questions about this feature:
1. Check the error logs in `application/logs/`
2. Verify database table exists: `SHOW TABLES LIKE 'tbltesheet_adjustments'`
3. Check browser console for JavaScript errors
4. Verify AJAX endpoints return expected JSON responses

## Version History

- **v1.0** (2025-10-26) - Initial implementation
  - Basic allowance/deduction functionality
  - Modal dialog interface
  - Salary calculation integration
