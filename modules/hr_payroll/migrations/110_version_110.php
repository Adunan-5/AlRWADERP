<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_110 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // 1. Create tbl_hrp_payroll table (Payroll Header/Master)
        if (!$CI->db->table_exists(db_prefix() . 'hrp_payroll')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . "hrp_payroll` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
        }

        // 2. Add payroll_id column to tbl_hrp_employees_value
        if (!$CI->db->field_exists('payroll_id', db_prefix() . 'hrp_employees_value')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . "hrp_employees_value`
                ADD COLUMN `payroll_id` int(11) DEFAULT NULL COMMENT 'Foreign key to tbl_hrp_payroll' AFTER `id`,
                ADD KEY `idx_payroll_id` (`payroll_id`)
            ;");
        }

        // 3. Create tbl_hrp_project_payments table (Project-based OT/Allowances/Deductions)
        if (!$CI->db->table_exists(db_prefix() . 'hrp_project_payments')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . "hrp_project_payments` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
        }

        // 4. Create tbl_hrp_payroll_status_log table (Status Change Audit)
        if (!$CI->db->table_exists(db_prefix() . 'hrp_payroll_status_log')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . "hrp_payroll_status_log` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `payroll_id` int(11) NOT NULL,
                `from_status` varchar(50) DEFAULT NULL,
                `to_status` varchar(50) NOT NULL,
                `changed_by` int(11) NOT NULL,
                `changed_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `comments` text DEFAULT NULL,

                PRIMARY KEY (`id`),
                KEY `idx_payroll_id` (`payroll_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
        }

        // 5. Add new permissions for payroll management
        // Note: Permissions are typically managed through the UI or settings
        // This is just a reference for what needs to be added via admin interface:
        // - hrp_employee.create (Generate new payroll)
        // - hrp_employee.approve (Approve payroll - HR Manager)
        // - hrp_employee.submit (Submit payroll - Finance/Admin)
    }
}
