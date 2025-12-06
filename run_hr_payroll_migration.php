<?php
/**
 * HR Payroll Module Migration Runner
 *
 * This script runs the hr_payroll module migration to version 110
 * Execute this file from the browser or command line to run the migration
 *
 * URL: https://rwaderp.local/run_hr_payroll_migration.php
 */

// Load CodeIgniter
require_once 'index.php';

// Get CodeIgniter instance
$CI =& get_instance();

// Load the migration library
$CI->load->library('App_module_migration', null, 'module_migration');

// Set the module
$module_name = 'hr_payroll';

echo "<!DOCTYPE html>
<html>
<head>
    <title>HR Payroll Migration Runner</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .success { color: #28a745; background: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 15px; border-radius: 5px; border-left: 4px solid #dc3545; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 15px; border-radius: 5px; border-left: 4px solid #17a2b8; margin: 10px 0; }
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
        .table th { background: #f8f9fa; font-weight: bold; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>HR Payroll Module Migration Runner</h1>
        <div class='info'>
            <strong>Module:</strong> {$module_name}<br>
            <strong>Target Version:</strong> 110 (Revision 1100)<br>
            <strong>Migration File:</strong> 110_version_110.php
        </div>
";

try {
    // Check if module exists
    $module_path = FCPATH . 'modules/' . $module_name;
    if (!is_dir($module_path)) {
        throw new Exception("Module directory not found: {$module_path}");
    }

    echo "<h2>Step 1: Checking Module</h2>";
    echo "<div class='success'>✓ Module directory found: {$module_path}</div>";

    // Check if migration file exists
    $migration_file = $module_path . '/migrations/110_version_110.php';
    if (!file_exists($migration_file)) {
        throw new Exception("Migration file not found: {$migration_file}");
    }

    echo "<div class='success'>✓ Migration file found: 110_version_110.php</div>";

    // Load the migration file
    require_once $migration_file;

    echo "<h2>Step 2: Running Migration</h2>";

    // Check if the tables already exist
    $tables_to_create = [
        'hrp_payroll',
        'hrp_project_payments',
        'hrp_payroll_status_log'
    ];

    $tables_exist = [];
    foreach ($tables_to_create as $table) {
        if ($CI->db->table_exists(db_prefix() . $table)) {
            $tables_exist[] = $table;
        }
    }

    if (!empty($tables_exist)) {
        echo "<div class='info'><strong>⚠ Warning:</strong> The following tables already exist:<br>";
        foreach ($tables_exist as $table) {
            echo "- " . db_prefix() . $table . "<br>";
        }
        echo "The migration may skip creating these tables.</div>";
    }

    // Instantiate and run the migration
    $migration = new Migration_Version_110();
    $migration->up();

    echo "<div class='success'>✓ Migration executed successfully!</div>";

    // Verify tables were created
    echo "<h2>Step 3: Verifying Tables</h2>";
    echo "<table class='table'>";
    echo "<thead><tr><th>Table Name</th><th>Status</th><th>Fields</th></tr></thead>";
    echo "<tbody>";

    foreach ($tables_to_create as $table) {
        $full_table_name = db_prefix() . $table;
        $exists = $CI->db->table_exists($full_table_name);

        if ($exists) {
            $fields = $CI->db->list_fields($full_table_name);
            $field_count = count($fields);
            echo "<tr>";
            echo "<td><code>{$full_table_name}</code></td>";
            echo "<td><span style='color: #28a745;'>✓ EXISTS</span></td>";
            echo "<td>{$field_count} fields</td>";
            echo "</tr>";
        } else {
            echo "<tr>";
            echo "<td><code>{$full_table_name}</code></td>";
            echo "<td><span style='color: #dc3545;'>✗ NOT FOUND</span></td>";
            echo "<td>-</td>";
            echo "</tr>";
        }
    }

    // Check if payroll_id was added to hrp_employees_value
    $employees_table = db_prefix() . 'hrp_employees_value';
    if ($CI->db->table_exists($employees_table)) {
        $has_payroll_id = $CI->db->field_exists('payroll_id', $employees_table);
        echo "<tr>";
        echo "<td><code>{$employees_table}</code></td>";
        echo "<td><span style='color: #28a745;'>✓ EXISTS</span></td>";
        echo "<td>payroll_id column: " . ($has_payroll_id ? "<span style='color: #28a745;'>✓ ADDED</span>" : "<span style='color: #dc3545;'>✗ NOT FOUND</span>") . "</td>";
        echo "</tr>";
    }

    echo "</tbody></table>";

    // Display detailed table structure
    echo "<h2>Step 4: Table Structure Details</h2>";

    foreach ($tables_to_create as $table) {
        $full_table_name = db_prefix() . $table;
        if ($CI->db->table_exists($full_table_name)) {
            echo "<h3>Table: <code>{$full_table_name}</code></h3>";
            $fields = $CI->db->field_data($full_table_name);

            echo "<table class='table'>";
            echo "<thead><tr><th>Field</th><th>Type</th><th>Max Length</th><th>Default</th></tr></thead>";
            echo "<tbody>";

            foreach ($fields as $field) {
                echo "<tr>";
                echo "<td><code>{$field->name}</code></td>";
                echo "<td>{$field->type}</td>";
                echo "<td>" . ($field->max_length ?: '-') . "</td>";
                echo "<td>" . ($field->default_value ?: '-') . "</td>";
                echo "</tr>";
            }

            echo "</tbody></table>";
        }
    }

    echo "<h2>✓ Migration Completed Successfully!</h2>";
    echo "<div class='success'>";
    echo "<strong>Next Steps:</strong><br>";
    echo "1. Verify the tables in your database<br>";
    echo "2. Continue with controller and view implementations<br>";
    echo "3. Test the payroll generation functionality<br>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<strong>✗ Migration Failed:</strong><br>";
    echo $e->getMessage();
    echo "</div>";

    if (isset($CI->db)) {
        $db_error = $CI->db->error();
        if (!empty($db_error['message'])) {
            echo "<div class='error'>";
            echo "<strong>Database Error:</strong><br>";
            echo "Code: " . $db_error['code'] . "<br>";
            echo "Message: " . $db_error['message'];
            echo "</div>";
        }
    }
}

echo "
    </div>
</body>
</html>";

// Delete this file after successful migration (optional - comment out if you want to keep it)
// unlink(__FILE__);
