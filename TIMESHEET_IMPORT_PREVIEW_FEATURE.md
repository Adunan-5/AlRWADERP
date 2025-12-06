# Timesheet Import Preview Feature

## Overview
This feature provides a comprehensive preview and selective import system for Excel timesheet uploads. Users can preview all employees before importing, see status indicators for each row, and selectively choose which employees to import.

## Feature Components

### 1. User Flow

1. **Upload Excel File** - User clicks "Preview & Import" button and selects an Excel file
2. **Preview Modal** - System displays all employees with status indicators and checkboxes
3. **Select Rows** - User selects which employees to import (can use "Select all importable")
4. **Process Import** - User clicks "Import Selected" to process only checked rows
5. **View Results** - System shows detailed results: imported count, skipped count, and any errors

### 2. Status Indicators

The preview shows one of five status types for each employee:

**Employees in Excel Sheet:**

| Status | Color | Icon | Meaning | Can Import? |
|--------|-------|------|---------|-------------|
| Ready to import | Green | ✓ | Employee exists and is assigned to project, no existing timesheet | Yes |
| Will update (X existing entries) | Blue | ℹ | Employee exists, is assigned, has existing timesheet entries | Yes |
| Not assigned to project | Yellow | ⚠ | Employee exists but not assigned to this project | No |
| Employee not found | Red | ✗ | IQAMA number not found in staff table | No |

**Missing Employees Section:**

| Status | Color | Icon | Meaning | Can Import? |
|--------|-------|------|---------|-------------|
| Missing from Excel | Gray | - | Employee is assigned to project but not found in uploaded Excel file | No |

### 3. Backend API Endpoints

#### Preview Excel File
**URL:** `/admin/timesheet/preview_excel`
**Method:** POST (AJAX, file upload)
**Parameters:**
- `file_input` - Excel file
- `project_id` - Project ID
- `selected_month` - Month in YYYY-MM format

**Process:**
1. Uploads file to `uploads/timesheets/temp/` directory
2. Parses Excel file using PhpSpreadsheet
3. For each employee row:
   - Checks if staff exists (by IQAMA number)
   - Checks if staff is assigned to project
   - Checks if timesheet already exists for the month
   - Determines appropriate status
4. Returns preview data without saving to database

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "row_index": 0,
            "iqama": "1234567890",
            "name": "John Doe",
            "staff_id": 5,
            "total_hours": "160.00",
            "status": "Ready to import",
            "status_class": "success",
            "can_import": true,
            "in_excel": true
        }
    ],
    "missing_employees": [
        {
            "row_index": null,
            "iqama": "9876543210",
            "name": "Jane Smith",
            "staff_id": 12,
            "total_hours": "0.00",
            "status": "Missing from Excel",
            "status_class": "default",
            "can_import": false,
            "in_excel": false
        }
    ],
    "file_name": "unique_filename.xlsx",
    "month": "2025-10",
    "project_id": 3
}
```

#### Process Selective Import
**URL:** `/admin/timesheet/process_selective_import`
**Method:** POST (AJAX)
**Parameters:**
- `selected_rows` - Array of row indexes to import
- `file_name` - Temporary file name from preview
- `project_id` - Project ID
- `selected_month` - Month in YYYY-MM format

**Process:**
1. Retrieves file from temp folder
2. Parses Excel again
3. Processes ONLY the rows in `selected_rows` array
4. For each selected row:
   - Checks if master record exists
   - Inserts or retrieves master ID
   - Processes each day's hours (regular + overtime)
   - Inserts/updates detail records
5. Deletes temporary file
6. Returns results

**Response:**
```json
{
    "success": true,
    "imported": 5,
    "skipped": 0,
    "errors": []
}
```

### 4. Files Modified

#### Controller: `application/controllers/admin/Timesheet.php`

**New Method: `preview_excel()` (lines 498-649)**
- Handles file upload to temp directory
- Parses Excel and generates preview data
- Checks employee status against 4 scenarios
- Returns preview without saving

**New Method: `process_selective_import()` (lines 651-795)**
- Processes only selected row indexes
- Imports timesheet data for checked employees
- Deletes temp file after processing
- Returns import results

**Key Code Snippet - Status Detection:**
```php
// Check employee existence
$staff = $this->db->select('staffid, firstname, lastname')
    ->where('iqama', $iqama)
    ->get(db_prefix() . 'staff')
    ->row();

