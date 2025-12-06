<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Equipments Module Installer
 * Runs migrations when module is activated
 */

if (!$CI->db->table_exists(db_prefix() . 'modules')) {
    $CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "modules` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `module_name` VARCHAR(55) NOT NULL,
        `installed_version` VARCHAR(10) DEFAULT NULL,
        `active` INT(1) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
}

// Check if module already registered
$CI->db->where('module_name', 'equipments');
$module = $CI->db->get(db_prefix() . 'modules')->row();

if (!$module) {
    $CI->db->insert(db_prefix() . 'modules', [
        'module_name' => 'equipments',
        'installed_version' => '1.1.0',
        'active' => 1
    ]);
}

// Run migrations
$CI->load->library('migration');

// Check if we need to add migration version tracking
if (!$CI->db->field_exists('migrations_version', db_prefix() . 'modules')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'modules` ADD COLUMN `migrations_version` INT(11) DEFAULT 0');
}

// Load App_module_migration library
if (!class_exists('App_module_migration')) {
    require_once(APPPATH . 'libraries/App_module_migration.php');
}

// Get current migration version for this module
$CI->db->where('module_name', 'equipments');
$current_module = $CI->db->get(db_prefix() . 'modules')->row();
$current_version = $current_module && isset($current_module->migrations_version)
    ? (int)$current_module->migrations_version
    : 0;

// Run all migrations up to version 117
$migrations_path = dirname(__FILE__) . '/migrations/';

if (is_dir($migrations_path)) {
    $migration_files = glob($migrations_path . '*.php');
    sort($migration_files);

    foreach ($migration_files as $file) {
        $filename = basename($file, '.php');

        // Extract version number (e.g., 101 from "101_version_101")
        if (preg_match('/^(\d+)_/', $filename, $matches)) {
            $version = (int)$matches[1];

            if ($version > $current_version) {
                require_once $file;

                $class_name = 'Migration_' . ucfirst(strtolower(str_replace($matches[1] . '_', '', $filename)));

                if (class_exists($class_name)) {
                    $migration = new $class_name();

                    try {
                        $migration->up();

                        // Update migration version
                        $CI->db->where('module_name', 'equipments');
                        $CI->db->update(db_prefix() . 'modules', [
                            'migrations_version' => $version
                        ]);

                        log_message('info', "Equipments module: Migration {$version} executed successfully");
                    } catch (Exception $e) {
                        log_message('error', "Equipments module: Migration {$version} failed - " . $e->getMessage());
                        throw $e;
                    }
                }
            }
        }
    }
}

// Create uploads directory for equipment documents
$upload_path = FCPATH . 'uploads/equipments/';
$document_paths = [
    'equipment_documents',
    'operator_documents',
    'agreements',
    'timesheets'
];

foreach ($document_paths as $dir) {
    $path = $upload_path . $dir . '/';
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
        // Create .htaccess to protect uploads
        file_put_contents($path . '.htaccess', "Options -Indexes");
    }
}

log_message('info', 'Equipments module installed successfully');
