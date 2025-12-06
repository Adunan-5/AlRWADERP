<?php
/**
 * View Payroll Generation Logs
 */

$mysqli = new mysqli('localhost', 'root', 'bluespot', 'rwaderpdb');
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

echo "<h1>Payroll Generation Activity Logs</h1>";
echo "<pre>";

echo "=== Recent Payroll-Related Activity ===\n";
$result = $mysqli->query("
    SELECT id, staffid, date, description
    FROM tblactivity_log
    WHERE description LIKE '%Payroll%' OR description LIKE '%payroll%'
    ORDER BY id DESC
    LIMIT 50
");

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "[{$row['id']}] {$row['date']} - Staff:{$row['staffid']}\n";
        echo "    " . $row['description'] . "\n\n";
    }
} else {
    echo "No payroll activity logs found.\n";
}

echo "\n=== All Recent Activity (last 20) ===\n";
$result = $mysqli->query("
    SELECT id, staffid, date, description
    FROM tblactivity_log
    ORDER BY id DESC
    LIMIT 20
");

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "[{$row['id']}] {$row['date']} - Staff:{$row['staffid']}\n";
        echo "    " . substr($row['description'], 0, 100) . "...\n\n";
    }
}

echo "\n=== Check existing payrolls ===\n";
$result = $mysqli->query("
    SELECT id, payroll_number, month, company_filter, ownemployee_type_id,
           status, total_employees, total_amount, created_date
    FROM tblhrp_payroll
    ORDER BY id DESC
    LIMIT 10
");

if ($result && $result->num_rows > 0) {
    echo "Found " . $result->num_rows . " payrolls:\n";
    while ($row = $result->fetch_assoc()) {
        echo "ID:{$row['id']}, Number:{$row['payroll_number']}, Month:{$row['month']}, ";
        echo "Company:{$row['company_filter']}, Type:{$row['ownemployee_type_id']}, ";
        echo "Employees:{$row['total_employees']}, Status:{$row['status']}\n";
    }
} else {
    echo "No payrolls found in database.\n";
}

$mysqli->close();

echo "</pre>";

echo "<p><a href='javascript:history.back()'>Go Back</a> | ";
echo "<a href='javascript:location.reload()'>Refresh</a></p>";
