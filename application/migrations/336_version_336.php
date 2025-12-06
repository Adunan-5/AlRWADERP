<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_336 extends App_Migration
{
    public function up()
    {
        $CI = &get_instance();

        // ========================================
        // Add 'staff_type' to employee_type ENUM
        // ========================================

        // Note: MySQL doesn't support ALTER COLUMN directly for ENUM
        // We need to modify the column definition

        $CI->db->query("
            ALTER TABLE `" . db_prefix() . "allowance_assignments`
            MODIFY COLUMN `employee_type` ENUM('company_type', 'profession_type', 'staff_type') NOT NULL COLLATE utf8mb4_unicode_ci
        ");

        // Update comment to reflect new option
        $CI->db->query("
            ALTER TABLE `" . db_prefix() . "allowance_assignments`
            MODIFY COLUMN `employee_type_id` INT(11) NOT NULL
            COMMENT 'ID from tblcompanytype, tblprofessiontype, or tblstafftype'
        ");
    }

    public function down()
    {
        $CI = &get_instance();

        // Remove 'staff_type' from ENUM (careful - this will fail if any records use staff_type)
        $CI->db->query("
            ALTER TABLE `" . db_prefix() . "allowance_assignments`
            MODIFY COLUMN `employee_type` ENUM('company_type', 'profession_type') NOT NULL COLLATE utf8mb4_unicode_ci
        ");

        // Restore original comment
        $CI->db->query("
            ALTER TABLE `" . db_prefix() . "allowance_assignments`
            MODIFY COLUMN `employee_type_id` INT(11) NOT NULL
            COMMENT 'ID from tblcompanytype or tblprofessiontype'
        ");
    }
}