if (!$staff) {
    $status = 'Employee not found';
    $statusClass = 'danger';
    $canImport = false;
} elseif (!in_array($staff->staffid, $assignedStaffIds)) {
    $status = 'Not assigned to project';
    $statusClass = 'warning';
    $canImport = false;
} else {
    // Check for existing timesheet
    $existing = $this->db->where([
        'project_id' => $project_id,
        'staff_id' => $staff->staffid,
        'month_year' => $month_year
    ])->get(db_prefix() . 'tesheet_master')->row();

    if ($existing) {
        $entryCount = $this->db->where('timesheet_id', $existing->id)
            ->get(db_prefix() . 'tesheet_details')
            ->num_rows();
        $status = 'Will update (' . $entryCount . ' existing entries)';
        $statusClass = 'info';
    } else {
        $status = 'Ready to import';
        $statusClass = 'success';
    }
    $canImport = true;
}
```

#### View: `application/views/admin/projects/project_timesheets.php`

**Modified Upload Form (lines 61-79)**
- Changed from direct POST to AJAX submission
- Updated button text to "Preview & Import"
- Added eye icon to button
- Form now triggers JavaScript preview instead of direct upload

**New Preview Modal (lines 113-185)**
- Large modal (95% width, 1400px max)
- Table showing all employees with:
  - Checkbox column (disabled for non-importable rows)
  - IQAMA number
  - Employee name
  - Status badge (color-coded)
- "Select all importable" checkbox in header
- Live counter showing selected rows
- Import button shows count: "Import Selected (X)"
- Status legend explaining each indicator

**New Result Modal (lines 187-220)**
- Shows import completion summary
- Displays imported/skipped counts
- Lists errors if any occurred
- Refresh button to reload page

**JavaScript Implementation (lines 289-520)**

**Form Submission:**
```javascript
$('#import_form').on('submit', function(e) {
    e.preventDefault();

    var formData = new FormData(this);

    $.ajax({
        url: admin_url + 'timesheet/preview_excel',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showPreviewModal(response);
            } else {
                alert_float('danger', response.message);
            }
        }
    });
});
```

**Preview Modal Population:**
```javascript
function showPreviewModal(response) {
    var data = response.data;
    var tbody = $('#previewTableBody');
    tbody.empty();

    $.each(data, function(index, row) {
        var statusBadge = getStatusBadge(row.status_class, row.status);
        var checkbox = row.can_import ?
            '<input type="checkbox" class="row-checkbox" data-row-index="' +
            row.row_index + '">' :
            '<input type="checkbox" disabled>';

        var tr = '<tr>' +
            '<td class="text-center">' + checkbox + '</td>' +
            '<td>' + row.iqama + '</td>' +
            '<td>' + row.name + '</td>' +
            '<td>' + statusBadge + '</td>' +
            '</tr>';

        tbody.append(tr);
    });

    $('#preview_file_name').val(response.file_name);
    $('#previewImportModal').modal('show');
}
```

**Status Badge Helper:**
```javascript
function getStatusBadge(statusClass, statusText) {
    return '<span class="label label-' + statusClass + '">' +
           statusText + '</span>';
}
```

**Checkbox Selection Logic:**
```javascript
// Select/deselect all importable rows
$('#selectAllImportable').on('change', function() {
    var checked = $(this).is(':checked');
    $('.row-checkbox:not(:disabled)').prop('checked', checked);
    updateSelectedCount();
});

// Update count when individual checkbox changes
$(document).on('change', '.row-checkbox', function() {
    updateSelectedCount();
});

