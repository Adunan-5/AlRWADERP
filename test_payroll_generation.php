<?php
/**
 * Test Payroll Generation
 */

// Load CodeIgniter
$_SERVER['REQUEST_METHOD'] = 'GET';
require_once 'index.php';

$CI =& get_instance();

echo "<h1>Testing Payroll Generation</h1>";
echo "<pre>";

// Load models
$CI->load->model('hr_payroll/hr_payroll_model');
$CI->load->helper('ownemployee_type');

echo "=== STEP 1: Check Database Connection ===\n";
echo "Database: " . $CI->db->database . "\n";
echo "Table prefix: " . db_prefix() . "\n\n";

echo "=== STEP 2: Check Employee Types ===\n";
$employee_types = get_all_ownemployee_types();
foreach ($employee_types as $type) {
    echo "- ID: {$type['id']}, Name: {$type['name']}\n";
}
echo "\n";

echo "=== STEP 3: Test Payroll Creation ===\n";
$month = '2025-11-01';
$company_filter = 'mohtarifeen';
$ownemployee_type_id = 10; // OWN_BASIC_INDIRECT
$rel_type = hrp_get_hr_profile_status();

echo "Month: $month\n";
echo "Company: $company_filter\n";
echo "Employee Type ID: $ownemployee_type_id\n";
echo "Rel Type: $rel_type\n\n";

// Check if payroll already exists
$CI->db->where('month', $month);
$CI->db->where('company_filter', $company_filter);
$CI->db->where('ownemployee_type_id', $ownemployee_type_id);
$existing = $CI->db->get(db_prefix() . 'hrp_payroll')->row();

if ($existing) {
    echo "⚠️ Payroll already exists! ID: {$existing->id}\n";
    echo "Deleting existing payroll...\n";
    $CI->hr_payroll_model->delete_payroll($existing->id);
}

// Create payroll
$payroll_data = [
    'month' => $month,
    'company_filter' => $company_filter,
    'ownemployee_type_id' => $ownemployee_type_id,
    'created_by' => 1, // Assuming admin user ID is 1
    'rel_type' => $rel_type,
];

echo "Creating payroll header...\n";
$payroll_id = $CI->hr_payroll_model->create_payroll($payroll_data);

if ($payroll_id === false) {
    echo "❌ FAILED to create payroll header!\n";
    echo "Error: " . $CI->db->error()['message'] . "\n";
    exit;
}

echo "✓ Payroll header created! ID: $payroll_id\n\n";

echo "=== STEP 4: Check Eligible Employees ===\n";
// Build same query as generate_payroll_employees
$CI->db->select('s.staffid, s.firstname, s.lastname, s.ownemployee_id, s.companytype_id, sp.basic_pay, sp.allowance');
$CI->db->from(db_prefix() . 'staff s');
$CI->db->join(db_prefix() . 'staffpay sp', 'sp.staff_id = s.staffid', 'left');
$CI->db->where('s.active', 1);
$CI->db->where('s.ownemployee_id', $ownemployee_type_id);
$CI->db->where_not_in('s.staffid', [1, 3]);
if ($company_filter == 'mohtarifeen') {
    $CI->db->where('s.companytype_id', 2);
} elseif ($company_filter == 'mahiroon') {
    $CI->db->where('s.companytype_id !=', 2);
}
$employees = $CI->db->get()->result_array();

echo "Found " . count($employees) . " eligible employees\n";
if (count($employees) > 0) {
    echo "\nFirst 5 employees:\n";
    foreach (array_slice($employees, 0, 5) as $emp) {
        echo "- ID: {$emp['staffid']}, Name: {$emp['firstname']} {$emp['lastname']}, Basic Pay: " . ($emp['basic_pay'] ?? 'NULL') . "\n";
    }
}
echo "\n";

echo "=== STEP 5: Generate Employee Records ===\n";
$employees_count = $CI->hr_payroll_model->generate_payroll_employees(
    $payroll_id,
    $month,
    $company_filter,
    $ownemployee_type_id,
    $rel_type
);

echo "Generated records for $employees_count employees\n\n";

if ($employees_count == 0) {
    echo "❌ NO EMPLOYEES GENERATED!\n";
    echo "Checking last query...\n";
    echo "Last Query: " . $CI->db->last_query() . "\n";
} else {
    echo "✓ SUCCESS!\n\n";
}

echo "=== STEP 6: Verify Payroll ===\n";
$payroll = $CI->hr_payroll_model->get_payroll($payroll_id);
if ($payroll) {
    echo "Payroll Number: {$payroll->payroll_number}\n";
    echo "Month: {$payroll->month}\n";
    echo "Status: {$payroll->status}\n";
    echo "Total Employees: {$payroll->total_employees}\n";
    echo "Total Amount: {$payroll->total_amount}\n\n";
}

echo "=== STEP 7: Check Employee Records ===\n";
$CI->db->where('payroll_id', $payroll_id);
$employee_records = $CI->db->get(db_prefix() . 'hrp_employees_value')->result_array();
echo "Employee records in database: " . count($employee_records) . "\n";

if (count($employee_records) > 0) {
    echo "\nFirst 3 records:\n";
    foreach (array_slice($employee_records, 0, 3) as $rec) {
        echo "- Staff ID: {$rec['staff_id']}, Basic: {$rec['basic']}, Full Salary: {$rec['full_salary']}\n";
    }
}

echo "\n=== STEP 8: Test DataTable Query ===\n";
$payrolls = $CI->hr_payroll_model->get_payrolls([]);
echo "Total payrolls returned: " . count($payrolls) . "\n";

if (count($payrolls) > 0) {
    echo "\nPayrolls:\n";
    foreach ($payrolls as $p) {
        echo "- ID: {$p['id']}, Number: {$p['payroll_number']}, Employees: {$p['total_employees']}\n";
    }
} else {
    echo "❌ No payrolls returned from get_payrolls()!\n";
}

echo "\n=== DONE ===\n";
echo "\nIf payroll was created successfully, you can view it at:\n";
echo admin_url('hr_payroll/manage_employees?payroll_id=' . $payroll_id) . "\n";

echo "</pre>";
