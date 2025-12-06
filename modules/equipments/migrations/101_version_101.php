<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration: Create operators table
 * Version: 1.0.1
 * Description: Create operators management table with document tracking
 */
class Migration_Version_101 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create tbloperators table
        if (!$CI->db->table_exists(db_prefix() . 'operators')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'operators` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `name_arabic` VARCHAR(255) DEFAULT NULL,
                `nationality` VARCHAR(100) DEFAULT NULL,
                `iqama_number` VARCHAR(50) DEFAULT NULL,
                `muqueen_number` VARCHAR(50) DEFAULT NULL,
                `license_number` VARCHAR(50) DEFAULT NULL,
                `license_type` VARCHAR(100) DEFAULT NULL,
                `license_expiry` DATE DEFAULT NULL,
                `passport_number` VARCHAR(50) DEFAULT NULL,
                `passport_expiry` DATE DEFAULT NULL,
                `medical_expiry` DATE DEFAULT NULL,
                `date_of_birth` DATE DEFAULT NULL,
                `phone` VARCHAR(50) DEFAULT NULL,
                `email` VARCHAR(100) DEFAULT NULL,
                `address` TEXT DEFAULT NULL,
                `supplier_id` INT(11) DEFAULT NULL COMMENT "From suppliers or purchase vendors",
                `operator_type` ENUM("own","hired") DEFAULT "own",
                `skills` TEXT DEFAULT NULL,
                `certifications` TEXT DEFAULT NULL,
                `status` ENUM("available","assigned","on_leave","terminated") DEFAULT "available",
                `joining_date` DATE DEFAULT NULL,
                `termination_date` DATE DEFAULT NULL,
                `notes` TEXT DEFAULT NULL,
                `created_by` INT(11) NOT NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `iqama_number` (`iqama_number`),
                KEY `idx_supplier_id` (`supplier_id`),
                KEY `idx_status` (`status`),
                KEY `idx_operator_type` (`operator_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
        }
    }

    public function down()
    {
        $CI = &get_instance();

        if ($CI->db->table_exists(db_prefix() . 'operators')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'operators`');
        }
    }
}