// Update selected count and button state
function updateSelectedCount() {
    var count = $('.row-checkbox:checked').length;
    $('#selectedCount').text(count);
    $('#importCountBadge').text(count);

    if (count > 0) {
        $('#proceedImportBtn').prop('disabled', false);
    } else {
        $('#proceedImportBtn').prop('disabled', true);
    }
}
```

**Import Processing:**
```javascript
$('#proceedImportBtn').click(function() {
    var selectedRows = [];
    $('.row-checkbox:checked').each(function() {
        selectedRows.push($(this).data('row-index'));
    });

    if (selectedRows.length === 0) {
        alert_float('warning', 'Please select at least one employee to import');
        return;
    }

    $.ajax({
        url: admin_url + 'timesheet/process_selective_import',
        type: 'POST',
        data: {
            selected_rows: selectedRows,
            file_name: $('#preview_file_name').val(),
            project_id: $('#import_project_id').val(),
            selected_month: $('#selected_month').val()
        },
        success: function(response) {
            $('#previewImportModal').modal('hide');

            if (response.success) {
                showResultModal(response);
            } else {
                alert_float('danger', response.message);
            }
        }
    });
});
```

**Result Modal Display:**
```javascript
function showResultModal(response) {
    $('#importedCount').text(response.imported);
    $('#skippedCount').text(response.skipped);

    var errorList = $('#errorList');
    errorList.empty();

    if (response.errors && response.errors.length > 0) {
        $('#errorSection').show();
        $.each(response.errors, function(index, error) {
            errorList.append('<li>' + error + '</li>');
        });
    } else {
        $('#errorSection').hide();
    }

    $('#resultModal').modal('show');
}
```

### 5. Directory Structure

```
uploads/
└── timesheets/
    └── temp/          # Temporary storage for preview files
        └── (files are deleted after import)
