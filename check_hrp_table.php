<?php
/**
 * Check tblhrp_employees_value table structure
 */

$mysqli = new mysqli('localhost', 'root', 'bluespot', 'rwaderpdb');
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

echo "<h1>HR Payroll Table Structure</h1>";
echo "<pre>";

echo "=== tblhrp_employees_value structure ===\n";
$result = $mysqli->query("DESCRIBE tblhrp_employees_value");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']}\n";
    }
} else {
    echo "ERROR: " . $mysqli->error . "\n";
}

echo "\n=== tblhrp_payroll structure ===\n";
$result = $mysqli->query("DESCRIBE tblhrp_payroll");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']}\n";
    }
} else {
    echo "ERROR: " . $mysqli->error . "\n";
}

$mysqli->close();

echo "</pre>";
