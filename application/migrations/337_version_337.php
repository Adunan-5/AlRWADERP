<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_337 extends App_Migration
{
    public function up()
    {
        $CI = &get_instance();

        // ========================================
        // Add new columns to hrp_employees_value table
        // ========================================

        // Check if columns don't exist before adding
        if (!$CI->db->field_exists('payroll_month', db_prefix() . 'hrp_employees_value')) {
            $CI->db->query("
                ALTER TABLE `" . db_prefix() . "hrp_employees_value`
                ADD COLUMN `payroll_month` VARCHAR(50) NULL COMMENT 'Payroll month in readable format (e.g., January 2025)'
                AFTER `mention`
            ");
        }

        if (!$CI->db->field_exists('comment_1', db_prefix() . 'hrp_employees_value')) {
            $CI->db->query("
                ALTER TABLE `" . db_prefix() . "hrp_employees_value`
                ADD COLUMN `comment_1` TEXT NULL COMMENT 'Comment field 1 for reference'
                AFTER `payroll_month`
            ");
        }

        if (!$CI->db->field_exists('comment_2', db_prefix() . 'hrp_employees_value')) {
            $CI->db->query("
                ALTER TABLE `" . db_prefix() . "hrp_employees_value`
                ADD COLUMN `comment_2` TEXT NULL COMMENT 'Comment field 2 for reference'
                AFTER `comment_1`
            ");
        }

        if (!$CI->db->field_exists('comment_3', db_prefix() . 'hrp_employees_value')) {
            $CI->db->query("
                ALTER TABLE `" . db_prefix() . "hrp_employees_value`
                ADD COLUMN `comment_3` TEXT NULL COMMENT 'Comment field 3 for reference'
                AFTER `comment_2`
            ");
        }
    }

    public function down()
    {
        $CI = &get_instance();

        // Remove columns if they exist
        if ($CI->db->field_exists('comment_3', db_prefix() . 'hrp_employees_value')) {
            $CI->db->query("
                ALTER TABLE `" . db_prefix() . "hrp_employees_value`
                DROP COLUMN `comment_3`
            ");
        }

        if ($CI->db->field_exists('comment_2', db_prefix() . 'hrp_employees_value')) {
            $CI->db->query("
                ALTER TABLE `" . db_prefix() . "hrp_employees_value`
                DROP COLUMN `comment_2`
            ");
        }

        if ($CI->db->field_exists('comment_1', db_prefix() . 'hrp_employees_value')) {
            $CI->db->query("
                ALTER TABLE `" . db_prefix() . "hrp_employees_value`
                DROP COLUMN `comment_1`
            ");
        }

        if ($CI->db->field_exists('payroll_month', db_prefix() . 'hrp_employees_value')) {
            $CI->db->query("
                ALTER TABLE `" . db_prefix() . "hrp_employees_value`
                DROP COLUMN `payroll_month`
            ");
        }
    }
}