```

**Note:** The `temp` folder is created automatically if it doesn't exist. Files are automatically deleted after import processing.

### 6. Excel File Format

The Excel file should follow this structure:

| Column | Header | Format | Required |
|--------|--------|--------|----------|
| A | IQAMA | Text/Number | Yes |
| B | Name | Text | Yes |
| C | Day 1 | Number (hours) | No |
| D | Day 1 OT | Number (hours) | No |
| E | Day 2 | Number (hours) | No |
| F | Day 2 OT | Number (hours) | No |
| ... | ... | ... | ... |
| Last-1 | Day 31 | Number (hours) | No |
| Last | Day 31 OT | Number (hours) | No |

**Important Notes:**
- IQAMA number must match exactly with staff records in database
- Alternating columns: Regular hours, then Overtime hours
- Empty cells are treated as 0 hours
- First row is headers (skipped during import)

### 7. Security Considerations

- **File Upload Validation**: Only Excel files allowed (.xlsx, .xls)
- **Temporary Storage**: Files stored in temp folder, deleted after processing
- **CSRF Protection**: All AJAX requests include CSRF token
- **Input Validation**: Staff ID, project ID, and month validated on server side
- **SQL Injection Prevention**: Uses CodeIgniter query builder with parameter binding
- **Access Control**: Only authenticated admin users can access import functionality

### 8. Error Handling

**Common Errors:**

1. **File Upload Error**
   - Invalid file type
   - File too large
   - Upload directory permissions

2. **Parse Error**
   - Corrupted Excel file
   - Invalid file structure
   - Missing required columns

3. **Import Error**
   - Database connection issues
   - Duplicate key violations
   - Invalid data types

**Error Response Example:**
```json
{
    "success": false,
    "message": "Error uploading file",
    "errors": [
        "Row 5: Invalid hours format",
        "Row 8: Date out of range"
    ]
}
```

### 9. User Interface Features

✅ **Preview Before Import** - See all employees before any database changes
✅ **Selective Import** - Choose which employees to import via checkboxes
✅ **Status Indicators** - Color-coded badges show import readiness
✅ **Batch Selection** - "Select all importable" checkbox for convenience
✅ **Live Counter** - Shows how many rows selected in real-time
✅ **Disabled Checkboxes** - Cannot select non-importable rows
✅ **Missing Employees Detection** - Shows assigned employees not found in Excel
✅ **Two-Section Display** - Separates employees in Excel from missing employees
✅ **Total Hours Preview** - Shows calculated hours for each employee in Excel
✅ **Detailed Results** - Shows exactly what was imported/skipped
✅ **Error Feedback** - Lists any errors that occurred during import
✅ **Refresh Option** - Easy way to reload page and see imported data

### 10. Testing Checklist

- [ ] Upload Excel with all valid employees
- [ ] Upload Excel with non-existent IQAMA numbers
- [ ] Upload Excel with employees not assigned to project
- [ ] Upload Excel with employees who already have timesheets
- [ ] Upload Excel missing some assigned employees (verify missing section appears)
- [ ] Upload Excel with all assigned employees (verify missing section is hidden)
- [ ] Test "Select all importable" checkbox
- [ ] Test individual checkbox selection
- [ ] Test import with 0 rows selected (should show warning)
- [ ] Test import with partial selection
- [ ] Verify missing employees table shows correct employees
- [ ] Verify missing employees are highlighted with warning color
- [ ] Verify total hours calculation in preview
- [ ] Verify temp file is deleted after import
- [ ] Verify existing timesheet updates correctly
- [ ] Check error handling for corrupted files
- [ ] Verify result modal shows correct counts

### 11. Performance Considerations

- **Chunked Processing**: Large files processed row by row
- **Temporary Storage**: Files stored temporarily to avoid multiple uploads
- **Batch Queries**: Uses batch insert when possible
- **Index Usage**: Database queries use indexes on staff_id, project_id, month_year
- **File Cleanup**: Automatic deletion prevents disk space buildup

### 12. Integration with Existing Features

This preview system integrates seamlessly with:

- **Existing Import Logic**: Reuses same data processing and validation
- **Timesheet Edit Page**: Imported data appears in edit grid
- **Salary Calculations**: Imported hours affect salary details immediately
- **Project Assignment**: Respects project-staff assignments
- **Month Selection**: Works with selected month from dropdown

## Missing Employees Detection

### What It Does

The preview modal now includes a **second section** that shows employees who are:
- ✅ Assigned to the project
- ❌ NOT found in the uploaded Excel file

This helps administrators identify missing employees before import, ensuring complete timesheet records.

### How It Works

1. **Backend Logic** (`application/controllers/admin/Timesheet.php:641-667`):
   - Tracks all staff IDs found in Excel (`$staffIdsInExcel`)
   - Gets all staff assigned to the project (`$assignedStaffIds`)
   - Calculates difference: `array_diff($assignedStaffIds, $staffIdsInExcel)`
   - Queries staff table to get missing employee details

2. **Frontend Display** (`application/views/admin/projects/project_timesheets.php:168-192`):
   - Shows "Missing from Excel" section only if missing employees exist
   - Displays table with employee name, IQAMA, and status badge
   - Highlights rows with warning color (yellow background)
   - Section is hidden if all assigned employees are in Excel

### Visual Layout

```
┌─────────────────────────────────────────────────┐
│ Preview Timesheet Import                        │
├─────────────────────────────────────────────────┤
│ 📊 Employees in Excel Sheet                     │
│ ┌─────────────────────────────────────────────┐ │
│ │ ☑ Select All | 5 employees                 │ │
│ │ ✓ John Doe    | Ready to import            │ │
│ │ ✓ Jane Smith  | Will update (12 entries)   │ │
│ │ ⚠ Bob Jones   | Not assigned               │ │
│ └─────────────────────────────────────────────┘ │
│                                                  │
│ ⚠️  Missing from Excel                           │
│ (Assigned to project but not in Excel file)     │
│ ┌─────────────────────────────────────────────┐ │
│ │ ⚠ Alice Brown | Missing from Excel         │ │
│ │ ⚠ Tom Wilson  | Missing from Excel         │ │
│ └─────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────┘
```

### Benefits

1. **Data Completeness** - Identifies gaps in Excel before import
2. **Error Prevention** - Catches missing employees that should be included
3. **Audit Trail** - Clear record of who was excluded from import
4. **User Confidence** - Full visibility into import scope

## Summary

The Timesheet Import Preview feature provides complete transparency and control over the Excel import process. Users can:

1. **See what will happen** before any data is saved
2. **Identify issues** (non-existent employees, unassigned staff) before importing
3. **Detect missing employees** who are assigned but not in Excel
4. **Choose selectively** which employees to import
5. **Understand the impact** (new records vs. updates)
6. **Review results** with detailed feedback

This approach reduces errors, increases confidence, and gives users full control over the timesheet import workflow.

## Version History

- **v1.1** (2025-10-26) - Missing Employees Detection
  - Added separate section for employees missing from Excel
  - Shows assigned employees not found in uploaded file
  - Helps ensure complete timesheet coverage

- **v1.0** (2025-10-26) - Initial implementation
  - Preview modal with status indicators
  - Selective row import via checkboxes
  - Result modal with detailed feedback
  - Temporary file storage system
