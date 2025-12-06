# Payroll Generation System - Design Document

## Overview
Transform the current employee data entry system into a structured payroll generation and approval workflow system.

## Requirements Summary
1. **Generate payroll** for specific month and employee type (ownemployee_id subtype)
2. **Payroll list/history** with clickable payrolls to load data
3. **Status workflow**: draft → ready for review → awaiting approval → submitted → completed
4. **Separate base salary from project payments**:
   - GOSI fields = Total fixed amounts from `tbl_staff_pay`
   - Balance/Full salary = OT from timesheet + project assignments
   - Permanent employees get base salary + separate project payments

## User Answers
- **UI Location:** Separate menu item for payroll list
- **Generation:** Manual - User clicks 'Generate Payroll' button
- **Data Source:** From tbl_staff_pay (base salary structure)
- **Permissions:**
  - HR Staff: draft → ready for review
  - HR Manager: ready → awaiting approval
  - Finance/Admin: awaiting → submitted
  - Auto: submitted → completed (after payment)

## Database Schema Changes

### 1. New Table: `tbl_hrp_payroll` (Payroll Header/Master)

```sql
CREATE TABLE `tbl_hrp_payroll` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payroll_number` varchar(50) NOT NULL,
  `month` date NOT NULL COMMENT 'First day of payroll month (YYYY-MM-01)',
  `company_filter` varchar(50) DEFAULT NULL COMMENT 'mohtarifeen, mahiroon, or NULL for all',
  `ownemployee_type_id` int(11) DEFAULT NULL COMMENT 'Employee subtype from tbl_ownemployeetype',
  `ownemployee_type_name` varchar(100) DEFAULT NULL COMMENT 'Employee type name snapshot',
  `status` enum('draft','ready_for_review','awaiting_approval','submitted','completed','cancelled') NOT NULL DEFAULT 'draft',
  `total_employees` int(11) DEFAULT 0,
  `total_amount` decimal(15,2) DEFAULT 0.00 COMMENT 'Total payroll amount',
  `rel_type` varchar(50) DEFAULT NULL COMMENT 'hr_records or other integration',
  `notes` text DEFAULT NULL,

  -- Audit trail
  `created_by` int(11) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_date` datetime DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL,
  `submitted_by` int(11) DEFAULT NULL,
  `submitted_date` datetime DEFAULT NULL,
  `completed_date` datetime DEFAULT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_payroll` (`month`, `company_filter`, `ownemployee_type_id`),
  KEY `idx_month_status` (`month`, `status`),
  KEY `idx_company` (`company_filter`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 2. Modify Table: `tbl_hrp_employees_value`

**Add new column:**
```sql
ALTER TABLE `tbl_hrp_employees_value`
  ADD COLUMN `payroll_id` int(11) DEFAULT NULL COMMENT 'Foreign key to tbl_hrp_payroll',
  ADD KEY `idx_payroll_id` (`payroll_id`);
```

**Make GOSI fields read-only** (from staff_pay):
- `gosi_basic_salary` - from staff_pay.basic_pay
- `gosi_housing_allowance` - from staff_pay.fat_allowance or accomodation_allowance
- `gosi_other_allowance` - from staff_pay.food_allowance + allowance
- `gosi_deduction` - from staff_pay.mewa

### 3. New Table: `tbl_hrp_project_payments` (Project-based OT/Allowances/Deductions)

```sql
CREATE TABLE `tbl_hrp_project_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payroll_id` int(11) NOT NULL COMMENT 'Link to tbl_hrp_payroll',
  `staff_id` int(11) NOT NULL,
  `month` date NOT NULL,
  `project_id` int(11) DEFAULT NULL COMMENT 'Link to tbl_projects if applicable',
  `project_name` varchar(255) DEFAULT NULL,

  -- Timesheet-based OT
  `ot_hours` decimal(10,2) DEFAULT 0.00,
  `ot_rate` decimal(15,2) DEFAULT 0.00,
  `ot_amount` decimal(15,2) DEFAULT 0.00,

  -- Additional payments/deductions
  `additional_allowance` decimal(15,2) DEFAULT 0.00,
  `additional_deduction` decimal(15,2) DEFAULT 0.00,

  `description` text DEFAULT NULL,
  `payment_status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `payment_date` date DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,

  `created_by` int(11) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `idx_payroll_id` (`payroll_id`),
  KEY `idx_staff_month` (`staff_id`, `month`),
  KEY `idx_project` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 4. New Table: `tbl_hrp_payroll_status_log` (Status Change Audit)

```sql
CREATE TABLE `tbl_hrp_payroll_status_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payroll_id` int(11) NOT NULL,
  `from_status` varchar(50) DEFAULT NULL,
  `to_status` varchar(50) NOT NULL,
  `changed_by` int(11) NOT NULL,
  `changed_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comments` text DEFAULT NULL,

  PRIMARY KEY (`id`),
  KEY `idx_payroll_id` (`payroll_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Application Flow

### 1. Payroll List Page (New)

**Menu Location:** HR Payroll → Payroll List

**Features:**
- DataTable showing all payrolls with columns:
  - Payroll Number
  - Month
  - Company (Mohtarifeen/Mahiroon/All)
  - Employee Type
  - Total Employees
  - Total Amount
  - Status (badge with color)
  - Actions (View, Edit Status, Delete)

- Filters:
  - Month range
  - Status dropdown
  - Company dropdown
  - Employee type dropdown

- **"Generate New Payroll" button** → Opens modal with:
  - Month selector
  - Company selector (Mohtarifeen/Mahiroon/All)
  - Employee type selector (from tbl_ownemployeetype)
  - Confirm button

### 2. Payroll Generation Process

**Trigger:** User clicks "Generate Payroll" in modal

**Backend Process (`generate_payroll()` controller method):**

1. **Validate inputs:**
   - Check if payroll already exists for month + company + employee_type
   - Check user permission (hrp_employee.create)

2. **Fetch employees:**
   ```sql
   SELECT s.* FROM tbl_staff s
   WHERE s.active = 1
   AND s.ownemployee_id = $employee_type_id
   AND s.staffid NOT IN (1, 3)  -- Exclude system accounts
   AND (
     -- Company filter logic
     ($company_filter = 'mohtarifeen' AND s.companytype_id = 2)
     OR ($company_filter = 'mahiroon' AND s.companytype_id != 2)
     OR ($company_filter IS NULL)
   )
   ```

3. **Create payroll header:**
   ```php
   $payroll_data = [
     'payroll_number' => generate_payroll_number($month, $company, $type),
     'month' => $month,
     'company_filter' => $company_filter,
     'ownemployee_type_id' => $employee_type_id,
     'ownemployee_type_name' => get_ownemployee_type_name($employee_type_id),
     'status' => 'draft',
     'created_by' => get_staff_user_id(),
     'rel_type' => hrp_get_hr_profile_status()
   ];
   INSERT INTO tbl_hrp_payroll
   ```

4. **Create employee payroll records:**
   ```php
   foreach ($employees as $employee) {
     // Get base salary from tbl_staff_pay
     $staff_pay = get_staff_pay($employee['staffid']);

     // Calculate GOSI totals
     $gosi_basic = $staff_pay['basic_pay'];
     $gosi_housing = $staff_pay['fat_allowance'] + $staff_pay['accomodation_allowance'];
     $gosi_other = $staff_pay['food_allowance'] + $staff_pay['allowance'];
     $gosi_deduction = $staff_pay['mewa'];
     $total_amount = $gosi_basic + $gosi_housing + $gosi_other - $gosi_deduction;

     // Get timesheet OT (if any) for this month
     $ot_data = get_timesheet_ot($employee['staffid'], $month);

     $employee_data = [
       'payroll_id' => $payroll_id,
       'staff_id' => $employee['staffid'],
       'month' => $month,
       'rel_type' => hrp_get_hr_profile_status(),
       'employee_id_iqama' => $employee['iqama_number'],
       'employee_account_no_iban' => $employee['bank_iban_number'],
       'bank_code' => $employee['bank_swift_code'],
       'gosi_basic_salary' => $gosi_basic,
       'gosi_housing_allowance' => $gosi_housing,
       'gosi_other_allowance' => $gosi_other,
       'gosi_deduction' => $gosi_deduction,
       'total_amount' => $total_amount,
       'basic' => $gosi_basic,
       'ot_hours' => $ot_data['hours'],
       'ot_rate' => $ot_data['rate'],
       'ot_amount' => $ot_data['amount'],
       'full_salary' => $total_amount + $ot_data['amount'],
       'balance' => $ot_data['amount'],
       // Contract data from hr_records if integrated
     ];

     INSERT INTO tbl_hrp_employees_value;
   }
   ```

5. **Update payroll totals:**
   ```sql
   UPDATE tbl_hrp_payroll
   SET total_employees = COUNT(*),
       total_amount = SUM(full_salary)
   WHERE id = $payroll_id
   ```

6. **Log status creation:**
   ```sql
   INSERT INTO tbl_hrp_payroll_status_log
   (payroll_id, to_status, changed_by, comments)
   VALUES ($payroll_id, 'draft', $user_id, 'Payroll generated')
   ```

7. **Redirect to manage_employees page:**
   ```php
   redirect(admin_url('hr_payroll/manage_employees?payroll_id=' . $payroll_id));
   ```

### 3. Manage Employees Page (Modified)

**URL:** `admin/hr_payroll/manage_employees?payroll_id=123`

**Changes:**

1. **Header section:**
   - Display payroll info: Payroll Number, Month, Company, Employee Type
   - Display status badge with color coding
   - Show status transition buttons based on current status and user permission

2. **GOSI fields become READ-ONLY:**
   - Display as text, not editable inputs
   - Remove from Handsontable editable columns
   - Show tooltip: "From base salary structure"

3. **Balance & Full Salary calculation:**
   ```javascript
   // In manage_employees_js.php
   function calculateRow(row) {
     // GOSI fields are now read-only
     var gosi_basic = floatval(getData(row, 'gosi_basic_salary'));
     var gosi_housing = floatval(getData(row, 'gosi_housing_allowance'));
     var gosi_other = floatval(getData(row, 'gosi_other_allowance'));
     var gosi_deduction = floatval(getData(row, 'gosi_deduction'));

     // Total GOSI amount (base salary)
     var total_amount = gosi_basic + gosi_housing + gosi_other - gosi_deduction;

     // OT calculation
     var ot_hours = floatval(getData(row, 'ot_hours'));
     var ot_rate = floatval(getData(row, 'ot_rate'));
     var ot_amount = ot_hours * ot_rate;

     // Full salary = Base + OT
     var full_salary = total_amount + ot_amount;

     // Balance = OT amount (separate payment)
     var balance = ot_amount;

     setData(row, 'ot_amount', ot_amount);
     setData(row, 'full_salary', full_salary);
     setData(row, 'balance', balance);
   }
   ```

4. **Status transition buttons:**
   - **Draft → Ready for Review** (HR Staff with hrp_employee.edit permission)
   - **Ready → Awaiting Approval** (HR Manager with hrp_employee.approve permission)
   - **Awaiting → Submitted** (Finance/Admin with hrp_employee.submit permission)
   - **Submitted → Completed** (Auto after payment processing)

5. **Status-based permissions:**
   - **Draft:** Fully editable
   - **Ready for Review:** Read-only for creator, editable by manager
   - **Awaiting Approval:** Read-only except for approver
   - **Submitted:** Read-only for all
   - **Completed:** Read-only for all

### 4. Status Workflow Controller Methods

**`change_payroll_status()` - Generic status transition:**
```php
public function change_payroll_status($payroll_id, $new_status)
{
    // Permission check based on transition
    $payroll = $this->hr_payroll_model->get_payroll($payroll_id);

    // Validate state transition
    $valid_transitions = [
        'draft' => ['ready_for_review'],
        'ready_for_review' => ['awaiting_approval', 'draft'],
        'awaiting_approval' => ['submitted', 'ready_for_review'],
        'submitted' => ['completed'],
    ];

    if (!in_array($new_status, $valid_transitions[$payroll->status])) {
        return ['success' => false, 'message' => 'Invalid status transition'];
    }

    // Permission check
    switch ($new_status) {
        case 'ready_for_review':
            if (!has_permission('hrp_employee', '', 'edit')) {
                access_denied('hrp_employee');
            }
            $update_field = 'reviewed';
            break;
        case 'awaiting_approval':
            if (!has_permission('hrp_employee', '', 'approve')) {
                access_denied('hrp_employee');
            }
            $update_field = 'approved';
            break;
        case 'submitted':
            if (!has_permission('hrp_employee', '', 'submit')) {
                access_denied('hrp_employee');
            }
            $update_field = 'submitted';
            break;
    }

    // Update status
    $this->hr_payroll_model->update_payroll_status([
        'payroll_id' => $payroll_id,
        'status' => $new_status,
        $update_field . '_by' => get_staff_user_id(),
        $update_field . '_date' => date('Y-m-d H:i:s')
    ]);

    // Log status change
    $this->hr_payroll_model->log_status_change([
        'payroll_id' => $payroll_id,
        'from_status' => $payroll->status,
        'to_status' => $new_status,
        'changed_by' => get_staff_user_id()
    ]);

    return ['success' => true, 'message' => 'Status updated successfully'];
}
```

## File Structure

### New Files to Create:

1. **Migration:**
   - `modules/hr_payroll/migrations/110_version_110.php`

2. **Views:**
   - `modules/hr_payroll/views/payroll/payroll_list.php` - Payroll list page
   - `modules/hr_payroll/views/payroll/generate_modal.php` - Generation modal

3. **JavaScript:**
   - `modules/hr_payroll/assets/js/payroll/payroll_list_js.php`

4. **Language:**
   - Add keys to `modules/hr_payroll/language/english/hr_payroll_lang.php`

### Modified Files:

1. **Controller:**
   - `modules/hr_payroll/controllers/Hr_payroll.php`
     - Add: `payroll_list()`, `generate_payroll()`, `change_payroll_status()`
     - Modify: `manage_employees()` to accept `payroll_id` parameter
     - Modify: `employees_filter()` to use `payroll_id`
     - Modify: `add_manage_employees()` to validate status

2. **Model:**
   - `modules/hr_payroll/models/Hr_payroll_model.php`
     - Add: `create_payroll()`, `get_payroll()`, `get_payrolls()`, `update_payroll_status()`, `log_status_change()`
     - Modify: `get_employees_data()` to filter by `payroll_id`
     - Add: `generate_payroll_employees()`

3. **View:**
   - `modules/hr_payroll/views/employees/employees_manage.php`
     - Add payroll info header
     - Add status badge and transition buttons
     - Make GOSI fields display-only

4. **JavaScript:**
   - `modules/hr_payroll/assets/js/manage_employees/manage_employees_js.php`
     - Modify column configuration (GOSI read-only)
     - Update calculation logic
     - Add status transition handlers

5. **Module Init:**
   - `modules/hr_payroll/hr_payroll.php`
     - Add menu item: "Payroll List"

## Permissions

Add new permissions to `hrp_employee` capability:
- `view` - View payrolls
- `create` - Generate new payroll
- `edit` - Edit draft payrolls
- `approve` - Approve payroll (HR Manager)
- `submit` - Submit payroll (Finance/Admin)
- `delete` - Delete draft payrolls

## UI/UX Enhancements

### Status Badge Colors:
- **draft:** Gray (#6c757d)
- **ready_for_review:** Blue (#007bff)
- **awaiting_approval:** Orange (#fd7e14)
- **submitted:** Purple (#6f42c1)
- **completed:** Green (#28a745)
- **cancelled:** Red (#dc3545)

### Payroll Number Format:
`PR-{YEAR}{MONTH}-{COMPANY}-{TYPE}-{SEQUENCE}`

Example: `PR-202510-MOH-PERM-001`
- PR = Payroll prefix
- 202510 = Year + Month
- MOH = Company code (MOH=Mohtarifeen, MAH=Mahiroon, ALL=All)
- PERM = Employee type code
- 001 = Sequential number

## Testing Checklist

- [ ] Generate payroll for Mohtarifeen permanent employees
- [ ] Generate payroll for Mahiroon permanent employees
- [ ] View payroll list with filters
- [ ] Load specific payroll and verify GOSI fields are read-only
- [ ] Edit OT hours and verify calculations
- [ ] Transition from draft → ready for review
- [ ] Transition from ready → awaiting approval
- [ ] Transition from awaiting → submitted
- [ ] Verify permission restrictions at each status
- [ ] Export payroll bank file
- [ ] Generate payslip for employee with project OT
- [ ] Verify status log audit trail

## Migration Strategy

1. **Phase 1:** Create new tables (migration 110)
2. **Phase 2:** Add payroll_id column to hrp_employees_value
3. **Phase 3:** Create payroll list UI and generation logic
4. **Phase 4:** Modify manage_employees to use payroll_id
5. **Phase 5:** Implement status workflow
6. **Phase 6:** Testing and refinement

## Backward Compatibility

- Existing `hrp_employees_value` records without `payroll_id` continue to work
- `manage_employees` without `payroll_id` parameter shows legacy behavior (for now)
- Old filters (month, company) still work alongside new payroll system
- Migration script should create "Legacy" payroll records for existing data (optional)
