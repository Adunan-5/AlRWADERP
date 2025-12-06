<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_332 extends App_Migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create salary payments table
        if (!$CI->db->table_exists(db_prefix() . 'timesheet_salary_payments')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'timesheet_salary_payments` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `staff_id` int(11) NOT NULL,
              `project_id` int(11) NOT NULL,
              `month` varchar(7) NOT NULL COMMENT "Format: YYYY-MM",
              `paid_from` varchar(50) NOT NULL COMMENT "Payment source: bank, cash, etc",
              `paid_date` date NOT NULL,
              `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
              `reference_number` varchar(100) DEFAULT NULL,
              `is_bank_transfer` tinyint(1) NOT NULL DEFAULT 0,
              `paid_by` int(11) NOT NULL COMMENT "Staff ID who made the payment",
              `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `staff_id` (`staff_id`),
              KEY `project_id` (`project_id`),
              KEY `month` (`month`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }
    }

    public function down()
    {
        $CI = &get_instance();

        if ($CI->db->table_exists(db_prefix() . 'timesheet_salary_payments')) {
            $CI->db->query('DROP TABLE `' . db_prefix() . 'timesheet_salary_payments`;');
        }
    }
}
