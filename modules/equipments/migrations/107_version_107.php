<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration: Create equipment timesheet details table
 * Version: 1.0.7
 * Description: Daily hours breakdown for timesheets
 */
class Migration_Version_107 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create timesheet details table
        if (!$CI->db->table_exists(db_prefix() . 'equipment_timesheet_details')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'equipment_timesheet_details` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `timesheet_id` INT(11) NOT NULL,
                `day` TINYINT(2) NOT NULL COMMENT "Day of month (1-31)",
                `date` DATE NOT NULL,
                `actual_hours` DECIMAL(5,2) DEFAULT 0,
                `overtime_hours` DECIMAL(5,2) DEFAULT 0,
                `notes` TEXT DEFAULT NULL,
                `is_working_day` TINYINT(1) DEFAULT 1,
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_timesheet_day` (`timesheet_id`, `day`),
                KEY `idx_date` (`date`),
                CONSTRAINT `fk_timesheet_details_timesheet`
                    FOREIGN KEY (`timesheet_id`) REFERENCES `' . db_prefix() . 'equipment_timesheet` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
        }
    }

    public function down()
    {
        $CI = &get_instance();

        if ($CI->db->table_exists(db_prefix() . 'equipment_timesheet_details')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'equipment_timesheet_details`');
        }
    }
}
