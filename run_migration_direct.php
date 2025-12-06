<?php
/**
 * Direct Database Migration Runner for HR Payroll Module Version 110
 *
 * This script connects directly to the database and creates the required tables
 * Run this from your browser: https://rwaderp.local/run_migration_direct.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Manual database configuration
// UPDATE THESE VALUES IF DIFFERENT FROM YOUR SETUP
$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'bluespot';
$db_name = 'rwaderpdb';
$db_prefix = 'tbl';

?>
<!DOCTYPE html>
<html>
<head>
    <title>HR Payroll Migration - Direct Database</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        h1 { color: #333; border-bottom: 3px solid #667eea; padding-bottom: 15px; margin-bottom: 30px; }
        h2 { color: #555; margin-top: 30px; }
        .success { color: #155724; background: #d4edda; padding: 15px; border-radius: 6px; border-left: 5px solid #28a745; margin: 15px 0; }
        .error { color: #721c24; background: #f8d7da; padding: 15px; border-radius: 6px; border-left: 5px solid #dc3545; margin: 15px 0; }
        .info { color: #004085; background: #cce5ff; padding: 15px; border-radius: 6px; border-left: 5px solid #007bff; margin: 15px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 15px; border-radius: 6px; border-left: 5px solid #ffc107; margin: 15px 0; }
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .table th, .table td { padding: 14px; text-align: left; border-bottom: 1px solid #dee2e6; }
        .table th { background: #f8f9fa; font-weight: 600; color: #495057; }
        .table tr:hover { background: #f8f9fa; }
        code { background: #f4f4f4; padding: 3px 8px; border-radius: 4px; font-family: "Courier New", monospace; color: #e83e8c; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .sql-query { background: #2d3748; color: #68d391; padding: 15px; border-radius: 6px; overflow-x: auto; margin: 10px 0; font-family: monospace; font-size: 13px; }
        .progress { width: 100%; background: #e9ecef; height: 30px; border-radius: 15px; overflow: hidden; margin: 20px 0; }
        .progress-bar { background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); height: 100%; line-height: 30px; color: white; text-align: center; font-weight: bold; transition: width 0.3s; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🚀 HR Payroll Module Migration v110</h1>
        <div class='info'>
            <strong>Database:</strong> <?php echo htmlspecialchars($db_name); ?><br>
            <strong>Host:</strong> <?php echo htmlspecialchars($db_host); ?><br>
            <strong>Table Prefix:</strong> <?php echo htmlspecialchars($db_prefix); ?><br>
            <strong>Target Version:</strong> 110 (Revision 1100)
        </div>

<?php

try {
    // Connect to database
    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }

    $mysqli->set_charset("utf8mb4");

    echo "<div class='success'>✓ Database connection established successfully</div>";

    // Migration queries
    $migrations = [];
    $migration_results = [];

    // 0. Create or complete tblhrp_employees_value table (if missing)
    $migrations[] = [
        'name' => 'Create/Complete hrp_employees_value table',
        'query' => "CREATE TABLE IF NOT EXISTS `{$db_prefix}hrp_employees_value` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `staff_id` INT(11) NULL,
            `month` DATE NOT NULL,
            `job_title` VARCHAR(200) NULL,
            `income_tax_number` VARCHAR(200) NULL,
            `residential_address` TEXT NULL,
            `income_rebate_code` VARCHAR(200) NULL,
            `income_tax_rate` VARCHAR(200) NULL,
            `probationary_contracts` LONGTEXT NULL,
            `primary_contracts` LONGTEXT NULL,
            `rel_type` VARCHAR(100),
            `probationary_effective` DATE NULL,
            `probationary_expiration` DATE NULL,
            `primary_effective` DATE NULL,
            `primary_expiration` DATE NULL,
            `employee_id_iqama` VARCHAR(200) NULL,
            `employee_account_no_iban` VARCHAR(200) NULL,
            `bank_code` VARCHAR(50) NULL,
            `bank_name` VARCHAR(200) NULL,
            `gosi_basic_salary` DECIMAL(15,2) DEFAULT 0.00,
            `gosi_housing_allowance` DECIMAL(15,2) DEFAULT 0.00,
            `gosi_other_allowance` DECIMAL(15,2) DEFAULT 0.00,
            `gosi_deduction` DECIMAL(15,2) DEFAULT 0.00,
            `total_amount` DECIMAL(15,2) DEFAULT 0.00,
            `balance` DECIMAL(15,2) DEFAULT 0.00,
            `full_salary` DECIMAL(15,2) DEFAULT 0.00,
            `basic` DECIMAL(15,2) DEFAULT 0.00,
            `ot_hours` DECIMAL(10,2) DEFAULT 0.00,
            `ot_rate` DECIMAL(15,2) DEFAULT 0.00,
            `ot_amount` DECIMAL(15,2) DEFAULT 0.00,
            `allowance` DECIMAL(15,2) DEFAULT 0.00,
            `deduction` DECIMAL(15,2) DEFAULT 0.00,
            `mention` TEXT NULL,
            `epf_no` VARCHAR(100) NULL,
            `social_security_no` VARCHAR(100) NULL,
            PRIMARY KEY (`id`),
            KEY `idx_staff_month` (`staff_id`, `month`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];

    // 1. Create tblhrp_payroll table
    $migrations[] = [
        'name' => 'Create hrp_payroll table',
        'query' => "CREATE TABLE IF NOT EXISTS `{$db_prefix}hrp_payroll` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];

    // 2. Add payroll_id to hrp_employees_value (only if column doesn't exist)
    // Note: Check if column exists first
    $check_payroll_id = $mysqli->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
                                        WHERE TABLE_SCHEMA = '{$db_name}'
                                        AND TABLE_NAME = '{$db_prefix}hrp_employees_value'
                                        AND COLUMN_NAME = 'payroll_id'");

    if (!$check_payroll_id || $check_payroll_id->num_rows == 0) {
        $migrations[] = [
            'name' => 'Add payroll_id to hrp_employees_value',
            'query' => "ALTER TABLE `{$db_prefix}hrp_employees_value`
                ADD COLUMN `payroll_id` int(11) DEFAULT NULL COMMENT 'Foreign key to tbl_hrp_payroll' AFTER `id`,
                ADD KEY `idx_payroll_id` (`payroll_id`)"
        ];
    } else {
        echo "<div class='info'>ℹ payroll_id column already exists in hrp_employees_value - skipping</div>";
    }

    // 3. Create tblhrp_project_payments table
    $migrations[] = [
        'name' => 'Create hrp_project_payments table',
        'query' => "CREATE TABLE IF NOT EXISTS `{$db_prefix}hrp_project_payments` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `payroll_id` int(11) NOT NULL COMMENT 'Link to tbl_hrp_payroll',
            `staff_id` int(11) NOT NULL,
            `month` date NOT NULL,
            `project_id` int(11) DEFAULT NULL COMMENT 'Link to tbl_projects if applicable',
            `project_name` varchar(255) DEFAULT NULL,
            `ot_hours` decimal(10,2) DEFAULT 0.00,
            `ot_rate` decimal(15,2) DEFAULT 0.00,
            `ot_amount` decimal(15,2) DEFAULT 0.00,
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];

    // 4. Create tblhrp_payroll_status_log table
    $migrations[] = [
        'name' => 'Create hrp_payroll_status_log table',
        'query' => "CREATE TABLE IF NOT EXISTS `{$db_prefix}hrp_payroll_status_log` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `payroll_id` int(11) NOT NULL,
            `from_status` varchar(50) DEFAULT NULL,
            `to_status` varchar(50) NOT NULL,
            `changed_by` int(11) NOT NULL,
            `changed_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `comments` text DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_payroll_id` (`payroll_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];

    // Run migrations
    $total_migrations = count($migrations);
    $completed = 0;

    echo "<h2>Running Migrations</h2>";
    echo "<div class='progress'><div class='progress-bar' id='progress-bar' style='width: 0%'>0%</div></div>";

    foreach ($migrations as $index => $migration) {
        $migration_name = $migration['name'];
        $query = $migration['query'];

        echo "<h3>" . ($index + 1) . ". {$migration_name}</h3>";

        // Check if we need to skip (for ALTER TABLE)
        $should_skip = false;
        if (isset($migration['check'])) {
            $check_result = $mysqli->query($migration['check']);
            if ($check_result && $check_result->num_rows > 0) {
                echo "<div class='warning'>⚠ Already exists - skipping</div>";
                $should_skip = true;
            }
        }

        if (!$should_skip) {
            echo "<div class='sql-query'>" . htmlspecialchars(substr($query, 0, 200)) . "...</div>";

            if ($mysqli->query($query)) {
                echo "<div class='success'>✓ Success</div>";
                $migration_results[] = ['name' => $migration_name, 'status' => 'success'];
            } else {
                $error = $mysqli->error;
                echo "<div class='error'>✗ Error: {$error}</div>";
                $migration_results[] = ['name' => $migration_name, 'status' => 'error', 'error' => $error];
            }
        } else {
            $migration_results[] = ['name' => $migration_name, 'status' => 'skipped'];
        }

        $completed++;
        $percent = round(($completed / $total_migrations) * 100);
        echo "<script>document.getElementById('progress-bar').style.width = '{$percent}%'; document.getElementById('progress-bar').textContent = '{$percent}%';</script>";
        flush();
    }

    // Verify tables
    echo "<h2>Verification</h2>";
    echo "<table class='table'>";
    echo "<thead><tr><th>Table Name</th><th>Status</th><th>Row Count</th><th>Fields</th></tr></thead>";
    echo "<tbody>";

    $tables_to_check = [
        'hrp_payroll',
        'hrp_project_payments',
        'hrp_payroll_status_log',
        'hrp_employees_value'
    ];

    foreach ($tables_to_check as $table) {
        $full_table = "{$db_prefix}{$table}";
        $result = $mysqli->query("SHOW TABLES LIKE '{$full_table}'");

        if ($result && $result->num_rows > 0) {
            // Get row count
            $count_result = $mysqli->query("SELECT COUNT(*) as cnt FROM `{$full_table}`");
            $count = $count_result ? $count_result->fetch_assoc()['cnt'] : 0;

            // Get field count
            $field_result = $mysqli->query("SHOW COLUMNS FROM `{$full_table}`");
            $field_count = $field_result ? $field_result->num_rows : 0;

            echo "<tr>";
            echo "<td><code>{$full_table}</code></td>";
            echo "<td><span class='badge badge-success'>✓ EXISTS</span></td>";
            echo "<td>{$count} rows</td>";
            echo "<td>{$field_count} fields</td>";
            echo "</tr>";

            // For hrp_employees_value, check if payroll_id exists
            if ($table == 'hrp_employees_value') {
                $check_field = $mysqli->query("SHOW COLUMNS FROM `{$full_table}` LIKE 'payroll_id'");
                if ($check_field && $check_field->num_rows > 0) {
                    echo "<tr><td colspan='4' style='padding-left: 40px;'>└─ <code>payroll_id</code> column: <span class='badge badge-success'>✓ ADDED</span></td></tr>";
                } else {
                    echo "<tr><td colspan='4' style='padding-left: 40px;'>└─ <code>payroll_id</code> column: <span class='badge badge-danger'>✗ MISSING</span></td></tr>";
                }
            }
        } else {
            echo "<tr>";
            echo "<td><code>{$full_table}</code></td>";
            echo "<td><span class='badge badge-danger'>✗ NOT FOUND</span></td>";
            echo "<td>-</td>";
            echo "<td>-</td>";
            echo "</tr>";
        }
    }

    echo "</tbody></table>";

    // Summary
    $success_count = count(array_filter($migration_results, fn($r) => $r['status'] == 'success'));
    $skipped_count = count(array_filter($migration_results, fn($r) => $r['status'] == 'skipped'));
    $error_count = count(array_filter($migration_results, fn($r) => $r['status'] == 'error'));

    echo "<h2>✓ Migration Summary</h2>";
    echo "<div class='info'>";
    echo "<strong>Total Migrations:</strong> {$total_migrations}<br>";
    echo "<strong>Successful:</strong> {$success_count}<br>";
    echo "<strong>Skipped:</strong> {$skipped_count}<br>";
    echo "<strong>Errors:</strong> {$error_count}<br>";
    echo "</div>";

    if ($error_count == 0) {
        echo "<div class='success'>";
        echo "<h3>🎉 Migration Completed Successfully!</h3>";
        echo "<strong>Next Steps:</strong><br>";
        echo "1. ✓ Database tables created successfully<br>";
        echo "2. → Continue with controller implementation<br>";
        echo "3. → Create payroll list view<br>";
        echo "4. → Test payroll generation<br>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<strong>⚠ Some migrations failed. Please check the errors above and fix them manually.</strong>";
        echo "</div>";
    }

    $mysqli->close();

} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<strong>✗ Fatal Error:</strong><br>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";
}

?>

        <div class='info' style='margin-top: 40px;'>
            <strong>Note:</strong> You can safely delete this file (<code>run_migration_direct.php</code>) after successful migration.
        </div>
    </div>
</body>
</html>