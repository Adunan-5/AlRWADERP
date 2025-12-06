<?php
/**
 * Check Table Names and Company Type Structure
 */

$mysqli = new mysqli('localhost', 'root', 'bluespot', 'rwaderpdb');
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

echo "<h1>Table Names and Company Type Check</h1>";
echo "<pre>";

echo "=== Check for payroll tables ===\n";
$result = $mysqli->query("SHOW TABLES LIKE '%hrp_payroll%'");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_array()) {
        echo "Found table: " . $row[0] . "\n";
    }
} else {
    echo "No payroll tables found!\n";
}

echo "\n=== Check for employees_value tables ===\n";
$result = $mysqli->query("SHOW TABLES LIKE '%hrp_employees_value%'");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_array()) {
        echo "Found table: " . $row[0] . "\n";
    }
} else {
    echo "No employees_value tables found!\n";
}

echo "\n=== Check tblcompanytype structure ===\n";
$result = $mysqli->query("DESCRIBE tblcompanytype");
if ($result) {
    echo "tblcompanytype fields:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  - {$row['Field']} ({$row['Type']})\n";
    }
} else {
    echo "ERROR: tblcompanytype table not found!\n";
}

echo "\n=== Company types data ===\n";
$result = $mysqli->query("SELECT * FROM tblcompanytype");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']}, Name: {$row['name']}\n";
    }
} else {
    echo "No company types found!\n";
}

echo "\n=== Check staff companytype_id distribution ===\n";
$result = $mysqli->query("
    SELECT s.companytype_id, ct.name, COUNT(*) as count
    FROM tblstaff s
    LEFT JOIN tblcompanytype ct ON ct.id = s.companytype_id
    WHERE s.active = 1
    GROUP BY s.companytype_id, ct.name
    ORDER BY count DESC
");
if ($result && $result->num_rows > 0) {
    echo "Active employees by company type:\n";
    while ($row = $result->fetch_assoc()) {
        $company = $row['name'] ?? 'NULL';
        echo "  Company ID: {$row['companytype_id']}, Name: {$company}, Employees: {$row['count']}\n";
    }
}

echo "\n=== Check if data exists in both payroll tables ===\n";
$tables_to_check = ['tbl_hrp_payroll', 'tblhrp_payroll'];
foreach ($tables_to_check as $table) {
    $result = $mysqli->query("SELECT COUNT(*) as cnt FROM $table");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "$table: {$row['cnt']} records\n";
    } else {
        echo "$table: Does not exist\n";
    }
}

$mysqli->close();

echo "\n=== DONE ===\n";
echo "</pre>";
