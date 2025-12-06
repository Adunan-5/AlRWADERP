<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_335 extends App_Migration
{
    public function up()
    {
        $CI = &get_instance();

        // ========================================
        // 1. Create tblallowance_types table
        // ========================================
        if (!$CI->db->table_exists(db_prefix() . 'allowance_types')) {
            $CI->db->query("
                CREATE TABLE `" . db_prefix() . "allowance_types` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `name` VARCHAR(100) NOT NULL COLLATE utf8mb4_unicode_ci,
                    `name_arabic` VARCHAR(100) NULL COLLATE utf8mb4_unicode_ci,
                    `description` TEXT NULL COLLATE utf8mb4_unicode_ci,
                    `is_active` TINYINT(1) DEFAULT 1 COMMENT '1=Active, 0=Inactive',
                    `sort_order` INT(11) DEFAULT 0 COMMENT 'Display order in pay modal',
                    `created_at` DATETIME NULL,
                    `modified_at` DATETIME NULL,
                    `created_by` INT(11) NULL,
                    `modified_by` INT(11) NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `name` (`name`),
                    KEY `is_active` (`is_active`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        // ========================================
        // 2. Create tblallowance_assignments table
        // ========================================
        if (!$CI->db->table_exists(db_prefix() . 'allowance_assignments')) {
            $CI->db->query("
                CREATE TABLE `" . db_prefix() . "allowance_assignments` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `allowance_type_id` INT(11) NOT NULL,
                    `employee_type` ENUM('company_type', 'profession_type') NOT NULL COLLATE utf8mb4_unicode_ci,
                    `employee_type_id` INT(11) NOT NULL COMMENT 'ID from tblcompanytype or tblprofessiontype',
                    `is_mandatory` TINYINT(1) DEFAULT 0 COMMENT '1=Must be filled, 0=Optional',
                    `default_amount` DECIMAL(15,2) NULL COMMENT 'Default suggested amount',
                    `created_at` DATETIME NULL,
                    `created_by` INT(11) NULL,
                    PRIMARY KEY (`id`),
                    KEY `allowance_type_id` (`allowance_type_id`),
                    KEY `employee_type_lookup` (`employee_type`, `employee_type_id`),
                    UNIQUE KEY `unique_assignment` (`allowance_type_id`, `employee_type`, `employee_type_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        // ========================================
        // 3. Create tblstaff_pay_allowances table
        // ========================================
        if (!$CI->db->table_exists(db_prefix() . 'staff_pay_allowances')) {
            $CI->db->query("
                CREATE TABLE `" . db_prefix() . "staff_pay_allowances` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `staff_pay_id` INT(11) NOT NULL,
                    `allowance_type_id` INT(11) NOT NULL,
                    `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                    PRIMARY KEY (`id`),
                    KEY `staff_pay_id` (`staff_pay_id`),
                    KEY `allowance_type_id` (`allowance_type_id`),
                    UNIQUE KEY `unique_pay_allowance` (`staff_pay_id`, `allowance_type_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        // ========================================
        // 4. Add GOSI columns to tblstaffpay
        // ========================================
        if (!$CI->db->field_exists('gosi_basic', db_prefix() . 'staffpay')) {
            $CI->db->query("
                ALTER TABLE `" . db_prefix() . "staffpay`
                ADD COLUMN `gosi_basic` DECIMAL(15,2) NULL DEFAULT 0.00 COMMENT 'GOSI Basic Amount' AFTER `mewa`
            ");
        }

        if (!$CI->db->field_exists('gosi_housing_allowance', db_prefix() . 'staffpay')) {
            $CI->db->query("
                ALTER TABLE `" . db_prefix() . "staffpay`
                ADD COLUMN `gosi_housing_allowance` DECIMAL(15,2) NULL DEFAULT 0.00 COMMENT 'GOSI Housing Allowance Amount' AFTER `gosi_basic`
            ");
        }

        // ========================================
        // 5. Insert sample allowance types (optional)
        // ========================================
        // Uncomment the following to insert sample data
        /*
        $sample_allowances = [
            [
                'name' => 'Transportation Allowance',
                'name_arabic' => 'بدل نقل',
                'description' => 'Monthly transportation allowance',
                'is_active' => 1,
                'sort_order' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => 1
            ],
            [
                'name' => 'Mobile Allowance',
                'name_arabic' => 'بدل جوال',
                'description' => 'Mobile phone allowance',
                'is_active' => 1,
                'sort_order' => 2,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => 1
            ],
            [
                'name' => 'Performance Bonus',
                'name_arabic' => 'مكافأة أداء',
                'description' => 'Performance-based bonus',
                'is_active' => 1,
                'sort_order' => 3,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => 1
            ]
        ];

        foreach ($sample_allowances as $allowance) {
            // Check if allowance already exists
            $exists = $CI->db->where('name', $allowance['name'])
                             ->get(db_prefix() . 'allowance_types')
                             ->row();

            if (!$exists) {
                $CI->db->insert(db_prefix() . 'allowance_types', $allowance);
            }
        }
        */
    }

    public function down()
    {
        $CI = &get_instance();

        // Drop tables in reverse order
        if ($CI->db->table_exists(db_prefix() . 'staff_pay_allowances')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'staff_pay_allowances`');
        }

        if ($CI->db->table_exists(db_prefix() . 'allowance_assignments')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'allowance_assignments`');
        }

        if ($CI->db->table_exists(db_prefix() . 'allowance_types')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'allowance_types`');
        }

        // Remove GOSI columns from tblstaffpay
        if ($CI->db->field_exists('gosi_housing_allowance', db_prefix() . 'staffpay')) {
            $CI->db->query("
                ALTER TABLE `" . db_prefix() . "staffpay`
                DROP COLUMN `gosi_housing_allowance`
            ");
        }

        if ($CI->db->field_exists('gosi_basic', db_prefix() . 'staffpay')) {
            $CI->db->query("
                ALTER TABLE `" . db_prefix() . "staffpay`
                DROP COLUMN `gosi_basic`
            ");
        }
    }
}
