<?php
/**
 * Check Employee Data and Database Structure
 */

$mysqli = new mysqli('localhost', 'root', 'bluespot', 'rwaderpdb');
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

echo "<h1>Database Structure Check</h1>";
echo "<pre>";

echo "=== Checking tblstaff structure ===\n";
$result = $mysqli->query("DESCRIBE tblstaff");
$staff_fields = [];
while ($row = $result->fetch_assoc()) {
    $staff_fields[] = $row['Field'];
}
echo "Fields in tblstaff containing 'iqama', 'bank', or 'number':\n";
foreach ($staff_fields as $field) {
    if (stripos($field, 'iqama') !== false || stripos($field, 'bank') !== false || stripos($field, 'number') !== false) {
        echo "  - $field\n";
    }
}

echo "\n=== Checking tblstaffpay structure ===\n";
$result = $mysqli->query("DESCRIBE tblstaffpay");
echo "All fields in tblstaffpay:\n";
while ($row = $result->fetch_assoc()) {
    echo "  - {$row['Field']} ({$row['Type']})\n";
}

echo "\n=== Checking employee count ===\n";
$result = $mysqli->query("SELECT COUNT(*) as cnt FROM tblstaff WHERE active = 1 AND ownemployee_id = 10");
$row = $result->fetch_assoc();
echo "Active employees with ownemployee_id=10: {$row['cnt']}\n";

$result = $mysqli->query("SELECT COUNT(*) as cnt FROM tblstaff WHERE active = 1 AND ownemployee_id = 10 AND companytype_id = 2");
$row = $result->fetch_assoc();
echo "Active Mohtarifeen employees with ownemployee_id=10: {$row['cnt']}\n";

echo "\n=== Sample employee data with JOIN ===\n";
$result = $mysqli->query("
    SELECT s.staffid, s.firstname, s.lastname, s.ownemployee_id, s.companytype_id,
           sp.staff_id as sp_staffid, sp.basic_pay
    FROM tblstaff s
    LEFT JOIN tblstaffpay sp ON sp.staff_id = s.staffid
    WHERE s.active = 1
      AND s.ownemployee_id = 10
      AND s.companytype_id = 2
      AND s.staffid NOT IN (1, 3)
    LIMIT 5
");
while ($row = $result->fetch_assoc()) {
    echo "Staff ID: {$row['staffid']}, Name: {$row['firstname']} {$row['lastname']}, ";
    echo "Has staffpay: " . ($row['sp_staffid'] ? 'YES' : 'NO');
    echo ", Basic Pay: " . ($row['basic_pay'] ?? 'NULL') . "\n";
}

echo "\n=== Checking for existing payrolls ===\n";
$result = $mysqli->query("SELECT id, payroll_number, month, status, total_employees FROM tblhrp_payroll ORDER BY id DESC LIMIT 5");
if ($result->num_rows > 0) {
    echo "Recent payrolls:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  - ID: {$row['id']}, Number: {$row['payroll_number']}, Month: {$row['month']}, Status: {$row['status']}, Employees: {$row['total_employees']}\n";
    }
} else {
    echo "No payrolls found in database.\n";
}

echo "\n=== Test Query (exact same as model) ===\n";
$query = "
    SELECT s.*, sp.*, CONCAT(s.firstname, ' ', s.lastname) as full_name
    FROM tblstaff s
    LEFT JOIN tblstaffpay sp ON sp.staff_id = s.staffid
    WHERE s.active = 1
      AND s.ownemployee_id = 10
      AND s.staffid NOT IN (1, 3)
      AND s.companytype_id = 2
";
$result = $mysqli->query($query);
echo "Query returned: " . $result->num_rows . " rows\n";

if ($result->num_rows > 0) {
    echo "\nFirst employee data:\n";
    $first = $result->fetch_assoc();
    echo "  staffid: " . ($first['staffid'] ?? 'NULL') . "\n";
    echo "  firstname: " . ($first['firstname'] ?? 'NULL') . "\n";
    echo "  lastname: " . ($first['lastname'] ?? 'NULL') . "\n";
    echo "  iqama_number: " . (isset($first['iqama_number']) ? $first['iqama_number'] : 'FIELD DOES NOT EXIST') . "\n";
    echo "  bank_iban_number: " . (isset($first['bank_iban_number']) ? $first['bank_iban_number'] : 'FIELD DOES NOT EXIST') . "\n";
    echo "  bank_swift_code: " . (isset($first['bank_swift_code']) ? $first['bank_swift_code'] : 'FIELD DOES NOT EXIST') . "\n";
    echo "  basic_pay: " . ($first['basic_pay'] ?? 'NULL') . "\n";
    echo "  fat_allowance: " . ($first['fat_allowance'] ?? 'NULL') . "\n";
    echo "  food_allowance: " . ($first['food_allowance'] ?? 'NULL') . "\n";
    echo "  allowance: " . ($first['allowance'] ?? 'NULL') . "\n";
    echo "  mewa: " . ($first['mewa'] ?? 'NULL') . "\n";
}

$mysqli->close();

echo "\n=== DONE ===\n";
echo "</pre>";
