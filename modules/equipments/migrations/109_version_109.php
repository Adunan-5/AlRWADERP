<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration: Add is_active column to operators table
 * Version: 1.0.9
 * Description: Add active/inactive status column to operators
 */
class Migration_Version_109 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Add is_active column to operators table
        if ($CI->db->table_exists(db_prefix() . 'operators')) {
            if (!$CI->db->field_exists('is_active', db_prefix() . 'operators')) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . 'operators` ADD COLUMN `is_active` TINYINT(1) DEFAULT 1 AFTER `status`');
                $CI->db->query('ALTER TABLE `' . db_prefix() . 'operators` ADD KEY `idx_is_active` (`is_active`)');
            }

            // Also add updated_by if not exists
            if (!$CI->db->field_exists('updated_by', db_prefix() . 'operators')) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . 'operators` ADD COLUMN `updated_by` INT(11) DEFAULT NULL AFTER `updated_at`');
            }

            // Add remarks column if not exists
            if (!$CI->db->field_exists('remarks', db_prefix() . 'operators')) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . 'operators` ADD COLUMN `remarks` TEXT DEFAULT NULL AFTER `notes`');
            }
        }
    }

    public function down()
    {
        $CI = &get_instance();

        if ($CI->db->table_exists(db_prefix() . 'operators')) {
            if ($CI->db->field_exists('is_active', db_prefix() . 'operators')) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . 'operators` DROP COLUMN `is_active`');
            }
            if ($CI->db->field_exists('updated_by', db_prefix() . 'operators')) {
                $CI->db->query('ALTER TABLE `' . db_prefix() . 'operators` DROP COLUMN `updated_by`');
            }
        }
    }
}
