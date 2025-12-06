<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_331 extends CI_Migration
{
    public function up()
    {
        // Create timesheet_adjustments table
        $this->db->query(
            'CREATE TABLE IF NOT EXISTS `' . db_prefix() . 'timesheet_adjustments` (
                `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                `staff_id` int NOT NULL,
                `project_id` int NOT NULL,
                `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT \'allowance or deduction\',
                `date` date NOT NULL,
                `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
                `amount` decimal(15,2) NOT NULL DEFAULT \'0.00\',
                `month_year` date NOT NULL COMMENT \'First day of month (YYYY-MM-01)\',
                `created_by` int NOT NULL,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `staff_id` (`staff_id`),
                KEY `project_id` (`project_id`),
                KEY `month_year` (`month_year`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
        );
    }
}
