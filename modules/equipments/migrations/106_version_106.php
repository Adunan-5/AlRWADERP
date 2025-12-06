<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration: Create equipment timesheet master table
 * Version: 1.0.6
 * Description: Monthly timesheet header for client billing
 */
class Migration_Version_106 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create equipment timesheet master table
        if (!$CI->db->table_exists(db_prefix() . 'equipment_timesheet')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'equipment_timesheet` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `timesheet_number` VARCHAR(100) DEFAULT NULL,
                `month` DATE NOT NULL COMMENT "YYYY-MM-01 format",
                `client_id` INT(11) NOT NULL,
                `project_id` INT(11) DEFAULT NULL,
                `equipment_id` INT(11) NOT NULL,
                `operator_id` INT(11) DEFAULT NULL,
                `mobilization_id` INT(11) DEFAULT NULL,

                -- Equipment details (denormalized for reporting)
                `equipment_description` VARCHAR(500) DEFAULT NULL,
                `driver_name` VARCHAR(255) DEFAULT NULL,
                `plate_number` VARCHAR(100) DEFAULT NULL,
                `location` VARCHAR(255) DEFAULT NULL,
                `mobilized_on` DATE DEFAULT NULL,

                -- Billing calculation
                `rate_per_hour` DECIMAL(15,2) NOT NULL DEFAULT 0,
                `total_hours` DECIMAL(10,2) DEFAULT 0,
                `total_amount` DECIMAL(15,2) DEFAULT 0,
                `deduction_amount` DECIMAL(15,2) DEFAULT 0,
                `deduction_reason` TEXT DEFAULT NULL,
                `payable_amount` DECIMAL(15,2) DEFAULT 0,

                -- Workflow status
                `status` ENUM("draft","submitted","verified","approved","invoiced","rejected") DEFAULT "draft",

                -- Prepared by (Business Coordinator)
                `prepared_by` INT(11) DEFAULT NULL,
                `prepared_at` DATETIME DEFAULT NULL,
                `prepared_remarks` TEXT DEFAULT NULL,

                -- Verified by (Marketing Manager)
                `verified_by` INT(11) DEFAULT NULL,
                `verified_at` DATETIME DEFAULT NULL,
                `verified_remarks` TEXT DEFAULT NULL,

                -- Approved by (General Manager)
                `approved_by` INT(11) DEFAULT NULL,
                `approved_at` DATETIME DEFAULT NULL,
                `approved_remarks` TEXT DEFAULT NULL,

                -- Invoice linkage
                `invoice_id` INT(11) DEFAULT NULL COMMENT "Link to tblinvoices",
                `invoice_generated` TINYINT(1) DEFAULT 0,
                `invoice_generated_at` DATETIME DEFAULT NULL,

                -- Rejection
                `rejected_by` INT(11) DEFAULT NULL,
                `rejected_at` DATETIME DEFAULT NULL,
                `rejection_reason` TEXT DEFAULT NULL,

                -- Audit
                `created_by` INT(11) NOT NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME DEFAULT NULL,

                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_timesheet_month_equipment` (`month`, `equipment_id`, `client_id`),
                KEY `idx_timesheet_number` (`timesheet_number`),
                KEY `idx_client_id` (`client_id`),
                KEY `idx_equipment_id` (`equipment_id`),
                KEY `idx_operator_id` (`operator_id`),
                KEY `idx_month` (`month`),
                KEY `idx_status` (`status`),
                KEY `idx_invoice_id` (`invoice_id`),
                CONSTRAINT `fk_equipment_timesheet_equipment`
                    FOREIGN KEY (`equipment_id`) REFERENCES `' . db_prefix() . 'equipments` (`id`) ON DELETE RESTRICT,
                CONSTRAINT `fk_equipment_timesheet_operator`
                    FOREIGN KEY (`operator_id`) REFERENCES `' . db_prefix() . 'operators` (`id`) ON DELETE SET NULL,
                CONSTRAINT `fk_equipment_timesheet_mobilization`
                    FOREIGN KEY (`mobilization_id`) REFERENCES `' . db_prefix() . 'equipment_mobilization` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
        }
    }

    public function down()
    {
        $CI = &get_instance();

        if ($CI->db->table_exists(db_prefix() . 'equipment_timesheet')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'equipment_timesheet`');
        }
    }
}
