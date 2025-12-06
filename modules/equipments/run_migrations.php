<?php
/**
 * Equipment Module Migration Runner
 *
 * IMPORTANT: Delete this file after running migrations for security!
 *
 * Usage: Access via browser: https://rwaderp.local/modules/equipments/run_migrations.php
 */

// Define BASEPATH to allow file access
define('BASEPATH', TRUE);

// Load CodeIgniter bootstrap
require_once('../../index.php');

// Get CodeIgniter instance
$CI =& get_instance();

echo "<!DOCTYPE html>
<html>
<head>
    <title>Equipment Module - Migration Runner</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 12px; border-radius: 4px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 12px; border-radius: 4px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 12px; border-radius: 4px; margin: 10px 0; }
        .warning { background: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 12px; border-radius: 4px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
        .step { margin: 20px 0; padding: 15px; background: #f8f9fa; border-left: 4px solid #4CAF50; }
        .step-title { font-weight: bold; color: #333; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🚀 Equipment Module - Migration Runner</h1>";

try {
    // Step 1: Check if module entry exists
    echo "<div class='step'>";
    echo "<div class='step-title'>Step 1: Checking Module Registration</div>";

    $CI->db->where('module_name', 'equipments');
    $module = $CI->db->get(db_prefix() . 'modules')->row();

    if ($module) {
        echo "<div class='info'>✓ Module 'equipments' is registered in database</div>";
        echo "<pre>Current Status: " . ($module->active ? 'ACTIVE' : 'INACTIVE') . "</pre>";
        if (isset($module->migrations_version)) {
            echo "<pre>Current Migration Version: " . $module->migrations_version . "</pre>";
        }
    } else {
        echo "<div class='warning'>⚠ Module not registered. This is normal for first install.</div>";
    }
    echo "</div>";

    // Step 2: Run the installer
    echo "<div class='step'>";
    echo "<div class='step-title'>Step 2: Running Module Installer</div>";

    if (file_exists(__DIR__ . '/install.php')) {
        echo "<div class='info'>Found install.php - Executing...</div>";

        ob_start();
        require_once(__DIR__ . '/install.php');
        $install_output = ob_get_clean();

        echo "<div class='success'>✓ Installer executed successfully</div>";
        if (!empty($install_output)) {
            echo "<pre>" . htmlspecialchars($install_output) . "</pre>";
        }
    } else {
        echo "<div class='error'>✗ install.php not found!</div>";
    }
    echo "</div>";

    // Step 3: Verify tables created
    echo "<div class='step'>";
    echo "<div class='step-title'>Step 3: Verifying Tables Created</div>";

    $expected_tables = [
        'operators',
        'equipment_document_types',
        'operator_document_types',
        'equipment_documents',
        'operator_documents',
        'equipment_operators',
        'equipment_mobilization',
        'equipment_timesheet',
        'equipment_timesheet_details',
        'equipment_agreements'
    ];

    $created_tables = [];
    $missing_tables = [];

    foreach ($expected_tables as $table) {
        $full_table_name = db_prefix() . $table;
        if ($CI->db->table_exists($full_table_name)) {
            $created_tables[] = $full_table_name;
            echo "<div class='success'>✓ Table created: <strong>$full_table_name</strong></div>";
        } else {
            $missing_tables[] = $full_table_name;
            echo "<div class='error'>✗ Table missing: <strong>$full_table_name</strong></div>";
        }
    }

    echo "<br><strong>Summary:</strong> " . count($created_tables) . " of " . count($expected_tables) . " tables created successfully";
    echo "</div>";

    // Step 4: Check default data
    echo "<div class='step'>";
    echo "<div class='step-title'>Step 4: Checking Default Document Types</div>";

    if ($CI->db->table_exists(db_prefix() . 'equipment_document_types')) {
        $eq_doc_types = $CI->db->get(db_prefix() . 'equipment_document_types')->result();
        echo "<div class='success'>✓ Equipment Document Types: " . count($eq_doc_types) . " records</div>";
        echo "<pre>";
        foreach ($eq_doc_types as $type) {
            echo "- {$type->name} ({$type->name_arabic})\n";
        }
        echo "</pre>";
    }

    if ($CI->db->table_exists(db_prefix() . 'operator_document_types')) {
        $op_doc_types = $CI->db->get(db_prefix() . 'operator_document_types')->result();
        echo "<div class='success'>✓ Operator Document Types: " . count($op_doc_types) . " records</div>";
        echo "<pre>";
        foreach ($op_doc_types as $type) {
            echo "- {$type->name} ({$type->name_arabic})\n";
        }
        echo "</pre>";
    }
    echo "</div>";

    // Step 5: Check migration version
    echo "<div class='step'>";
    echo "<div class='step-title'>Step 5: Final Migration Version</div>";

    $CI->db->where('module_name', 'equipments');
    $final_module = $CI->db->get(db_prefix() . 'modules')->row();

    if ($final_module && isset($final_module->migrations_version)) {
        $expected_version = 109;
        if ($final_module->migrations_version == $expected_version) {
            echo "<div class='success'>✓ Migration version is correct: <strong>$expected_version</strong></div>";
        } else {
            echo "<div class='warning'>⚠ Migration version: {$final_module->migrations_version} (expected: $expected_version)</div>";
        }
    }
    echo "</div>";

    // Step 6: Check upload directories
    echo "<div class='step'>";
    echo "<div class='step-title'>Step 6: Checking Upload Directories</div>";

    $upload_base = FCPATH . 'uploads/equipments/';
    $upload_dirs = ['equipment_documents', 'operator_documents', 'agreements', 'timesheets'];

    foreach ($upload_dirs as $dir) {
        $path = $upload_base . $dir . '/';
        if (is_dir($path)) {
            echo "<div class='success'>✓ Directory exists: $path</div>";
        } else {
            echo "<div class='error'>✗ Directory missing: $path</div>";
        }
    }
    echo "</div>";

    // Success Summary
    echo "<div class='step' style='border-left-color: #4CAF50; background: #d4edda;'>";
    echo "<h2 style='color: #155724; margin-top: 0;'>🎉 Migration Complete!</h2>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ol>";
    echo "<li>Go to <a href='../../admin/setup/modules'>Admin → Setup → Modules</a></li>";
    echo "<li>Find 'Equipments' module and ensure it's <strong>ACTIVE</strong></li>";
    echo "<li>Check the menu - You should see new 'Equipments' menu with submenus</li>";
    echo "<li><strong>DELETE THIS FILE (run_migrations.php) for security!</strong></li>";
    echo "</ol>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Error During Migration</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}

// Check logs
echo "<div class='step'>";
echo "<div class='step-title'>Step 7: Recent Log Entries</div>";
$log_file = APPPATH . 'logs/log-' . date('Y-m-d') . '.php';
if (file_exists($log_file)) {
    $logs = file_get_contents($log_file);
    $equipment_logs = [];
    $lines = explode("\n", $logs);
    foreach ($lines as $line) {
        if (stripos($line, 'equipment') !== false || stripos($line, 'migration') !== false) {
            $equipment_logs[] = $line;
        }
    }

    if (!empty($equipment_logs)) {
        echo "<pre style='max-height: 300px; overflow-y: auto;'>";
        echo htmlspecialchars(implode("\n", array_slice($equipment_logs, -20))); // Last 20 lines
        echo "</pre>";
    } else {
        echo "<div class='info'>No equipment-related log entries found</div>";
    }
} else {
    echo "<div class='info'>No log file found for today</div>";
}
echo "</div>";

echo "
</div>
</body>
</html>";

