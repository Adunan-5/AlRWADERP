<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_333 extends App_Migration
{
    public function up()
    {
        $CI = &get_instance();

        // Add 'additions' column to hrp_employees_value table to separate from allowance
        if (!$CI->db->field_exists('additions', db_prefix() . 'hrp_employees_value')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'hrp_employees_value`
                ADD COLUMN `additions` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `allowance`');

            log_activity('Migration 333: Added additions column to hrp_employees_value table');
        }
    }

    public function down()
    {
        $CI = &get_instance();

        // Remove additions column
        if ($CI->db->field_exists('additions', db_prefix() . 'hrp_employees_value')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'hrp_employees_value`
                DROP COLUMN `additions`');
        }
    }
}
